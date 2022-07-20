<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class CmAuthServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $file = app_path('CmAuth/CmAuth.php');
		if(file_exists($file)){
			require_once($file);
		}
    }
}
