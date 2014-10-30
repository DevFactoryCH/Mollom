<?php namespace Devfactory\Mollom\Facades;

use Illuminate\Support\Facades\Facade;

class Mollom extends Facade {

  /**
   * Get the registered name of the component.
   *
   * @return string
   */
  protected static function getFacadeAccessor() { return 'mollom'; }

}