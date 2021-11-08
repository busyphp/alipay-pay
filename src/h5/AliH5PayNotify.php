<?php

namespace BusyPHP\alipay\pay\h5;

use BusyPHP\alipay\pay\AliPayNotify;

/**
 * 手机网站支付结果异步通知
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/11/8 下午10:47 AliH5PayNotify.php $
 * @see https://docs.open.alipay.com/203/105286/
 */
class AliH5PayNotify extends AliPayNotify
{
    /**
     * 获取配置名称
     * @return string
     */
    protected function getConfigKey() : string
    {
        return 'h5';
    }
}