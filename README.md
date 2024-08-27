# Сервис по отправке Feedback
## Быстрый старт

Определи переменные окружения

```dotenv
BOT_TOKEN="Youtoken"
BOT_CHAT_ID="YouChatID"
```

Проинициализируй конструктор переменными окружения
```php
$botToken = env('BOT_TOKEN');
$chatId = env('BOT_CHAT_ID');

$client = new ProxyTelegramConnector(string $botToken, string $chatId)
```

## Инициализация сервиса
Сервис инициализируется ClientInterface'ом. Имеется встроенный Клиент ProxyTelegramConnector,
который является interface для ClientInterface. ProxyTelegramConnector проксирующий клиент запросов.
```php
$client = new ProxyTelegramConnector(..., string $botToken, string $chatId) // Implements ClientInterface
```

Для Container это может выглядеть следующим образом

```php
ClientInterface::class => function (ContainerInterface $container) {
    return new ProxyTelegramConnector(
        $container->get(PsrClientInterface::class),
        $container->get(Psr17Factory::class),
        env('BOT_TOKEN') ?? '',
        env('BOT_CHAT_ID') ?? ''
    );
},
```

## Расширяемость
Для использование собственного Клиента, наследуй интерфейс ClientInterface или расширяй ProxyTelegramConnector

```php
use Feedback\Interfaces\ClientInterface;

class CustomConnector implements ClientInterface
{

}
```

или 

```php
use Feedback\Connectors\ProxyTelegramConnector;

class CustomConnector extends ProxyTelegramConnector
{

}
```