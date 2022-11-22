<?php

namespace App\Console\Commands\Payment;

use App\Services\Payment\YooMoney;
use Illuminate\Console\Command;

class YooMoneyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yoo-money:check-payment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'descripti';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        YooMoney::get_change_payment();
        return Command::SUCCESS;
    }
}
