<?php

namespace Feedback\Connectors;

use Feedback\Exceptions\IsNotEmptyAuthTelegram;
use Feedback\Exceptions\IsNotEmptyChatIdTelegram;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Feedback\Interfaces\ClientInterface;
use Psr\Http\Client\ClientInterface as PsrClientInterface;
use Psr\Http\Message\UriFactoryInterface;

class ProxyTelegramConnector implements ClientInterface
{
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

    /** @var PsrClientInterface */
    private PsrClientInterface $httpClient;

    /** @var UriFactoryInterface */
    private UriFactoryInterface $uriFactory;

    public function __construct(
        PsrClientInterface $httpClient,
        UriFactoryInterface $uriFactory,
        string $token = '',
        string $chatId = ''
    )
    {
        $this->httpClient = $httpClient;
        $this->uriFactory = $uriFactory;
        $this->token = $token;
        $this->chatId = $chatId;
    }

    /**
     * @param RequestInterface $request
     * @return string
     */
    private function getLastAction(RequestInterface $request): string
    {
        $uri = explode('/', $request->getUri()->getPath());
        return end($uri);
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws ClientExceptionInterface|IsNotEmptyAuthTelegram|IsNotEmptyChatIdTelegram
     */
    public function send(RequestInterface $request): ResponseInterface
    {
        $action = $this->getLastAction($request);

        $newRequest = $this->getRequestWithNewUri($request, $action);

        if(!$this->token) {
            throw new IsNotEmptyAuthTelegram();
        }

        if(!$this->chatId) {
            throw new IsNotEmptyChatIdTelegram();
        }

        return $this->httpClient->sendRequest($newRequest);
    }

    /**
     * @param RequestInterface $currentRequest
     * @param string $hook
     * @return RequestInterface
     */
    private function getRequestWithNewUri(RequestInterface $currentRequest, string $hook): RequestInterface
    {
        $request = $this->filterHeadersRequest($currentRequest);
        $proxyUrl = $this->getStartUrl($hook, $this->chatId);

        $uri = $this
            ->uriFactory
            ->createUri($proxyUrl)
            ->withQuery(
                $currentRequest->getUri()->getQuery() . '&' .
                $this->uriFactory->createUri($proxyUrl)->getQuery()
        );

        return $request->withUri($uri);
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