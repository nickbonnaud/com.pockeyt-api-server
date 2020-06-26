<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AutoBillClosedNotifications;

class SendAutoBillClosed extends Command {
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'notifications:send_closed';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Send auto bill closed notifications for transactions';

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct() {
    parent::__construct();
  }

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle(AutoBillClosedNotifications $notifications) {
    $notifications->send();
  }
}
