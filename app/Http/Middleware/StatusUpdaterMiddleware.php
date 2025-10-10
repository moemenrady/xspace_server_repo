<?php

namespace App\Http\Middleware;

use App\Support\StatusUpdater;
use Closure;
use Illuminate\Http\Request;

class StatusUpdaterMiddleware
{
  public function handle(Request $request, Closure $next)
  {
    try {
            StatusUpdater::run();
    } catch (\Throwable $e) {
      \Log::error('StatusUpdater error: ' . $e->getMessage());
    }

    return $next($request);
  }
}
