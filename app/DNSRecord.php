<?php

namespace App;

use Exception;
use Illuminate\Contracts\Support\Arrayable;

class DNSRecord implements Arrayable
{
    public string $DomainName;
    public string $HostName;
    public string $RecordType;
    public string $Data;
    public ?int $TTL;
    public ?int $Priority;

    public static function create($DomainName, $HostName, $RecordType, $Data, $TTL = null, $Priority = null)
    {
        $dnsRecord = new self();

        $dnsRecord->DomainName = $DomainName;
        $dnsRecord->HostName = $HostName;
        $dnsRecord->RecordType = $RecordType;

        $Data = str_split($Data, 250);

        $dnsRecord->Data = '';

        if (count($Data) === 1) {
            $dnsRecord->Data = $Data[0];
        } else {
            $dnsRecord->Data = collect($Data)
                ->map(fn($chunk) => '"' . $chunk . '"')
                ->implode(' ');
        }

        $dnsRecord->TTL = (int)($TTL ?? 3600);
        $dnsRecord->Priority = (int)($Priority ?? 0);

        $dnsRecord->validate();

        return $dnsRecord;
    }

    public static function createFromRecord($record)
    {
        $dnsRecord = new self();

        foreach ($record as $name => $value) {
            if (!property_exists(self::class, $name)) {
                {
                    continue;
                }
            }

            $dnsRecord->$name = $value;
        }

        return $dnsRecord;
    }

    public static function createFromArguments($arguments)
    {
        $arguments = collect($arguments)
            ->mapWithKeys(
                function ($value, $key) {
                    return [ucfirst($key) => $value];
                }
            );

        return self::create(
            $arguments['DomainName'],
            $arguments['HostName'],
            $arguments['RecordType'],
            $arguments['Data'],
            $arguments['TTL'] ?? null,
            $arguments['Priority'] ?? null,
            );
    }

    public function toArray()
    {
        return collect(get_object_vars($this))
            ->map(fn($value, $name) => $this->$name)
            ->toArray();
    }

    private function validate()
    {
        if (!in_array($this->RecordType, ['A', 'AAAA', 'MX', 'CNAME', 'NS', 'TXT', 'SRV'])) {
            throw new Exception("recordType {$this->RecordType} is not allowed.");
        }

        if (!in_array(
            $this->TTL,
            [
                '60',
                '300',
                '600',
                '900',
                '1800',
                '2700',
                '3600',
                '7200',
                '14400',
                '28800',
                '43200',
                '64800',
                '86400',
                '172800'
            ]
        )) {
            throw new Exception("TTL {$this->TTL} is not allowed.");
        }

        if (!in_array($this->Priority, [0, 5, 10, 20, 30, 40, 50, 60, 70, 80, 90, 100])) {
            throw new Exception("recordType {$this->RecordType} is not allowed.");
        }

        if ($this->RecordType === 'MX' && $this->Priority === 0) {
            throw new Exception('MX records should have priority > 0.');
        }

        if ($this->RecordType !== 'MX' && $this->Priority !== 0) {
            throw new Exception('Non MX records should have priority = 0.');
        }
    }

    public function toString()
    {
        return "{$this->HostName} {$this->RecordType} {$this->getDataDisplay()} {$this->TTL} {$this->Priority}";
    }

    public function getDataDisplay()
    {
        if (strlen($this->Data) > 20) {
            return substr($this->Data, 0, 20) . '...';
        }

        return $this->Data;
    }

    public static function getProperties()
    {
        return collect(get_class_vars(self::class))
            ->keys()
            ->toArray();
    }
}