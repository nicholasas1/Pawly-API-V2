<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Http\Controllers\schedulersystemcontroller;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        //$schedule->command( [schedulersystemcontroller::class,'orderList'])->everyMinute()->runInBackground();
        $schedule->call('App\Http\Controllers\schedulersystemcontroller@orderList')->everyMinute();
        $schedule->call('App\Http\Controllers\schedulersystemcontroller@vcLinkEnd')->everyMinute();
        //[schedulersystemcontroller::class,'orderList']
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
