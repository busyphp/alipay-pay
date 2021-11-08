<?php

namespace BusyPHP\alipay\pay\pc;

use BusyPHP\alipay\pay\AliPayRefundQuery;

/**
 * PC支付退款查询程序
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/11/8 下午10:40 AliPcPayRefundQuery.php $
 */
class AliPcPayRefundQuery extends AliPayRefundQuery
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