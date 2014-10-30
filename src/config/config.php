<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| Mollom dev Mode
	|--------------------------------------------------------------------------
	|
	| When the dev mode is enabled the package will use the dev.mollom.com api
	|
	*/
  'dev' => false,

	/*
	|--------------------------------------------------------------------------
	| Mollom Public Key
	|--------------------------------------------------------------------------
	|
	| This key is used to comminicate with the mollom api
  | https://mollom.com/user/xxxx/site-manager
	|
	*/
  'mollom_public_key' => '',

	/*
	|--------------------------------------------------------------------------
	| Mollom private Key
	|--------------------------------------------------------------------------
	|
	| This key is used to comminicate with the mollom api
  | https://mollom.com/user/xxxx/site-manager
	|
  */
  'mollom_private_key' => '',

  /*
   | List of ISO 639-1 language codes supported by Mollom.
   |
   | If your application has a predefined list of ISO 639-1 languages already,
   | intersect your list with this via strtok($langcode, '-').
   |
   | example : en
   */
  'mollom_languages_expected' => '',
);
