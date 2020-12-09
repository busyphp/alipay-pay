<?php

namespace BusyPHP\alipay\pay\pc;

use BusyPHP\alipay\pay\AliPayRefundQuery;

/**
 * PC支付退款查询程序
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/11/25 下午9:04 下午 AliPcPayRefundQuery.php $
 */
class AliPcPayRefundQuery extends AliPayRefundQuery
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