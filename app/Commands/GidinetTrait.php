<?php

namespace App\Commands;

use App\DNSRecord;
use App\GidinetAPI;

trait GidinetTrait
{
    private GidinetAPI $api;

    public function __construct()
    {
        parent::__construct();

        $this->api = new GidinetAPI(env('GIDINET_USERNAME'), env('GIDINET_PASSWORD'));
    }

    private function checkResult($result)
    {
        if ($result['status'] === 'ok') {
            return true;
        }

        $this->error("Errore ({$result['errorCode']}): {$result['errorMessage']}");

        return false;
    }

    /**
     * @param string $domainName
     * @return array|bool
     */
    private function listRecords($domainName = null)
    {
        $domainName ??= $this->argument('domainName');

        $result = $this->api->recordGetList(
            [
                'domainName' => $domainName
            ]
        );

        if (!$this->checkResult($result)) {
            return false;
        }

        return $result['records'];
    }

    private function addRecord(DNSRecord $record)
    {
        $result = $this->api->recordAdd(
            [
                'record' => $record
            ]
        );

        if (!$this->checkResult($result)) {
            return false;
        }

        $this->info("Record '{$record->toString()}' has been added.");

        return $result;
    }

    private function updateRecord(DNSRecord $oldRecord, DNSRecord $newRecord)
    {
        $result = $this->api->recordUpdate(
            [
                'oldRecord' => $oldRecord,
                'newRecord' => $newRecord
            ]
        );

        if (!$this->checkResult($result)) {
            return false;
        }

        $this->info("Record has been updated.");
        $this->info("Old: {$oldRecord->toString()}.");
        $this->info("New: {$newRecord->toString()}.");

        return $result;
    }

    private function deleteRecord(DNSRecord $record)
    {
        $result = $this->api->recordDelete(
            [
                'record' => $record
            ]
        );

        if (!$this->checkResult($result)) {
            return false;
        }

        $this->info("Record '{$record->toString()}' has been deleted.");

        return $result;
    }

}