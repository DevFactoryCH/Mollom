<?php

Validator::extend('mollom', function($attribute, $value, $parameters)
{
    return Mollom::validate($value, $parameters);
});