#Api [![Build Status](https://travis-ci.org/paulbunyannet/api.svg?branch=master)](https://travis-ci.org/paulbunyannet/api)
Shortcut for making http calls to json endpoint

## Request Methods:

Each request method can send option header as the second parameter

### Get

```php
$getArgs = ['something' => ,'something_else']; 
$get = new Get('https://pathtoapi.com/get/?'.http_build_query($getArgs));
$retrieve = $get->retrieve();
var_dump($retrieve); // { "some_response_key" => "some_response_value" }
```

```php
$getArgs = ['something' => ,'something_else']; 
$get = new Get('https://pathtoapi.com/get/?'.http_build_query($getArgs), ['auth-key','auth-value']);
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

## Authentication

Each request method can use payload authentication and then check that payload on the receiving end

```php
// on the sender side:
$postArgs = ['something' => ,'something_else'];
$identity = 'my-user-name'; // used for looking up private key on the receiving side
$privateKey = "my-super-secret-key";
$sender = new Auth\Send($identity, $privateKey);
$sender->generateHash($postArgs);

$post = new Post('https://pathtoapi.com/post');
$retrieve = $post->retrieve($postArgs);

// then on the receiving side:
$lookUpPrivateKey = 'my-super-secret-key-found-on-receiver-side'; // this is where you would do a lookup for user's private key by the ident key that was sent
$receiver = new Auth\Receive($_POST['payload'], $lookUpPrivateKey);
$verifyHash = receiver->verifyHash($_POST); // will return true if payload hash sent is correct
```