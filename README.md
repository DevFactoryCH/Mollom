Mollom
======

Mollom for laravel 4.2

[![Build Status](https://travis-ci.org/DevFactoryCH/Mollom.svg)](https://travis-ci.org/DevFactoryCH/Mollom)
[![Code Climate](https://codeclimate.com/github/DevFactoryCH/Mollom/badges/gpa.svg)](https://codeclimate.com/github/DevFactoryCH/Mollom)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/DevFactoryCH/mollom/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/DevFactoryCH/mollom/?branch=master)
[![Test Coverage](https://codeclimate.com/github/DevFactoryCH/Mollom/badges/coverage.svg)](https://codeclimate.com/github/DevFactoryCH/Mollom)
[![Latest Stable Version](https://poser.pugx.org/devfactory/mollom/v/stable.svg)](https://packagist.org/packages/devfactory/mollom)
[![Total Downloads](https://poser.pugx.org/devfactory/mollom/downloads.svg)](https://packagist.org/packages/devfactory/mollom)
[![License](https://poser.pugx.org/devfactory/Mollom/license.svg)](https://packagist.org/packages/devfactory/Mollom)


##How to setup

update `composer.json` file:

```json
{
    "require": {
        "devfactory/mollom": "1.0.5"
    }
}
```

and run `composer update` from terminal to download files.

update `app.php` file in `app/config` directory:

```php
'providers' => array(
  'Devfactory\Mollom\MollomServiceProvider',
),
```

```php
alias => array(
    'Mollom'          => 'Devfactory\Mollom\Facades\Mollom',
),
```

##Configuration

```php
 php artisan config:publish devfactory/mollom
```

```php
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

```

##How to use captcha
in your blade add following code:

```php
{{ Mollom::captcha('cpachaID') }}
{{ Form::text('capchaInput') }}

```

and for validate user entered data just add `mollom` to array validation rules.

```php
$rules = array(
  'capchaInput' => 'required|mollom:cpachaID'
);

$validator = Validator::make(Input::all(), $rules);

if($validator -> fails()) {
  return Redirect::back() -> withErrors($validator);
}
```

##How to check the comment spam
This method will contact mollom to check a content

```php
    $comment = array(
        'title' => 'comment title',
        'body' => 'body comment',
        'name' => 'authorName',
        'mail' => 'authorEmail'
    );

    try {
      $result = Mollom::comment($comment);

    } catch (\Devfactory\Mollom\Exceptions\UnknownSpamClassificationException $e) {
      //Mollom return anothor value
    } catch (\Devfactory\Mollom\Exceptions\SystemUnavailableException $e) {
      // Unable to contact mollom
    }
```
And return :

```php
'ham'    //Is not a spam

'spam'   //Is a spam

'unsure' //Not sure you should display a captcha
```
