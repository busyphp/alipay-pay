<?php

namespace BusyPHP\alipay\pay;

use BusyPHP\exception\AppException;

/**
 * 支付宝支付异常类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/7/8 下午8:32 下午 AliPayException.php $
 */
class AliPayPayException extends AppException
{
    public function __construct($message = "", $code = 0, $subMsg = '', $subCode = '')
    {
        $this->setData('ALIPAY ERROR', [
            'Message' => $message,
            'Code'    => $code,
            'SubCode' => $subCode,
            'SubMsg'  => $subMsg
        ]);
        
        parent::__construct("{$message}, Code: {$code}, SubMsg: {$subMsg}, SubCode: {$subCode}");
    }
}