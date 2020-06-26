<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Model;
use App\Models\Transaction\TransactionStatus;

class AchCustomer extends Model {
  
  //////////////////// Attribute Mods/Helpers ////////////////////

	protected $fillable = ['customer_url', 'funding_source_url'];
	protected $hidden = ['id', 'customer_account_id', 'created_at', 'updated_at', 'customer_url', 'funding_source_url'];

	//////////////////// Relationships ////////////////////

	public function account() {
		return $this->belongsTo('App\Models\Customer\CustomerAccount', 'customer_account_id');
	}

	//////////////////// Core Methods ////////////////////

	public function pay($transaction) {
		$businessUrl = $transaction->business->account->achAccount->funding_url;
		$fee = $transaction->total * env('PERCENT_FEE') + env('SET_FEE');
		$request = [
			'_links' => [
				'source' => [
					'href' => $this->funding_source_url
				],
				'destination' => [
					'href' => $businessUrl
				]
			],
			'amount' => [
				'value' => strval($transaction->total / 100),
				'currency' => 'USD',
			],
			'fees' => [
		    [
	        '_links' => [
           'charge-to' => [
              'href' => $businessUrl
           ]
	        ],
	        'amount' => [
           'value' => strval($fee / 100),
           'currency' => 'USD'
	        ]
		    ]
		  ]
		];
		if (env('APP_ENV') == 'testing') {
			$transferId = 'fake_identifier'; 
		} else {
			$transferApi = new DwollaSwagger\TransfersApi($this->createApiClient());
			$transferId = $transferApi->create($request);
		}

		$transaction->update([
			'payment_transaction_id' => $transferId,
			'status_id' => TransactionStatus::where('code', 103)->first()->id
		]);
	}

	private function createApiClient() {
		DwollaSwagger\Configuration::env('DWOLLA_TOKEN');
		return new DwollaSwagger\ApiClient(ENV('DWOLLA_URL'));
	}
}
