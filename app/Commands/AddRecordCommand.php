<?php

namespace App\Commands;

use App\DNSRecord;
use LaravelZero\Framework\Commands\Command;

class AddRecordCommand extends Command
{
    use GidinetTrait;

    protected $signature = 'add {domainName} {hostName} {recordType} {data} {ttl?} {priority?} {--update}';

    protected $description = 'Add DNS record';

    public function handle()
    {
        $newRecord = DNSRecord::createFromArguments($this->arguments());

        if ($this->option('update')) {
            if (($records = $this->listRecords()) === false) {
                return;
            }

            $recordsMenu = collect($records)->map->toString()->toArray();

            $option = $this->menu('Available DNS records', $recordsMenu)->open();

            if ($option === null) {
                return;
            }

            $this->updateRecord($records[$option], $newRecord);
        } else {
            $this->addRecord($newRecord);
        }
    }
}
