<?php

namespace BusyPHP\alipay\pay;

use BusyPHP\alipay\AlipayConfig;
use BusyPHP\alipay\pay\app\AliAppPayCreate;
use BusyPHP\alipay\pay\app\AliAppPayNotify;
use BusyPHP\alipay\pay\app\AliAppPayRefund;
use BusyPHP\alipay\pay\app\AliAppPayRefundQuery;
use BusyPHP\alipay\pay\h5\AliH5PayCreate;
use BusyPHP\alipay\pay\h5\AliH5PayNotify;
use BusyPHP\alipay\pay\h5\AliH5PayRefund;
use BusyPHP\alipay\pay\h5\AliH5PayRefundQuery;
use BusyPHP\alipay\pay\pc\AliPcPayCreate;
use BusyPHP\alipay\pay\pc\AliPcPayNotify;
use BusyPHP\alipay\pay\pc\AliPcPayRefund;
use BusyPHP\alipay\pay\pc\AliPcPayRefundQuery;
use BusyPHP\App;
use BusyPHP\helper\HttpHelper;
use BusyPHP\helper\RsaHelper;
use BusyPHP\Request;
use BusyPHP\trade\defines\PayType;
use Throwable;

/**
 * 支付基本类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/11/8 下午8:32 AliPayPay.php $
 */
abstract class AliPayPay
{
    use AlipayConfig;
    
    /**
     * AppId
     * @var string
     */
    protected $appId;
    
    /**
     * 私钥文件路径
     * @var string
     */
    protected $privateCert;
    
    /**
     * 公钥文件路径
     * @var string
     */
    protected $publicCert;
    
    /**
     * 是否RSA2加密
     * @var bool
     */
    protected $isRsa2;
    
    /**
     * 支付类型
     * @var string
     */
    protected $type;
    
    /**
     * 账号
     * @var string
     */
    protected $email;
    
    /**
     * 商户号
     * @var string
     */
    protected $pattern;
    
    /**
     * @var App
     */
    protected $app;
    
    /**
     * @var Request
     */
    protected $request;
    
    /**
     * 请求参数
     * @var array
     */
    protected $params = [];
    
