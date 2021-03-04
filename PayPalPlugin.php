<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/3/3
 * Time: 9:36
 */
namespace plugins\pay_pal;

use cmf\lib\Plugin;
use think\Db;
use think\exception\HttpResponseException;


class PayPalPlugin extends Plugin
{
    const CMF_PAYPAL        = 'cmf-paypal';

    public $info=[
        'name'        => 'PayPal',
        'title'       => 'PayPal支付插件',
        'description' => 'PayPal支付插件',
        'status'      => 1,
        'author'      => 'GLL',
        'version'     => '1.0',
        'demo_url'    => 'http://www.thinkcmf.com',
        'author_url'  => 'http://www.thinkcmf.com'
    ];

    public $hasAdmin = 0;

    public function install()
    {
        Db::name('OrderPayment')->where('code',self::CMF_PAYPAL)->delete(true);
        Db::name('OrderPayment')->insertAll([
            [
                'code'        => self::CMF_PAYPAL,
                'name'        => 'paypal',
                'description' => 'paypal',
                'tips'        => 'paypal跳转',
            ]
        ]);
        return true;//安装成功返回true，失败false
    }
    // 插件卸载
    public function uninstall()
    {
        return true;//卸载成功返回true，失败false
    }

    public function createPayPal($params = []){
        // Autoload SDK package for composer based installations
        require 'vendor/autoload.php';

        $apiContext = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                'AekhtPol_seuQTZjAen07IcrBwc6lyaFP-y85JgSTAQ2Cm9tbgA7Pbwl2GYRo3SuJD0c3eCP5zjAjEPR',
                'EEPkUFQzkHqgidZpG5I98__C4qR9tZdeR2zMvK8ronLEMQX9BzfAaIhn16v91_d6jyCdrdK0exqVUTao'
            )
        );
        $shippingPrice = 2;
        $taxPrice = 0;
        $subTotal = 26;
        $item1 = new \PayPal\Api\Item();
        $item1->setName("产品2")->setCurrency("USD")->setQuantity(1)->setPrice(10);
        $item2 = new \PayPal\Api\Item();
        $item2->setName("产品1")->setCurrency("USD")->setQuantity(2)->setPrice(8);
        $itemList = new \PayPal\Api\ItemList();
        $itemList->setItems([$item1,$item2]);
        // Set payment details
        $details = new \PayPal\Api\Details();
        $details->setShipping($shippingPrice)->setTax($taxPrice)->setSubtotal($subTotal);
        // Set payment amount
        //注意，此处的subtotal，必须是产品数*产品价格，所有值必须是正确的，否则会报错
        $total = $shippingPrice + $subTotal + $taxPrice;
        $amount = new \PayPal\Api\Amount();
        $amount->setCurrency("USD")->setTotal($total)->setDetails($details);
        // Set transaction object
        $transaction = new \PayPal\Api\Transaction();
        $transaction->setAmount($amount)->setItemList($itemList)->setDescription("这是交易描述")
            ->setInvoiceNumber(uniqid());//setInvoiceNumber为支付唯一标识符,在使用时建议改成订单号
        $payer = new \PayPal\Api\Payer();
        $payer->setPaymentMethod('paypal');//["credit_card", "paypal"]
        $redirectUrls = new \PayPal\Api\RedirectUrls();
        $redirectUrl = "https://www.baidu.com/";//支付成功跳转的回调
        $cancelUrl = "https://www.baidu.com/";//取消支付的回调
        $redirectUrls->setReturnUrl($redirectUrl)->setCancelUrl($cancelUrl);
        // Create the full payment object
        $payment = new \PayPal\Api\Payment();
        $payment->setIntent("sale")->setPayer($payer)->setRedirectUrls($redirectUrls)->addTransaction($transaction);
        try {
//            $clientId = "AekhtPol_seuQTZjAen07IcrBwc6lyaFP-y85JgSTAQ2Cm9tbgA7Pbwl2GYRo3SuJD0c3eCP5zjAjEPR";//上面应用的clientId和secret
//            $secret = "EEPkUFQzkHqgidZpG5I98__C4qR9tZdeR2zMvK8ronLEMQX9BzfAaIhn16v91_d6jyCdrdK0exqVUTao";
//            $oAuth = new \PayPal\Auth\OAuthTokenCredential($clientId, $secret);
//            $apiContext =  new \PayPal\Rest\ApiContext($oAuth);
//            if(env('APP_DEBUG') === false ){
//                halt(2222);
//                halt($apiContext->setConfig(['mode' => 'sandbox']));//设置线上环境,默认是sandbox
//                halt(111);
//            }
            $payment->create($apiContext);
        } catch (\Exception $e) {
            return $e->getMessage();//错误提示
        }
        $approvalUrl = $payment->getApprovalLink();
        return ($approvalUrl);//这个是请求支付的链接，在浏览器中请求此链接就会跳转到支付页面
    }

}