# paulbunyannet/api 

[![Build Status](https://travis-ci.org/paulbunyannet/api.svg?branch=master)](https://travis-ci.org/paulbunyannet/api)
[![Latest Version](https://img.shields.io/packagist/v/paulbunyannet/api.svg?style=flat-square)](https://packagist.org/packages/paulbunyannet/api)

** =paulbunyannet/api** Shortcut for making http calls to json endpoint


## Installation

This project can be installed via [Composer]:

``` bash
$ composer require paulbunyannet/api:^1.0
```

## Request Methods:

### Get

```php
$getArgs = ['something' => ,'something_else']; 
$get = new Get('https://pathtoapi.com/get/?'.http_build_query($getArgs));
$retrieve = $get->retrieve();
var_dump($retrieve); // { "some_response_key" => "some_response_value" }
```

```php
$getArgs = ['something' => ,'something_else']; 
$get = new Get('https://pathtoapi.com/get/?'.http_build_query($getArgs));
$retrieve = $get->retrieve();
var_dump($retrieve); // { "some_response_key" => "some_response_value" }
```

### Post

```php
$postArgs = ['something' => ,'something_else']; 
$post = new Post('https://pathtoapi.com/post');
$retrieve = $post->retrieve($postArgs);
var_dump($retrieve); // { "some_response_key" => "some_response_value" }
```

### Put

```php
$postArgs = ['something' => ,'something_else']; 
$put = new Put('https://pathtoapi.com/put');
$retrieve = $put->retrieve($postArgs);
var_dump($retrieve); // { "some_response_key" => "some_response_value" }
```

### Delete

```php
$delete = new Put('https://pathtoapi.com/delete/user/1');
$retrieve = $delete->retrieve();
var_dump($retrieve); // { "success" => "true" }
```

## Headers

Header array can be passed into a new object:

```php
$getArgs = ['something' => ,'something_else']; 
$get = new Get('https://pathtoapi.com/get/?'.http_build_query($getArgs));
// headers to pass with request
$get->setHeaders(['headerKey' => 'headerValue']);
$retrieve = $get->retrieve();
var_dump($retrieve); // { "some_response_key" => "some_response_value" }
```

The header "headerKey: headerValue" will then be passed on with the REST request

## Authentication

Each request method can use payload authentication and then check that payload on the receiving end

```php
// on the sender side:
$postArgs = ['something' => ,'something_else'];
$identity = 'my-user-name'; // used for looking up private key on the receiving side
$privateKey = "my-super-secret-key";
$post = new Post('https://pathtoapi.com/post');
$post->setPayload([Auth\AuthBootstrap::IDENTITY => $identity, Auth\AuthBootstrap::PRIVATEKEY => $privateKey]);
$retrieve = $post->retrieve($postArgs);

// then on the receiving side:
$lookUpPrivateKey = 'my-super-secret-key'; // this is where you would do a lookup for user's private key by the ideney key that was sent with the request
$receiver = new Auth\Receive($_POST['payload'], $lookUpPrivateKey);
$verifyHash = receiver->verifyHash($_POST); // will return true if payload hash sent is correct
```