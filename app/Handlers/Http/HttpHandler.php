<?php

namespace App\Handlers\Http;

use Zttp\Zttp;

class HttpHandler {

	public function get($url, $headers, $queryParams = []) {
		return Zttp::withOptions(['headers' => $headers])->get($url, $queryParams);
	}

	public function post($url, $headers, $body = []) {
		return Zttp::withOptions(['headers' => $headers])->post($url, $body);
	}

	public function put($url, $headers, $body = []) {
		return Zttp::withOptions(['headers' => $headers])->put($url, $body);
	}

	public function delete($url, $headers, $body = []) {
		return Zttp::withOptions(['headers' => $headers])->delete($url, $body);
	}

	public function postFormParams($url, $body, $headers = null) {
		if ($headers) {
			return Zttp::asFormParams()->withHeaders($headers)->post($url, $body);
		} else {
			return Zttp::asFormParams()->post($url, $body);
		}
	}
}