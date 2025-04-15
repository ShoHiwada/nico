<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->user() && auth()->user()->is_admin) {
            // 管理者の場合はリクエストを進める
            return $next($request);
        }

        // 管理者でない場合は別のページにリダイレクト
        return redirect('/dashboard')->with('error', '管理者専用のページです');
    }
}

