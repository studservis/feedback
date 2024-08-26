<?php

namespace Feedback\Exceptions;

use Exception;

class NotFoundAction extends Exception
{
    protected $message = "Action not found";
}