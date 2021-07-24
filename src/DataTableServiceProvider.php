<?php

namespace Octopy\DataTable;

use Illuminate\Support\ServiceProvider;
use Octopy\DataTable\Console\MakeDataTableCommand;

/**
 * Class DataTableServiceProvider
 * @package Octopy\DataTable
 */
class DataTableServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot()
    {
        $this->registerCommands();
    }

    /**
     * @return void
     */
    private function registerCommands() : void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeDataTableCommand::class,
            ]);
        }
    }
}
