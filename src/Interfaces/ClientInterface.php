<?php

namespace Feedback\Interfaces;

use Psr\Http\Message\RequestInterface;

interface ClientInterface
{
    public function send(RequestInterface $request);
}