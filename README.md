Laravel-Curl
========


Laravel-Curl is an object-oriented wrapper of the PHP cURL extension.

# Installation
To install the package, simply add the following to your Laravel installation's `composer.json` file

```json
"require": {
	"zjango/laracurl": "dev-master"  
},
```

### In Laravel 5.0

Run the usual `composer update` to pull the files.  Then, add the following **Service Provider** to your `providers` array in your `config/app.php` config.

```php
'providers' => array(
	...
	'Zjango\Curl\CurlServiceProvider',
);
```

And finally add a new line to the aliases array:

```php
		'Curl'	=>	'Zjango\Curl\Facades\Curl',
```

### In Laravel 5.2

Run the usual `composer update` to pull the files.  Then, add the following **Service Provider** to your `providers` array in your `config/app.php` config.

```php
'providers' => array(
	...
	Zjango\Curl\CurlServiceProvider::class,
);
```

And finally add a new line to the aliases array:

```php
		'Curl'  =>  Zjango\Curl\Facades\Curl::class,
```

# Quick Start and Examples

**Simple GET Request**
```php
	Curl::get('http://www.example.com/');
```

**Easily Build URL With Query String Attached**
```php
	Curl::buildUrl('http://www.example.com/search', array(
		'q' => 'keyword',
	));
```

**Easily GET Request With Query String Attached**
```php
	Curl::get('http://www.example.com/search', array(
		'q' => 'keyword',
	));
```

**post() accepts array of POST data**
```php
	Curl::post('http://www.example.com/login/', array(
		'username' => 'myusername',
		'password' => 'mypassword',
	));
```

**Prefix 'json' to method to post as JSON**
//todo

**Prefix 'raw' to method to post as JSON**
//todo

```php
$curl = new Curl;
$curl->setBasicAuthentication('username', 'password');
$curl->setUserAgent('');
$curl->setReferrer('');
$curl->setHeader('X-Requested-With', 'XMLHttpRequest');
$curl->setCookie('key', 'value');
$curl->get('http://www.example.com/');
if ($curl->error) {
    echo $curl->error_code;
}
else {
    echo $curl->body;
}
var_dump($curl->request_headers);
var_dump($curl->response_headers);
```

```php
$curl = new Curl;
$curl->setopt(CURLOPT_RETURNTRANSFER, TRUE);
$curl->setopt(CURLOPT_SSL_VERIFYPEER, FALSE);
$curl->get('https://encrypted.example.com/');
```

```php
Curl::put('http://api.example.com/user/', array(
    'first_name' => 'Zach',
    'last_name' => 'Borboa',
));
```

```php
Curl::patch('http://api.example.com/profile/', array(
    'image' => '@path/to/file.jpg',
));
```

```php
Curl::delete('http://api.example.com/user/', array(
    'id' => '1234',
));
```

```php
$curl->close();
```

###The Response Object###

The `$response` variable in above examples represents an object as well.

```php

// Return cURL and http status (bool)
$response->error

// Return cURL error or http error code
$response->error_code

// cURL error or http error message
$response->error_message

// curl error (bool)
$response->curl_error

// curl error code
$response->curl_error_code

// curl error message
$response->curl_error_message


// http error  (bool)
$response->http_error

// Response http status code
$response->http_status_code

// Response http error message
$response->http_error_message

// Request _cookies
$response->_cookies


// set _headers
$response->_headers

// Request request_headers
$response->request_headers

// Return Headers
$response->response_headers

// cURL Info
$response->info

// Response Body
$response->body

```

