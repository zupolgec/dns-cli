<?php

namespace App;

use SoapClient;
use SoapFault;

/**
 * @method recordGetList(array $parameters)
 * @method recordAdd(array $parameters)
 * @method recordUpdate(array $parameters)
 */
class GidinetAPI
{
    private SoapClient $client;

    private string $username;
    private string $password;

    private const WSDL_URL = 'https://api.quickservicebox.com/API/Beta/DNSAPI.asmx?WSDL';

    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = base64_encode($password);

        $options = array(
            'soap_version' => SOAP_1_2,
            'exceptions' => true,
            'trace' => 1,
            'cache_wsdl' => WSDL_CACHE_NONE
        );

        try {
            $this->client = new SoapClient(self::WSDL_URL, $options);
        } catch (SoapFault $e) {
        }
    }

    private function prepareParameters($parameters)
    {
        $baseParameters = [
            'accountUsername' => $this->username,
            'accountPasswordB64' => $this->password
        ];

        return array_merge($baseParameters, $parameters[0]);
    }

    private function parseResponse($method, $response)
    {
        $resultName = $method . 'Result';

        $result = $response->$resultName;

        $resultCode = $result->resultCode;

        $errorCodes = [
            0 => 'Operazione riuscita',
            1 => 'Autenticazione fallita',
            2 => 'Operazione fallita - impossibile modificare un valore in sola lettura',
            3 => 'Operazione fallita - parametri non validi',
            4 => 'Operazione fallita - errore non definito',
            5 => 'Operazione fallita - oggetto non trovato',
            6 => 'Operazione fallita - oggetto in uso',
        ];

        if ($method == 'recordGetList' && $resultCode === 0) {
            $records = collect($result->resultItems->DNSRecordListItem)
                ->map(
                    function ($record) {
                        return DNSRecord::createFromRecord($record);
                    }
                )
                ->sortBy('Data')
                ->sortBy('Priority')
                ->sortBy('RecordType')
                ->sortBy('HostName')
                ->values();
        }

        return [
            'status' => $resultCode === 0 ? 'ok' : 'ko',
            'errorCode' => $resultCode,
            'errorMessage' => $errorCodes[$resultCode],
            'message' => $result->resultText,
            'records' => $records ?? []
        ];
    }

    private function callSOAPMethod($method, $parameters)
    {
        $response = $this->client->$method($this->prepareParameters($parameters));

        return $this->parseResponse($method, $response);
    }

    public function __call($method, $parameters)
    {
        $result = $this->callSOAPMethod($method, $parameters);

        return $result;
    }
}