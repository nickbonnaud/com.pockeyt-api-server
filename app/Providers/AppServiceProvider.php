<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Models\Business\Business;
use App\Models\Business\Profile;
use App\Models\Business\Account;
use App\Models\Business\PayFacAccount;
use App\Models\Business\PayFacBusiness;
use App\Models\Business\PayFacOwner;
use App\Models\Business\GeoAccount;
use App\Models\Business\ActiveItem;
use App\Models\Business\PosAccount;
use App\Models\Business\SquareAccount;
use App\Models\Business\CloverAccount;
use App\Models\Business\LightspeedRetailAccount;
use App\Models\Business\ShopifyAccount;
use App\Models\Business\VendAccount;

use App\Observers\Business\BusinessObserver;
use App\Observers\Business\ProfileObserver;
use App\Observers\Business\AccountObserver;
use App\Observers\Business\PayFacAccountObserver;
use App\Observers\Business\PayFacBusinessObserver;
use App\Observers\Business\PayFacOwnerObserver;
use App\Observers\Business\GeoAccountObserver;
use App\Observers\Business\ActiveItemObserver;
use App\Observers\Business\PosAccountObserver;
use App\Observers\Business\SquareAccountObserver;
use App\Observers\Business\CloverAccountObserver;
use App\Observers\Business\LightspeedRetailAccountObserver;
use App\Observers\Business\ShopifyAccountObserver;
use App\Observers\Business\VendAccountObserver;


use App\Models\Customer\Customer;
use App\Models\Customer\AchCustomer;
use App\Models\Customer\CardCustomer;

use App\Observers\Customer\CustomerObserver;
use App\Observers\Customer\AchCustomerObserver;
use App\Observers\Customer\CardCustomerObserver;


use App\Models\Transaction\UnassignedTransaction;
use App\Models\Transaction\Transaction;
use App\Models\Transaction\TransactionNotification;

use App\Observers\Transaction\UnassignedTransactionObserver;
use App\Observers\Transaction\TransactionObserver;
use App\Observers\Transaction\TransactionNotificationObserver;

use App\Models\Location\ActiveLocation;

use App\Observers\Location\ActiveLocationObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
        Business::observe(BusinessObserver::class);
        Profile::observe(ProfileObserver::class);
        Account::observe(AccountObserver::class);
        PayFacAccount::observe(PayFacAccountObserver::class);
        PayFacBusiness::observe(PayFacBusinessObserver::class);
        PayFacOwner::observe(PayFacOwnerObserver::class);
        GeoAccount::observe(GeoAccountObserver::class);
        PosAccount::observe(PosAccountObserver::class);
        SquareAccount::observe(SquareAccountObserver::class);
        CloverAccount::observe(CloverAccountObserver::class);
        LightspeedRetailAccount::observe(LightspeedRetailAccountObserver::class);
        ShopifyAccount::observe(ShopifyAccountObserver::class);
        VendAccount::observe(VendAccountObserver::class);

        Customer::observe(CustomerObserver::class);
        AchCustomer::observe(AchCustomerObserver::class);
        CardCustomer::observe(CardCustomerObserver::class);
        ActiveItem::observe(ActiveItemObserver::class);

        UnassignedTransaction::observe(UnassignedTransactionObserver::class);
        Transaction::observe(TransactionObserver::class);
        TransactionNotification::observe(TransactionNotificationObserver::class);

        ActiveLocation::observe(ActiveLocationObserver::class);
    }
}
