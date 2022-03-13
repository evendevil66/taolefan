<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class CheckAdminLogin
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
        if(Cookie::get('username') != null){
            $admin = app(\App\Models\Admin::class)->getAdminByUsername(Cookie::get('username'));
            if($admin!=null){
                return $next($request);
            }
        }
        return redirect('/admin/login');


    }
}
