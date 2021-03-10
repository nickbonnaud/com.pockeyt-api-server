<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Carbon\Carbon;

class MasterTestSeeder extends Seeder {
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run() {
  	$faker = \Faker\Factory::create();

    $business = factory(\App\Models\Business\Business::class)->create(['email' => 'test@pockeyt.com', 'password' => 'Password1!']);
    $profile = factory(\App\Models\Business\Profile::class)->create(['business_id' => $business->id]);
    $hours = factory(\App\Models\Business\Hours::class)->create(['profile_id' => $profile->id]);
    $photos = factory(\App\Models\Business\ProfilePhotos::class)->create(['profile_id' => $profile->id]);
    $profile->photos->logo()->associate(factory(\App\Models\Business\Photo::class)->create())->save();
    $profile->photos->banner()->associate(factory(\App\Models\Business\Photo::class)->create(['name' => 'banner-' . time() . Str::random(5) . '.png']))->save();


    $account = $business->account;
    $account->account_status_id = \App\Models\Business\AccountStatus::where('code', 200)->first()->id;
    $account->save();
    $payFacAccount = factory(\App\Models\Business\PayFacAccount::class)->create(['account_id' => $account->id]);
    factory(\App\Models\Business\PayFacBusiness::class)->create(['pay_fac_account_id' => $payFacAccount]);
    
    factory(\App\Models\Business\PayFacOwner::class)->create(['pay_fac_account_id' => $payFacAccount]);
    factory(\App\Models\Business\PayFacBank::class)->create(['pay_fac_account_id' => $payFacAccount]);

    $location = factory(\App\Models\Business\Location::class)->create(['business_id' => $business->id]);
    $business->location->region()->associate(factory(\App\Models\Location\Region::class)->create());
    factory(\App\Models\Business\GeoAccount::class)->create(['location_id' => $location->id]);
    factory(\App\Models\Business\BeaconAccount::class)->create(['location_id' => $location->id]);

    $inventory = factory(\App\Models\Business\Inventory::class)->create(['business_id' => $business->id]);
    factory(\App\Models\Business\ActiveItem::class, 30)->create(['inventory_id' => $inventory->id]);
    factory(\App\Models\Business\InactiveItem::class, 10)->create(['inventory_id' => $inventory->id]);

    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => \App\Models\Business\PosAccountStatus::where('code', 200)->first()->id, 'business_id' => $business->id]);
    factory(\App\Models\Business\SquareAccount::class)->create(['pos_account_id' => $posAccount->id]);

    factory(\App\Models\Business\Employee::class, 12)->create(['business_id' => $business->id]);

    $messages = factory(\App\Models\Business\BusinessMessage::class, 6)->create(['business_id' => $business->id]);
    foreach ($messages as $message) {
      factory(\App\Models\Business\BusinessMessageReply::class, 2)->create(['business_message_id' => $message->id]);
    };

    $customers = factory(\App\Models\Customer\Customer::class, 60)->create();

    foreach ($customers as $customer) {
      $profile = factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
      $photo = factory(\App\Models\Customer\CustomerProfilePhoto::class)->create(['customer_profile_id' => $profile->id]);
      $photo->avatar()->associate(factory(\App\Models\Customer\CustomerPhoto::class)->create());
      factory(\App\Models\Customer\PushToken::class)->create(['customer_id' => $customer->id]);
      $account = factory(\App\Models\Customer\CustomerAccount::class)->create(['customer_id' => $customer->id]);
      factory(\App\Models\Customer\AchCustomer::class)->create(['customer_account_id' => $customer->id]);
      factory(\App\Models\Customer\CardCustomer::class)->create(['customer_account_id' => $customer->id]);
    };
  	

  	foreach ($customers as $customer) {
  		if ($customer->id % 2 == 0) {
  			$codes = \App\Models\Transaction\TransactionStatus::pluck('code')->toArray();
  			$status = \App\Models\Transaction\TransactionStatus::where('code', Arr::random($codes))->first();
  			$employeeIds = \App\Models\Business\Employee::pluck('external_id')->toArray();
  			$employeeId = Arr::random($employeeIds);

  			$netSales = $faker->numberBetween($min = 5, $max = 100) * 100;
  			$tax = $netSales * 0.1;
  			$tip = $status->code > 102 ? ($netSales + $tax) * 0.15 : 0;
  			$total = $netSales + $tax + $tip;

  			
  			$isHistoric = $faker->boolean;

  			$billCreatedAt = $isHistoric ? 
  				Carbon::parse($faker->dateTimeBetween($startDate = '-1 months', $endDate = 'now', $timezone = null)) :
  				Carbon::parse($faker->dateTimeBetween($startDate = '-1 days', $endDate = 'now', $timezone = null));
  			$createdAt = $billCreatedAt->addMinutes($faker->numberBetween($min = 1, $max = 30));
  			$updatedAt = $createdAt->addMinutes($faker->numberBetween($min = 1, $max = 60));

  			$transaction = factory(\App\Models\Transaction\Transaction::class)->create([
  				'customer_id' => $customer->id,
  				'business_id' => $business->id,
  				'status_id' => $status->id,
  				'payment_transaction_id' => $status->code == 200 ? $faker->asciify('***************') : null,
  				'pos_transaction_id' => $faker->asciify('******************'),
  				'employee_id' => $employeeId,
  				'tax' => $tax,
  				'tip' => $tip,
  				'net_sales' => $netSales,
  				'total' => $total,
  				'bill_created_at' => $billCreatedAt,
  				'created_at' => $createdAt,
  				'updated_at' => $updatedAt
  			]);

        factory(\App\Models\Transaction\PurchasedItem::class, $faker->numberBetween($min = 1, $max = 10))->create([
  				'transaction_id' => $transaction->id,
  				'item_id' => Arr::random(\App\Models\Business\ActiveItem::pluck('id')->toArray())
  			]);

  			if ($status->code == 200 && $isHistoric) {
  				factory(\App\Models\Location\HistoricLocation::class)->create([
  					'bill_identifier' => $business->posAccount->type .  '_' . $customer->identifier,
  					'customer_id' => $customer->id,
  					'location_id' => $business->location->id,
  					'transaction_id' => $transaction->id,
  					'created_at' => $updatedAt,
  					'updated_at' => $updatedAt,
  				]);
  			} else {
  				factory(\App\Models\Location\ActiveLocation::class)->create([
  					'bill_identifier' => $business->posAccount->type .  '_' . $customer->identifier,
  					'customer_id' => $customer->id,
  					'location_id' => $business->location->id,
  					'transaction_id' => $transaction->id,
  					'created_at' => $createdAt->subMinutes($faker->numberBetween($min = 5, $max = 20)),
  					'updated_at' => $createdAt->addMinutes($faker->numberBetween($min = 2, $max = 10)),
  				]);
  			}
  			if ($faker->boolean($chanceOfGettingTrue = 20)) {
  				$status = \App\Models\Refund\RefundStatus::all()->random();
  				factory(\App\Models\Refund\Refund::class)->create([
  					'transaction_id' => $transaction->id,
  					'status_id' => $status->id,
  					'total' => round($transaction->total - ($transaction->total * 0.5)),
  					'pos_refund_id' => $faker->asciify('******************'),
  					'payment_refund_id' => $status->code == 200 ? $faker->asciify('*************') : null
  				]);
  			}
  		} else {
  			$isHistoric = $faker->boolean;
  			if ($isHistoric) {
  				$createdAt = Carbon::parse($faker->dateTimeBetween($startDate = '-1 months', $endDate = 'now', $timezone = null));
  				factory(\App\Models\Location\HistoricLocation::class)->create([
  					'bill_identifier' => $business->posAccount->type .  '_' . $customer->identifier,
  					'customer_id' => $customer->id,
  					'location_id' => $business->location->id,
  					'created_at' => $createdAt,
  					'updated_at' => $createdAt,
  				]);
  			} else {
  				$createdAt = Carbon::parse($faker->dateTimeBetween($startDate = '-1 days', $endDate = 'now', $timezone = null));
  				factory(\App\Models\Location\ActiveLocation::class)->create([
  					'bill_identifier' => $business->posAccount->type .  '_' . $customer->identifier,
  					'customer_id' => $customer->id,
  					'location_id' => $business->location->id,
  					'created_at' => $createdAt,
  					'updated_at' => $createdAt,
  				]);
  			}
  		}
  	}
  }
}
