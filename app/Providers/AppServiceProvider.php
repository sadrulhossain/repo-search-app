<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Auth;
use Route;
use Common;
use Session;

class AppServiceProvider extends ServiceProvider {

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
        view()->composer('*', function ($view) {
            if (Auth::check()) {
                $currentControllerFunction = Route::currentRouteAction();
                $controllerName = $currentCont = '';
                if (!empty($currentControllerFunction[1])) {
                    $currentCont = preg_match('/([a-z]*)@/i', request()->route()->getActionName(), $currentControllerFunction);
                    $controllerName = str_replace('controller', '', strtolower($currentControllerFunction[1]));
                }


                $view->with([
                    'controllerName' => $controllerName,
                ]);
            }
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        //
    }

}
