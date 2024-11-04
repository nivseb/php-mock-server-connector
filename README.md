# PHP Mock Server Connector

[![Tests](https://img.shields.io/github/actions/workflow/status/nivseb/php-mock-server-connector/test.yml?branch=main&label=Tests)](https://github.com/nivseb/php-mock-server-connector/actions/workflows/tests.yml)
[![Supported PHP Version](https://badgen.net/packagist/php/nivseb/php-mock-server-connector?color=8892bf)](https://www.php.net/supported-versions)
[![Latest Stable Version](https://poser.pugx.org/nivseb/php-mock-server-connector/v/stable.svg)](https://packagist.org/packages/nivseb/php-mock-server-connector)
[![Total Downloads](https://poser.pugx.org/nivseb/php-mock-server-connector/downloads.svg)](https://packagist.org/packages/nivseb/php-mock-server-connector)

PHP Mock Server Connector is a tool that make it easy to use the [MockServer](https://www.mock-server.com) in php based tests.
The method of utilisation is based on the [Mockery](https://github.com/mockery/mockery) project. The creation of 
Expectations is therefore very similar.

### Installation

1. To install PHP Mock Server Connector you can easily use composer.
    ```sh
    composer require --dev nivseb/php-mock-server-connector
    ```
2. You need a running instance of the [MockServer](https://www.mock-server.com/mock_server/getting_started.html#start_mockserver).
3. Existing test setup for php based test. For example a setup with [PHPUnit](https://phpunit.de).

## Usage

### Setup in your tests

After the installation, you can start to use the connector in your tests. The first step is now to connect to the [MockServer instance](https://www.mock-server.com),
for that add the following code to your test. This can be done in a single test case or in another setup part for
your tests (like the setUp Method in PHPUnit tests). But this need the first step that is executed.

```php
    use Nivseb\PhpMockServerConnector\Server;
    MockServer::init('https://your_mock_server.localhost');
```

The next part you must add is this code. It must be placed after your tests, for example in the `tearDown` method
in PHPUnit tests. This code will verify the expectations in your [MockServer instance](https://www.mock-server.com).

```php
    use Nivseb\PhpMockServerConnector\Server;
    MockServer::close();
```

For PHPUnit tests the package comes with the trait `UseMockServer`. This adds
two methods to the test class `initMockServer` and `closeMockServer`. The method `closeMockServer` is called in the
tearDown from the phpunit test. Now your integration can look like this:

```php
    use Nivseb\PhpMockServerConnector\PhpUnit\UseMockServer;
    use PHPUnit\Framework\TestCase;
    
    class YourTest extends TestCase {
        use UseMockServer;
        
        protected function setUp(): void
        {
            parent::setUp();
            $this->initMockServer('https://your_mock_server.localhost');
        }
    }
```

### Create expectation

After you finished the setup for your test cases, you can now add expectations to you tests. First you must create
an instance for an endpoint. This endpoint is the route entry point for a mock. This design allow you that you can build
mocks for different other external apis with only one [MockServer instance](https://www.mock-server.com).

```php
    use Nivseb\PhpMockServerConnector\PhpUnit\MockServerEndpoint;

    $mockServer = new MockServerEndpoint('/rootPath');
```

For every request that you want to mock you call now the allows method. That give you a `PendingExpectation`, this will
create the expectation at your [MockServer instance](https://www.mock-server.com) on destruct or with the call of
the `run` method.

```php
    use Nivseb\PhpMockServerConnector\PhpUnit\UseMockServer;
    use Nivseb\PhpMockServerConnector\PhpUnit\MockServerEndpoint;
    use PHPUnit\Framework\TestCase;
    use GuzzleHttp\Client;
    
    $mockServer = new MockServerEndpoint('/rootPath');
    
    $mockServer->allows('GET', '/firstPath')->andReturn(200, ['data' => 'This is a JSON test content.']);
    // OR
    $myRequest = $mockServer->allows('GET', '/secondPath');
    $myRequest->andReturn(200, ['data' => 'This is a JSON test content.']);
    $myRequest->run();
```

The expectation will be verified on the close call for the mock server, see for that [Setup in your tests](#setup-in-your-tests).

## Supported request expectations 

### methods and uri

You can create expectations for methods and paths in all combinations that are possible with the [MockServer](https://www.mock-server.com).

#### Parameters
 
To add a check for parameters to your expectations, you can call the method `withPathParameters` or `withQueryParameters`. 

```php
    use Nivseb\PhpMockServerConnector\PhpUnit\UseMockServer;
    use Nivseb\PhpMockServerConnector\PhpUnit\MockServerEndpoint;
    use PHPUnit\Framework\TestCase;
    use GuzzleHttp\Client;
    
    // Expected: /test/myExpectedPath?myQueryParameter=myExpectedValue
    $mockServer = new MockServerEndpoint('/test');
    $mockServer
        ->allows('GET', '/{myPathParameter}')
        ->withPathParameters(['myPathParameter' => 'myExpectedPath'])
        ->withQueryParameters(['myQueryParameter' => 'myExpectedValue']);

```

### Request Headers

You can add expected headers in the request by calling the withHeaders method on the pending expectation.

```php
    use Nivseb\PhpMockServerConnector\PhpUnit\UseMockServer;
    use Nivseb\PhpMockServerConnector\PhpUnit\MockServerEndpoint;
    use PHPUnit\Framework\TestCase;
    use GuzzleHttp\Client;
    
    $mockServer = new MockServerEndpoint('/test');
    $mockServer->allows('GET', '/')->withHeaders(['myHeader' => 'myExpectedValue']);
```

### Body

A request body can be expected with a call of the `withBody` method. The Body can be sent as array or string. 

```php
    use Nivseb\PhpMockServerConnector\PhpUnit\UseMockServer;
    use Nivseb\PhpMockServerConnector\PhpUnit\MockServerEndpoint;
    use PHPUnit\Framework\TestCase;
    use GuzzleHttp\Client;
    
    $mockServer = new MockServerEndpoint('/test');
    $mockServer->allows('POST', '/')->withBody(['data' => 'This is a JSON test content.']);
```

### Multiple calls

With the `times` method you can define that a request should be executed multiple times.

## Response

The response for an expectation can be defined in with the `andReturn` method. For the response you can define
the status code, body and headers. 

```php
    use Nivseb\PhpMockServerConnector\PhpUnit\UseMockServer;
    use Nivseb\PhpMockServerConnector\PhpUnit\MockServerEndpoint;
    use PHPUnit\Framework\TestCase;
    use GuzzleHttp\Client;
    
    $mockServer = new MockServerEndpoint('/test');
    $mockServer->allows('GET', '/')->andReturn(200, ['data' => 'This is a JSON test content.']);
```

## Defaults

Every expectation comes with some default values. This example will define, that the request is executed one time and
return an empty response with the status code 200.

```php
    use Nivseb\PhpMockServerConnector\PhpUnit\UseMockServer;
    use Nivseb\PhpMockServerConnector\PhpUnit\MockServerEndpoint;
    use PHPUnit\Framework\TestCase;
    use GuzzleHttp\Client;
    
    $mockServer = new MockServerEndpoint('/');
    $mockServer->allows('GET', '/');
```

## Example

Here you have an example for a full functional PHPUnit test case. 

```php
    use Nivseb\PhpMockServerConnector\PhpUnit\UseMockServer;
    use Nivseb\PhpMockServerConnector\PhpUnit\MockServerEndpoint;
    use PHPUnit\Framework\TestCase;
    use GuzzleHttp\Client;
    
    class ExampleTest extends TestCase {
        use UseMockServer;
        
        protected function setUp(): void
        {
            parent::setUp();
            $this->initMockServer('https://your_mock_server.localhost');
        }

        public function testMyRequest() : void {
            $mockServer = new MockServerEndpoint('/rootPath');
            $mockServer->allows('GET', '/mySubPath')->andReturn(200, ['data' => 'This is a JSON test content.'])
            
            $client = new Client(['base_uri' => 'https://your_mock_server.localhost/rootPath'])
            $response = $this->client->get('/mySubPath');
            
            self::assertEquals(200, $response->getStatusCode());
            self::assertEquals('{"data":"This is a JSON test content."}',$response->getBody()->getContents());
        }
    }
```
