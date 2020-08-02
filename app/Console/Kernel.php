<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {
  /**
   * The Artisan commands provided by your application.
   *
   * @var array
   */
  protected $commands = [
      //
  ];

  /**
   * Define the application's command schedule.
   *
   * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
   * @return void
   */
  protected function schedule(Schedule $schedule) {
    $schedule->command('notifications:send_closed')
      ->everyFiveMinutes()
      ->withoutOverlapping(5)
      ->runInBackground();

    $schedule->command('notifications:send_auto')
      ->everyFiveMinutes()
      ->withoutOverlapping(5)
      ->runInBackground();

    $schedule->command('notifications:send_auto_issue')
      ->everyTenMinutes()
      ->withoutOverlapping(10)
      ->runInBackground();

    $schedule->command('notifications:send_fix')
      ->everyTenMinutes()
      ->withoutOverlapping(10)
      ->runInBackground();
  }

  /**
   * Register the commands for the application.
   *
   * @return void
   */
  protected function commands() {
    $this->load(__DIR__.'/Commands');

    require base_path('routes/console.php');
  }
}
