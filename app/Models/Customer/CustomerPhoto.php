<?php

namespace App\Models\Customer;

use Image;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\Customer\CustomerProfilePhoto;

class CustomerPhoto extends Model {

	//////////////////// Attribute Mods/Helpers ////////////////////

	protected $fillable = ['name', 'small_url', 'large_url'];
	protected $hidden = ['id', 'created_at', 'updated_at'];

	//////////////////// Core Methods ////////////////////

	public function baseDir() {
    return 'customer/photos';
  }

  //////////////////// Relationships ////////////////////

	public function avatar() {
		return $this->hasOne('App\Models\Customer\CustomerProfilePhoto', 'avatar_id');
	}

	//////////////////// Accessor Methods ////////////////////

	public function getSmallUrlAttribute($smallUrl) {
		return url(Storage::url($smallUrl));
	}

	public function getLargeUrlAttribute($largeUrl) {
		return url(Storage::url($largeUrl));
	} 

	//////////////////// Core Methods ////////////////////

	public function createPhoto($photoData, $profile) {
		$this->deleteOldPhoto($profile);
		$this->createAvatar($photoData['avatar']);
		return $this->setCustomerProfilePhoto($profile);
	}

	public function setCustomerProfilePhoto($profile) {
		$profilePhoto = $this->getCustomerProfilePhoto($profile);
		$profilePhoto->avatar_id = $this->id;
		$profilePhoto->save();
		return $profilePhoto;
	}

	public function getCustomerProfilePhoto($profile) {
		if (!$profile->photo) {
			return new CustomerProfilePhoto(['customer_profile_id' => $profile->id]);
		}
		return $profile->photo;
	}

	public function createAvatar($file) {
		$largeDimensions = [500, 500];
		$smallDimensions = [250, 250];

		$fileName = $this->createFileName($file);

		$urlPathName = 'lg-' . $fileName;
		$largeUrl = $this->makeAvatar($file, $largeDimensions, $urlPathName);

		$urlPathName = 'sm-' . $fileName;
		$smallUrl = $this->makeAvatar($file, $smallDimensions, $urlPathName);

		$this->fillCustomerPhotoAttributes($fileName, $smallUrl, $largeUrl);
	}

	public function fillCustomerPhotoAttributes($fileName, $smallUrl, $largeUrl) {
		$this->fill([
			'name' => $fileName,
			'small_url' => $smallUrl,
			'large_url' => $largeUrl
		])->save();
	}

	public function makeAvatar($file, $dimensions, $urlPathName) {
		$avatar = $this->shapePhoto($file, $dimensions);
		$avatarPath = $this->createAvatarPath($urlPathName);
		$this->storeAvatar($avatarPath, $avatar);
		return $avatarPath;
	}

	public function storeAvatar($avatarPath, $avatar) {
		Storage::disk('public')->put($avatarPath, $avatar);
	}

	public function createAvatarPath($urlPathName) {
		return $this->baseDir() . '/' . $urlPathName;
	}

	public function shapePhoto($file, $dimensions) {
		$avatar = Image::make($file)
			->fit($dimensions[0], $dimensions[1], function($constraint) {
				$constraint->upsize();
			}, 'center')->encode('png');
		return $avatar;
	}

	public function createFileName($file) {
		return 'avatar-' . time() . Str::random(5) . '.png';
	}

	public function deleteOldPhoto($profile) {
		if ($profile->photo && $profile->photo->avatar_id != null) {
			$avatar = CustomerPhoto::where('id', $profile->photo->avatar_id)->first();
			$this->removeOldAvatarFromStorage($avatar);
			$avatar->delete();
		}
	}

	public function removeOldAvatarFromStorage($avatar) {
		Storage::disk('public')->delete([
			$this->baseDir() . '/sm-' . $avatar->name,
			$this->baseDir() . '/lg-' . $avatar->name
		]);
	}
}
