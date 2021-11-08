支付宝支付模块
===============

## 说明

用于BusyPHP的支付宝支付，支持wap端、H5端、APP端、PC端支付/退款

## 安装
```
composer require busyphp/alipay-pay
```

## 配置 `config/extend/alipay.php`

```php
<?php

return [
    
    // 支付相关配置
    'pay' => [
        // 电脑网站支付
        'pc'  => [
            // 'type'             => 1,
            // 'email'            => '',
            // 'pattern'          => '',
            // 'app_id'           => '',
            // 'rsa_private_path' => app()->getRootPath() . '私钥路径',
            // 'rsa_public_path'  => app()->getRootPath() . '公钥路径',
            // 'is_rsa2'          => true
        ],
        
        // H5支付
        'h5'  => [
            // 'type'             => 2,
            // 'email'            => '',
            // 'pattern'          => '',
            // 'app_id'           => '',
            // 'rsa_private_path' => app()->getRootPath() . '私钥路径',
            // 'rsa_public_path'  => app()->getRootPath() . '公钥路径',
            // 'is_rsa2'          => true
        ],
        
        // APP支付
        'app' => [
            // 'type'             => 3,
            // 'email'            => '',
            // 'pattern'          => '',
            // 'app_id'           => '',
            // 'rsa_private_path' => app()->getRootPath() . '私钥(pkcs8)路径',
            // 'rsa_public_path'  => app()->getRootPath() . '公钥文件路径',
            // 'is_rsa2'          => false
        ],
    ]
];
```

## 配置 `config/extend/trade.php`
```php
<?php
use BusyPHP\alipay\pay\AliPayPay;
use BusyPHP\trade\defines\PayType;

return [
    // 支付接口绑定
    'apis'            => [
        PayType::ALIPAY_PC => AliPayPay::pc(),
        PayType::ALIPAY_H5 => AliPayPay::h5(),
        PayType::ALIPAY_APP => AliPayPay::app(),
    ]
];
```