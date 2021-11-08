<?php

namespace BusyPHP\alipay\pay\app;

use BusyPHP\alipay\pay\AliPayRefund;

/**
 * APP支付退款
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/11/8 下午11:08 AliAppPayRefund.php $
 */
class AliAppPayRefund extends AliPayRefund
{
    /**
     * 获取配置名称
     * @return string
     */
    protected function getConfigKey() : string
    {
        return 'app';
    }
}