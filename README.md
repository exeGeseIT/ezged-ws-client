#ezged-ws-client
=======================

**ezged-ws-client** is a simple PHP client for [ezGED's](https://www.ezdev.fr/) web services.
It relies on [symfony/http-client](https://github.com/symfony/http-client) component for the HTTP request layer.


## Installation

```sh
composer require exegeseit/ezged-ws-client
```

After installing, you need to require Composer's autoloader:

```php
require 'vendor/autoload.php';
```


### EzGED API DESCRIPTION
For convenience, you can find EzGED json web services descriptions [here](https://wiki.ezdev.fr/doku.php?id=dev:api:webservices:json)


### USAGE
```php
<?php

require_once 'vendor/autoload.php';

use ExeGeseIT\EzGEDWsClient\EzGEDClient;
use ExeGeseIT\EzGEDWsClient\EzGEDHelper;

$config = [
  'url' => 'https://myserver.io/ezged3',
  'user' => 'wsuser',
  'pwd' => 'YourPassW0$l*',
];;

//$ezWS = new EzGEDClient( $ezgedUrl=$config['url'], $httpclient=null, $apiUser=$config['user'], $apiPwd=$config['pwd'], $sslVerifyPeer=false);
$ezWS = (new EzGEDClient( $config['url'] ))
    ->setApiUser($config['user'])
    ->setApiPwd($config['pwd'])
    ->setSslVerifyPeer(false)
    ;


$response = $ezWS->connect(true);



$ezWS->logout();

```



### TODO:
- [ ] Documentation
