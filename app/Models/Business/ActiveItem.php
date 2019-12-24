<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;

class ActiveItem extends Model {

	//////////////////// Attribute Mods/Helpers ////////////////////

	protected $guarded = [];
	protected $hidden = [ 'id', 'inventory_id'];

	//////////////////// Relationships ////////////////////

	public function inventory() {
		return $this->belongsTo('App\Models\Business\Inventory');
	}

	public function purchasedItems() {
		return $this->hasMany('App\Models\Transaction\PurchasedItem', 'item_id');
	}

	public function unassignedPurchasedItems() {
		return $this->hasMany('App\Models\Transaction\UnassignedPurchasedItem', 'item_id');
	}

	//////////////////// Core Methods ////////////////////

	public static function findOrCreateItemSquare($item, $business) {
		return self::firstOrCreate(
			[
				'inventory_id' => $business->inventory->id,
				'main_id' => $item['item_detail']['item_id'],
				'sub_id' => $item['item_detail']['item_variation_id'],
			],
			[
				'name' => $item['name'],
				'sub_name' => $item['item_variation_name'],
				'category' => $item['item_detail']['category_name'],
				'price' => $item['single_quantity_money']['amount']
			]
		);
	}

	public static function findOrCreateItemClover($item, $business) {
		return self::firstOrCreate(
			[
				'inventory_id' => $business->inventory->id,
				'main_id' => $item['item']['id'],
			],
			[
				'name' => $item['name'],
				'sub_name' => $item['alternateName'] == "" ? null : $item['alternateName'],
				'price' => $item['price']
			]
		);
	}

	public static function findOrCreateItemLightspeedRetail($item, $business) {
		return self::firstOrCreate(
			[
				'inventory_id' => $business->inventory->id,
				'main_id' => $item['itemID']
			],
			[
				'name' => $item['Item']['description'],
				'price' => $item['unitPrice']
			]
		);
	}

	public static function findOrCreateItemShopify($item, $business) {
		return self::firstOrCreate(
			[
				'inventory_id' => $business->inventory->id,
				'main_id' => $item['product_id'],
				'sub_id' => $item['variant_id']
			],
			[
				'name' => $item['title'],
				'sub_name' => $item['variant_title'],
				'price' => $item['price'] * 100
			]
		);
	}

	public static function findItemVend($item, $business) {
		return self::where('inventory_id', $business->inventory->id)->where('main_id', $item->product_id)->first();
	}

	public static function createItemVend($productData, $business) {
		return self::create([
			'inventory_id' => $business->inventory->id,
			'main_id' => $productData['id'],
			'name' => $productData['name'],
			'sub_name' => $productData['variant_name'],
			'price' => $productData['price_excluding_tax'] * 100
		]);
	}
}
