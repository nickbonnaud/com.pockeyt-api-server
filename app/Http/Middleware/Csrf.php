<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;

class Csrf {
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure  $next
   * @return mixed
   */
  public function handle($request, Closure $next) {
    if (is_null($request->header('csrf-token')) || is_null(auth('business')->user()) || $request->header('csrf-token') !== auth()->payload()->get('csrf-token')) {
      return response()->json(['message' => 'Permission denied.'], 401);
    }
    return $next($request);
  }
}
