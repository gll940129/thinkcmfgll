<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/3/4
 * Time: 17:27
 */

namespace plugins\pay_pal\lib;


class Config
{
    public static function get($key = false)
    {
        $data   = [];
        $config = cmf_get_plugin_config('PayPal');
        $dev    = isset($config['dev']) ? $config['dev'] : false;

        $config = [
            // 支付宝支付参数
            'paypal' => [
                'debug'       => $dev, // 沙箱模式
                'pay_pal_client_id'      => isset($config['pay_pal_client_id']) ? $config['pay_pal_client_id'] : '', // 应用ID
                'pay_pal_secret'  => isset($config['pay_pal_secret']) ? $config['pay_pal_secret'] : '',
                'notify_url'  => cmf_plugin_url('PayPal://PayPal/payRedirect', [], true), // 支付通知URL
                'return_url'  => cmf_url('order/order/index',[],true,true),
            ]
        ];
        if($key && isset($config[$key])){
            return $config[$key];
        }
        return $config;
    }
}