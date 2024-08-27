<?php

namespace Feedback\Exceptions;

use Exception;
use Throwable;

class IsNotEmptyAuthTelegram extends Exception implements Throwable
{
    protected $message = 'The token is required.';
}