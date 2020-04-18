<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

class DeleteRecordCommand extends Command
{
    use GidinetTrait;

    protected $signature = 'delete {domainName}';

    protected $description = 'Delete DNS record';

    public function handle()
    {
        if (($records = $this->listRecords()) === false) {
            return;
        }

        $recordsMenu = collect($records)->map->toString()->toArray();

        $option = $this->menu('Available DNS records', $recordsMenu)->open();

        if ($option === null) {
            return;
        }

        if (($result = $this->deleteRecord($records[$option])) === false) {
            return;
        }
    }
}
