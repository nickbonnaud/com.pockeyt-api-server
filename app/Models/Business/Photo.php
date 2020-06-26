<?php

namespace App\Models\Business;

use Image;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Photo extends Model {
	
	//////////////////// Attribute Mods/Helpers ////////////////////

	protected $fillable = ['name', 'small_url', 'large_url'];
	protected $hidden = ['id'];

	//////////////////// Relationships ////////////////////

	public function logo() {
		return $this->hasOne('App\Models\Business\ProfilePhotos', 'logo_id');
	}

	public function banner() {
		return $this->hasOne('App\Models\Business\ProfilePhotos', 'banner_id');
	}

	//////////////////// Accessor Methods ////////////////////

	public function getSmallUrlAttribute($smallUrl) {
		return url(Storage::url($smallUrl));
	}

	public function getLargeUrlAttribute($largeUrl) {
		return url(Storage::url($largeUrl));
	}

	//////////////////// Core Methods ////////////////////

	public function baseDir() {
    return 'business/photos';
  }

	public function createPhoto($photoData, $profile) {
		$file = $photoData['photo'];
		$isLogo = $photoData['is_logo'];
		$this->deleteOldPhoto($profile, $isLogo);
		$photo = $this->createPhotos($file, $isLogo);
		return $this->setProfilePhotos($profile, $isLogo);
	}

	public function setProfilePhotos($profile, $isLogo) {
		if ($isLogo) {
			$profile->photos->logo_id = $this->id;
		} else {
			$profile->photos->banner_id = $this->id;
		}
		($profile->photos)->save();
		return $profile->photos;
	}

	public function createPhotos($file, $isLogo) {
		$largeUrlDimensions = $isLogo ? [400, 400] : [1000, 650];
		$smalllUrlDimensions = $isLogo ? [200, 200] : [500, 325];
		$fileName = $this->createFileName($file, $isLogo);

		$urlPathName = 'lg-' . $fileName;
		$largeUrl = $this->makePhoto($file, $isLogo, $largeUrlDimensions, $urlPathName);
		$urlPathName = 'sm-' . $fileName;
		$smallUrl = $this->makePhoto($file, $isLogo, $smalllUrlDimensions, $urlPathName);
		
		$this->fillPhotoAttributes($fileName, $smallUrl, $largeUrl);
		return $this;
	}

	public function fillPhotoAttributes($fileName, $smallUrl, $largeUrl) {
		$this->fill([
			'name' => $fileName,
			'small_url' => $smallUrl,
			'large_url' => $largeUrl
		])->save();
	}

	public function makePhoto($file, $isLogo, $dimensions, $fileName) {
		$photo = $this->shapePhoto($file, $dimensions);
		$photoPath = $this->createPhotoPath($fileName);
		$this->storePhoto($photoPath, $photo);
		return $photoPath;
	}

	public function createFileName($file, $isLogo) {
		$prefix = $isLogo ? 'logo-' : 'banner-';
		return $prefix . time() . Str::random(5) . '.png';
	}

	public function shapePhoto($file, $dimensions) {
		$photo = Image::make($file)
			->fit($dimensions[0], $dimensions[1], function($constraint) {
				$constraint->upsize();
			}, 'center')->encode('png');
			return $photo;
	}

	public function createPhotoPath($filename) {
		return $this->baseDir() . '/' . $filename;
	}

	public function storePhoto($photoPath, $photo) {
		Storage::disk('public')->put($photoPath, $photo);
	}

	public function deleteOldPhoto($profile, $isLogo) {
		if ($isLogo) {
			if ($profile->photos->logo_id != null) {
				$logo = Photo::where('id', $profile->photos->logo_id)->first();
				$this->removeOldPhotoFromStorage($logo);
				$logo->delete();
			}
		} else {
			if ($profile->photos->banner_id != null) {
				$banner = Photo::where('id', $profile->photos->banner_id)->first();
				$this->removeOldPhotoFromStorage($banner);
				$banner->delete();
			}
		}
	}

	public function removeOldPhotoFromStorage($photo) {
		Storage::disk('public')->delete([
				$this->baseDir() . '/sm-' . $photo->name,
				$this->baseDir() . '/lg-' . $photo->name
		]);
	}
}
