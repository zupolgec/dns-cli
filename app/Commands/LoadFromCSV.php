<?php

namespace App\Commands;

use App\DNSRecord;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class LoadFromCSV extends Command
{
    use GidinetTrait;

    protected $signature = 'load-csv {domainName} {file}';

    protected $description = 'Load DNS records from CSV';

    public function handle()
    {
        $csv = explode("\n", file_get_contents($this->argument('file')));

        foreach ($csv as $row) {
            $row = explode(',', $row);

            if (substr($row[0], 0, 1) == '"') {
                foreach ($row as $i => $part) {
                    $row[$i] = substr($part, 1, -1);
                }
            }

            $hostName = $row[0];
            $recordType = $row[1];
            $data = $row[2];

            $record = DNSRecord::create($this->argument('domainName'), $hostName, $recordType, $data);

            $this->addRecord($record);
        }
    }
}
