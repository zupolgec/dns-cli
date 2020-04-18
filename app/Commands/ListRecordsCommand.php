<?php

namespace App\Commands;

use App\DNSRecord;
use LaravelZero\Framework\Commands\Command;

class ListRecordsCommand extends Command
{
    use GidinetTrait;

    protected $signature = 'show {domainName}';

    protected $description = 'Lists DNS records for the specified domain';

    public function handle()
    {
        if (($records = $this->listRecords()) === false) {
            return;
        }

        $this->table(DNSRecord::getProperties(), $records);
    }
}
