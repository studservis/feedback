<?php

namespace Feedback\Exceptions;

use Exception;
use Throwable;

class IsNotEmptyChatIdTelegram extends Exception implements Throwable
{
    protected $message = 'The chat id is required.';
}