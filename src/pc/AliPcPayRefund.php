<?php

namespace BusyPHP\alipay\pay\pc;

use BusyPHP\alipay\pay\AliPayRefund;

/**
 * 电脑网站支付退款
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/11/8 下午10:39 AliPcPayRefund.php $
 */
class AliPcPayRefund extends AliPayRefund
{
    /**
     * 获取配置名称
     * @return string
     */
    protected function getConfigKey() : string
    {
        return 'pc';
    }
}