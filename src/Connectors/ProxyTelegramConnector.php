<?php

namespace Feedback\Connectors;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Feedback\Interfaces\ClientInterface;
use GuzzleHttp\Client;

class ProxyTelegramConnector implements ClientInterface
{
    protected const HOOK_MESSAGE = 'sendMessage';

    protected const HOOK_PHOTO = 'sendPhoto';

    protected const PROTOCOL = 'https';

    /** @var string  */
    protected string $token;

    /** @var string  */
    protected string $chatId;

    /** @var string  */
    protected string $apiUrl = 'api.telegram.org';

    /** @var array<string> */
    protected array $allowHeaders = [
        'Accept',
        'Accept-Encoding',
        'Pragma',
        'User-Agent',
    ];

    public function __construct(string $token, string $chatId)
    {
        $this->token = $token;
        $this->chatId = $chatId;
    }

    /**
     * @param Request $request
     * @return ResponseInterface
     * @throws ClientExceptionInterface
     * @throws GuzzleException
     */
    public function send(RequestInterface $request): ResponseInterface
    {
        return $this->methodSupport($request) ?
            $this->sendWithAttachment($request) :
            $this->sendMessage($request);
    }

    /**
     * @param Request $request
     * @return bool
     */
    protected function methodSupport(RequestInterface $request): bool
    {
        return $this->getLastAction($request) == self::HOOK_PHOTO &&
            $request->getMethod() === 'POST'
        ;
    }

    /**
     * @param RequestInterface $request
     * @return string
     */
    private function getLastAction(RequestInterface $request): string
    {
        $uri = explode('/', $request->getUri()->getPath());
        return last($uri) ?? '';
    }

    /**
     * @param Request $request
     * @return ResponseInterface
     * @throws ClientExceptionInterface
     */
    protected function sendMessage(RequestInterface $request): ResponseInterface
    {
        $request = $this->filterHeadersRequest($request);

        $proxyUrl = $this->getStartUrl(self::HOOK_MESSAGE, $this->chatId);

        $uri = new Uri($proxyUrl);
        $uri = $uri->withQuery($uri->getQuery() . '&' . $request->getUri()->getQuery());
        $request = $request->withUri($uri);

        $client = new Client();

        return $client->send($request);
    }

    /**
     * @param Request $request
     * @return ResponseInterface
     * @throws ClientExceptionInterface
     */
    protected function sendWithAttachment(RequestInterface $request): ResponseInterface
    {
        $request = $this->filterHeadersRequest($request);

        $proxyUrl = $this->getStartUrl(self::HOOK_PHOTO, $this->chatId);

        $uri = new Uri($proxyUrl);
        $uri = $uri->withQuery($uri->getQuery() . '&' . $request->getUri()->getQuery());
        $request = $request->withUri($uri);

        $client = new Client();

        return $client->send($request);

    }

    /**
     * @param string $hook
     * @param string $chatId
     * @return string
     * @
     */
    protected function getStartUrl(string $hook, string $chatId): string
    {
        return
            self::PROTOCOL . '://' .
            $this->apiUrl . '/' .
            'bot' .
            $this->token . '/'.
            $hook . '?chat_id=' . $chatId
        ;
    }

    /**
     * @param RequestInterface $request
     * @return RequestInterface
     */
    protected function filterHeadersRequest(RequestInterface $request): RequestInterface
    {
        foreach ($request->getHeaders() as $name => $value) {
            if(!in_array($name, $this->allowHeaders)) {
                $request = $request->withoutHeader($name);
            }
        }

        return $request;
    }
}