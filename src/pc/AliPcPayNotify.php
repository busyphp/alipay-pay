<?php

namespace BusyPHP\alipay\pay\pc;

use BusyPHP\alipay\pay\AliPayNotify;

/**
 * 电脑网站支付结果异步通知
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/11/8 下午10:33 AliPcPayNotify.php $
 * @see https://docs.open.alipay.com/270/105902/
 */
class AliPcPayNotify extends AliPayNotify
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