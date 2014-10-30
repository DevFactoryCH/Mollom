Mollom
======

Mollom for laravel 4

[![Build Status](https://travis-ci.org/DevFactoryCH/mollom.svg)](https://travis-ci.org/DevFactoryCH/mollom)

##How to setup

update `composer.json` file:

```json
{
    "require": {
        "devfactory/mollom": "0.1.2-alpha"
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

##How to use captcha
in your HTML form add following code:

```php
{{ Mollom::captcha('login') }}
{{ Form::text('input_captcha') }}

```

and for validate user entered data just add `mollom` to array validation rules.

```php
$rules = array(
  'input_captcha' => 'required|mollom:login'
);

$validator = Validator::make(Input::all(), $rules);

if($validator -> fails()) {
  return Redirect::back() -> withErrors($validator);
}
```

##How to check the comment spam
This method will return
ham - OK
spam - is a spam
unsure - display a captcha to be sure is not a spam

```php
    $comment = array('title' => 'comment title', 'body' => 'body comment', 'name' => 'authorName', 'mail' => 'authorEmail');

    try {
      $result = Mollom::comment($comment);

    } catch (\Devfactory\Mollom\Exceptions\UnknownSpamClassificationException $e) {
      //Mollom return anothor value
    } catch (\Devfactory\Mollom\Exceptions\SystemUnavailableException $e) {
      // Unable to contact mollom
    }
```
