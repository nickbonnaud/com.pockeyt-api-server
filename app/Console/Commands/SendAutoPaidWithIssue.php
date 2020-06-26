<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AutoPaidWithIssueNotifications;

class SendAutoPaidWithIssue extends Command {
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'notifications:send_auto_issue';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Send auto paid notifications for transactions with issue';

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
  public function handle(AutoPaidWithIssueNotifications $notifications) {
    $notifications->send();
  }
}
