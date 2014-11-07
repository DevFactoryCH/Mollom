<?php namespace Devfactory\Mollom;

use Illuminate\Support\ServiceProvider;
use Devfactory\Mollom\Client;

class MollomServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('devfactory/mollom', 'mollom', __DIR__);

    require __DIR__ . '/validation.php';
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
    $this->app['mollom'] = $this->app->share(function($app) {
      return new Client(null, $app['request']);
    });
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('mollom');
	}

}