    /**
     * 业务参数
     * @var array
     */
    protected $bizContent = [];
    
    
    /**
     * AliPayPay constructor.
     * @throws AliPayPayException
     */
    public function __construct()
    {
        $name = "pay.{$this->getConfigKey()}.";
        
        $this->app         = App::getInstance();
        $this->request     = $this->app->request;
        $this->type        = $this->getConfig($name . 'type', '');
        $this->appId       = $this->getConfig($name . 'app_id', '');
        $this->email       = $this->getConfig($name . 'email', '');
        $this->pattern     = $this->getConfig($name . 'pattern', '');
        $this->privateCert = $this->getConfig($name . 'rsa_private_path', '');
        $this->publicCert  = $this->getConfig($name . 'rsa_public_path', '');
        $this->isRsa2      = $this->getConfig($name . 'is_rsa2', true);
        
        if (!$this->appId) {
            throw new AliPayPayException('没有配置参数: app_id');
        }
        if (!is_file($this->privateCert)) {
            throw new AliPayPayException('没有配置参数或文件不存在: rsa_private_path');
        }
        if (!is_file($this->publicCert)) {
            throw new AliPayPayException('没有配置参数或文件不存在: rsa_public_path');
        }
    }
    
    
    /**
     * 获取配置名称
     * @return string
     */
    protected abstract function getConfigKey() : string;
    
    
    /**
     * 获取接口方法
     * @return string
     */
    protected abstract function getMethod() : string;
    
    
    /**
     * 获取远程服务器ATN结果,验证返回URL
     * @param string $notifyId 通知校验ID
     * @throws AliPayPayException
     */
    protected function verifyNotify($notifyId)
    {
        if (!$notifyId) {
            throw new AliPayPayException('notify_id参数为空');
        }
        
        if (!$this->pattern) {
            throw new AliPayPayException('没有配置参数: pattern');
        }
        
        try {
            $result = HttpHelper::get('https://mapi.alipay.com/gateway.do', [
                'service'   => 'notify_verify',
                'partner'   => $this->pattern,
                'notify_id' => trim($notifyId),
            ]);
        } catch (Throwable $e) {
            throw new AliPayPayException("HTTP请求失败: {$e->getMessage()} [{$e->getCode()}]");
        }
        
        
        if ($result != 'true') {
            throw new AliPayPayException('验证ANT结果失败, 返回结果: ' . $result);
        }
    }
    
    
    /**
     * 初始化参数
     */
    protected function initParams()
    {
        $this->params['app_id']      = $this->appId;
        $this->params['method']      = $this->getMethod();
        $this->params['format']      = 'JSON';
        $this->params['charset']     = 'utf-8';
        $this->params['sign_type']   = $this->isRsa2 ? 'RSA2' : 'RSA';
        $this->params['timestamp']   = date('Y-m-d H:i:s');
        $this->params['version']     = '1.0';
        $this->params['biz_content'] = json_encode($this->bizContent, JSON_UNESCAPED_UNICODE);
        
        // 签名
        $this->params['sign'] = RsaHelper::sign(self::temp($this->params, 'sign'), $this->privateCert, true, $this->isRsa2);
    }
    
    
    /**
     * 执行请求
     * @param string $responseKey
     * @return array
     */
    protected function execute(string $responseKey = '') : array
    {
        $this->initParams();
        try {
            $result = HttpHelper::get('https://openapi.alipay.com/gateway.do', $this->params);
        } catch (Throwable $e) {
            throw new AliPayPayException("HTTP请求失败: {$e->getMessage()} [{$e->getCode()}]");
        }
        
        $result = json_decode($result, true) ?: [];
        if ($responseKey) {
            $result         = $result[$responseKey] ?? [];
            $result['code'] = intval($result['code'] ?? 0);
            $result['msg']  = trim((string) ($result['msg'] ?? ''));
            
            if ($result['code'] != 10000) {
                $result['sub_msg']  = trim((string) $result['sub_msg'] ?? '');
                $result['sub_code'] = trim((string) ($result['sub_code'] ?? ''));
                throw new AliPayPayException($result['msg'], $result['code'], $result['sub_msg'], $result['sub_code']);
            }
            
            return $result;
        }
        
        return $result;
    }
    
    
    /**
     * 通用生成待参与运算的签名字符串
     * @param array        $params 待签名数据
     * @param string|array $filterKeys 要过滤的键名称，多个用逗号隔开
     * @return string 签名
     */
    protected static function temp($params, $filterKeys = '')
    {
        if (!is_array($filterKeys)) {
            $filterKeys = explode(',', $filterKeys);
        }
        $filter = array_map('trim', $filterKeys);
        
        
        $array = [];
        foreach ($params as $key => $value) {
            if (in_array($key, $filter) || $value === '') {
                continue;
            }
            $array[$key] = $value;
        }
        
        ksort($array);
        reset($array);
        $query = [];
        foreach ($array as $key => $value) {
            $query[] = "{$key}={$value}";
        }
        
        return implode('&', $query);
    }
    
    
    /**
     * 获取支付宝PC端接口配置
     * @param string $name 名称
     * @param string $alias 别名
     * @param int    $client 客户端类型
     * @return array
     */
    public static function pc(?string $name = null, ?string $alias = null, ?int $client = null) : array
    {
        return [
            'name'          => $name ?? '支付宝网页支付',
            'alias'         => $alias ?? '支付宝',
            'client'        => $client ?? PayType::CLIENT_ALIPAY,
            'create'        => AliPcPayCreate::class,
            'notify'        => AliPcPayNotify::class,
            'refund'        => AliPcPayRefund::class,
            'refund_notify' => '',
            'refund_query'  => AliPcPayRefundQuery::class,
        ];
    }
    
    
    /**
     * 获取支付宝H5端接口配置
     * @param string $name 名称
     * @param string $alias 别名
     * @param int    $client 客户端类型
     * @return array
     */
    public static function h5(?string $name = null, ?string $alias = null, ?int $client = null) : array
    {
        return [
            'name'          => $name ?? '支付宝H5支付',
            'alias'         => $alias ?? '支付宝',
            'client'        => $client ?? PayType::CLIENT_ALIPAY,
            'create'        => AliH5PayCreate::class,
            'notify'        => AliH5PayNotify::class,
            'refund'        => AliH5PayRefund::class,
            'refund_notify' => '',
            'refund_query'  => AliH5PayRefundQuery::class,
        ];
    }
    
    
    /**
     * 获取支付宝APP端接口配置
     * @param string $name 名称
     * @param string $alias 别名
     * @param int    $client 客户端类型
     * @return array
     */
    public static function app(?string $name = null, ?string $alias = null, ?int $client = null) : array
    {
        return [
            'name'          => $name ?? '支付宝APP支付',
            'alias'         => $alias ?? '支付宝',
            'client'        => $client ?? PayType::CLIENT_ALIPAY,
            'create'        => AliAppPayCreate::class,
            'notify'        => AliAppPayNotify::class,
            'refund'        => AliAppPayRefund::class,
            'refund_notify' => '',
            'refund_query'  => AliAppPayRefundQuery::class,
        ];
    }
}