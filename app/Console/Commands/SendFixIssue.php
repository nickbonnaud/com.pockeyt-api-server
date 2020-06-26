<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AutoFixIssueNotification;

class SendFixIssue extends Command {
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'notifications:send_fix';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Send auto fix issue notifications for transactions';

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
  public function handle(AutoFixIssueNotification $notifications) {
    $notifications->send();
  }
}
