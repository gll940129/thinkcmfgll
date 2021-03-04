<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/3/3
 * Time: 10:33
 */
return [
    //    'dev'             => [
//        'title'   => '第三方支付沙箱',
//        'type'    => 'radio',
//        'options' => [
//            0 => '关闭',
//            1 => '启用'
//        ],
//        'value'   => '0',
//        'tip'     => '沙箱模式'
//    ],
    'pay_pal_client_id'      => [
        'title' => 'payPal client_id',
        'type'  => 'text',
        'value' => '',
        'tip'   => 'payPal client_id'
    ],
    'pay_pal_secret'  => [
        'title' => 'payPal secret',
        'type'  => 'text',
        'value' => '',
        'tip'   => 'payPal secret'
    ],
];