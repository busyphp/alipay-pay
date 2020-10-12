<?php

namespace BusyPHP\alipay\pay\pc;


use BusyPHP\alipay\pay\AliPayRefund;

/**
 * 电脑网站支付退款
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2018 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2018-06-06 上午10:13 AliPcPayRefund.php $
 */
class AliPcPayRefund extends AliPayRefund
{
    /**
     * 获取配置名称
     * @return string
     */
    protected function getConfigKey()
    {
        return 'pc';
    }
}