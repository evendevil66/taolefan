<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class CheckWxLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(Cookie::get('openid') != null && Cookie::get('nickname') != null){
                return $next($request);
        }else{
            $app = app('wechat.official_account');
            try {
                $user =$app->oauth->user();
                Cookie::queue('openid', $user->id, 60*24*7);
                Cookie::queue('nickname', $user->name, 60*24*7);
                return $next($request);
            }catch (\Exception $e){
                $response = $app->oauth->scopes(['snsapi_userinfo'])
                    ->redirect($request->fullUrl());
                return $response;
            }
        }
    }
}
