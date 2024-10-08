<?php

namespace Feedback;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Feedback\Interfaces\ClientInterface;

final class Feedback
{
    private ClientInterface $connector;

    public function __construct(ClientInterface $connector)
    {
        $this->connector = $connector;
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function send(RequestInterface $request): ResponseInterface
    {
        return $this->connector->send($request);
    }
}