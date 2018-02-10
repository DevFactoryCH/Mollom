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
    $this->publishConfig();

    require __DIR__ . '/validation.php';
	}

  /**
   * Register the service provider.
   *
   * @return void
   */
  public function register() {
    $this->registerServices();
  }

  /**
   * Get the services provided by the provider.
   *
   * @return array
   */
  public function provides() {
    return ['mollom'];
  }

	/**
   * Register the package services.
   *
   * @return void
   */
  protected function registerServices() {
    $this->app->singleton('mollom', function ($app) {
      return new Client(null, $app['request']);
    });
  }

  /**
   * Publish the package configuration
   */
  protected function publishConfig() {
    $this->publishes([
      __DIR__ . '/config/config.php' => config_path('mollom.php'),
    ]);
  }

}
