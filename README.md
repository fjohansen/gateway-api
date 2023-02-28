
Gateway-API
=======

A simple implementation of GatewayAPI. A rock solid SMS API (See https://gatewayapi.com)


Installation
------------

Use composer to manage your dependencies and download Gateway-API:

```bash
composer require finna/gateway-api
```

Example
-------
```php
use Finna\GatewayApi\GatewayClient;
use Finna\GatewayApi\ParameterException;

$secret = 'your api key here';

try {
    $result = (new GatewayClient($secret))
        ->setSender('My-name') //You can of course set your phone number to allow replies
        ->setRecipients([12345678, 87654321])
        ->setMessage('Test message from GatewayClient')
        ->send();
    error_log(print_r($result, true));
} catch (ParameterException $exception) {
    error_log('Parameter exception: ' . $exception->getMessage());
} catch (Throwable $exception) {
    error_log('Exception: ' . $exception->getMessage());
}

```