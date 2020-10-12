<?php

namespace BusyPHP\alipay\pay;

use BusyPHP\alipay\AlipayConfig;
use BusyPHP\App;
use BusyPHP\helper\net\Http;
use BusyPHP\Request;
use Throwable;

/**
 * 支付基本类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/7/8 下午9:19 下午 AliPayPay.php $
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
    protected $rsaPrivatePath;
    
    /**
     * 公钥文件路径
     * @var string
     */
    protected $rsaPublicPath;
    
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
     * AliPayPay constructor.
     * @throws AliPayPayException
     */
    public function __construct()
    {
        $name = "pay.{$this->getConfigKey()}.";
        
        $this->app            = app();
        $this->request        = $this->app->request;
        $this->type           = $this->getConfig($name . 'type', '');
        $this->appId          = $this->getConfig($name . 'app_id', '');
        $this->email          = $this->getConfig($name . 'email', '');
        $this->pattern        = $this->getConfig($name . 'pattern', '');
        $this->rsaPrivatePath = $this->getConfig($name . 'rsa_private_path', '');
        $this->rsaPublicPath  = $this->getConfig($name . 'rsa_public_path', '');
        $this->isRsa2         = $this->getConfig($name . 'is_rsa2', true);
        
        if (!$this->appId) {
            throw new AliPayPayException('没有配置参数: app_id');
        }
        if (!is_file($this->rsaPrivatePath)) {
            throw new AliPayPayException('没有配置参数或文件不存在: rsa_private_path');
        }
        if (!is_file($this->rsaPublicPath)) {
            throw new AliPayPayException('没有配置参数或文件不存在: rsa_public_path');
        }
    }
    
    
    /**
     * 获取配置名称
     * @return string
     */
    protected abstract function getConfigKey();
    
    
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
            $result = Http::get('https://mapi.alipay.com/gateway.do', [
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
     * 通用RSA签名
     * @param string $data 待签名数据
     * @param string $path 密钥路径
     * @param string $tagName 密钥BEGIN END名称
     * @param bool   $isRsa2 是否RSA2
     * @return string 签名结果
     * @throws AliPayPayException
     */
    protected static function rsaSign($data, $path, $tagName = null, $isRsa2 = false)
    {
        $pemTag = !empty($tagName) ? strtoupper($tagName) : 'RSA PRIVATE';
        $begin  = '-----BEGIN ' . $pemTag . ' KEY-----';
        $end    = '-----END ' . $pemTag . ' KEY-----';
        if (!is_file($path)) {
            throw new AliPayPayException('私钥不存在: ' . $path);
        }
        
        $secret = file_get_contents($path);
        $secret = str_replace($begin, '', $secret);
        $secret = str_replace($end, '', $secret);
        $secret = str_replace("\n", '', $secret);
        $secret = $begin . "\n" . wordwrap($secret, 64, "\n", true) . "\n" . $end;
        
        $resource = openssl_get_privatekey($secret);
        if (!$resource) {
            throw new AliPayPayException('私钥不正确');
        }
        
        openssl_sign($data, $sign, $resource, $isRsa2 ? OPENSSL_ALGO_SHA256 : OPENSSL_ALGO_SHA1);
        openssl_free_key($resource);
        
        return base64_encode($sign);
    }
    
    
    /**
     * 通用RSA验签
     * @param string $data 待签名数据
     * @param string $sign 要校对的的签名结果
     * @param string $path 公钥文件路径
     * @param string $tagName 密钥BEGIN END名称
     * @param bool   $isRsa2 是否RSA2
     * @throws AliPayPayException
     */
    protected static function checkRsaSign($data, $sign, $path, $tagName = null, $isRsa2 = false)
    {
        $tagName = !empty($tagName) ? strtoupper($tagName) : 'PUBLIC';
        $begin   = '-----BEGIN ' . $tagName . ' KEY-----';
        $end     = '-----END ' . $tagName . ' KEY-----';
        
        if (!is_file($path)) {
            throw new AliPayPayException('公钥不存在: ' . $path);
        }
        $secret   = file_get_contents($path);
        $secret   = str_replace($begin, '', $secret);
        $secret   = str_replace($end, '', $secret);
        $secret   = str_replace("\n", "", $secret);
        $secret   = $begin . "\n" . wordwrap($secret, 64, "\n", true) . "\n" . $end;
        $resource = $isRsa2 ? openssl_pkey_get_public($secret) : openssl_get_publickey($secret);
        if (!$resource) {
            throw new AliPayPayException('公钥格式不正确!');
        }
        
        $result = openssl_verify($data, base64_decode($sign), $resource, $isRsa2 ? OPENSSL_ALGO_SHA256 : OPENSSL_ALGO_SHA1);
        openssl_free_key($resource);
        if ($result != 1) {
            throw new AliPayPayException('RAS签名校验错误');
        }
    }
    
    
    /**
     * 通用生成待参与运算的签名字符串
     * @param array        $params 待签名数据
     * @param string|array $filterKeys 要过滤的键名称，多个用逗号隔开
     * @return string 签名
     */
    protected static function createSignTemp($params, $filterKeys = '')
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
}