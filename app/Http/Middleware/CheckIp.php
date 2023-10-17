<?php

namespace App\Http\Middleware;

use Closure;
use Carbon;
use App\Models\User\UserIp;

class CheckIp
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(!$request->user()) {
            if(UserIp::where('ip', $request->ip())->exists()) {
                if (UserIp::where('ip', $request->ip())->where('is_user_banned', 1)->exists()) return redirect('/ip-block');
            }
        }
        else {
            if($request->user()->is_banned) {
                return redirect('/banned');
            }
        }

        return $next($request);
    }
}
