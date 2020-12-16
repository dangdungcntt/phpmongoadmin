<?php

namespace App\Console\Commands;

use App\Models\Connection;
use Illuminate\Console\Command;
use Illuminate\Foundation\Console\KeyGenerateCommand;

class RegenerateAppKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'regenerate-app-key';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'RegenerateAppKey';

    public function handle(): int
    {
        $updates = [];

        Connection::all()->each(
            function (Connection $connection) use (&$updates) {
                $updates[$connection->id] = $connection->uri;
            }
        );

        $this->call(
            KeyGenerateCommand::class,
            [
                '--force'
            ]
        );

        foreach ($updates as $id => $uri) {
            Connection::query()->firstWhere('id', $id)->update(compact('uri'));
        }

        $this->info('Updated all connections uri with new app key.');

        return 0;
    }
}
