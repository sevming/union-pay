<h1 align="center"> union-pay </h1>

## Installing

```shell
$ composer require sevming/union-pay -vvv
```

## Usage
```php
<?php

use Sevming\UnionPay\UnionPay;;

$unionPay = new UnionPay([
    'log' => [
        'default' => 'prod',
        'channels' => [
            'prod' => [
                'driver' => 'daily',
                'path' => 'unionpay.log',
                'level' => 'info',
            ],
        ],
    ],
    'msg_src' => '',
    'msg_src_id' => '',
    'mid' => '',
    'tid' => '',
    'inst_mid' => '',
    'key' => '',
    'notify_url' => '', 
    'return_url' => '',
]);
// 支付
$paymentUrl = $unionPay->web->wxpay([
    'merOrderId' => date('YmdHis'), 
    'totalAmount' => 1
]);
header('location:' . $paymentUrl);
exit;

// 异步通知
$result = $unionPay->web->verify();
// 响应银联
$unionPay->web->success();
```

## Contributing

You can contribute in one of three ways:

1. File bug reports using the [issue tracker](https://github.com/sevming/union-pay/issues).
2. Answer questions or fix bugs on the [issue tracker](https://github.com/sevming/union-pay/issues).
3. Contribute new features or update the wiki.

_The code contribution process is not very formal. You just need to make sure that you follow the PSR-0, PSR-1, and PSR-2 coding guidelines. Any new code contributions must be accompanied by unit tests where applicable._

## License

MIT