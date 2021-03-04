<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/3/3
 * Time: 10:34
 */

namespace plugins\pay_pal\controller;

use app\order\service\ApiService;
use cmf\controller\PluginRestBaseController;
use think\facade\Log;
use api\mall\service\OrderService;

class PayPalController extends PluginRestBaseController
{

    protected $config = [];

    public function initialize()
    {
        $this->config = \plugins\pay_pal\lib\Config::get();
    }

    public function index()
    {

    }

    public function return()
    {

    }

    public function notify()
    {

        //获取回调结果
        $json_data = $this->get_JsonData();

        if (!empty($json_data)) {
            Log::debug("paypal notify info:\r\n" . json_encode($json_data));
        } else {
            Log::debug("paypal notify fail:参加为空");
        }
        //自己打印$json_data的值看有那些是你业务上用到的
        //比如我用到
        $data['invoice'] = $json_data['resource']['invoice_number'];
        $data['txn_id'] = $json_data['resource']['id'];
        $data['total'] = $json_data['resource']['amount']['total'];
        $data['status'] = isset($json_data['status']) ? $json_data['status'] : '';
        $data['state'] = $json_data['resource']['state'];

        try {
            //处理相关业务
        } catch (\Exception $e) {
            //记录错误日志
            Log::error("paypal notify fail:" . $e->getMessage());

            return "fail";
        }
        return "success";
    }

    public function get_JsonData()
    {
        $json = file_get_contents('php://input');
        if ($json) {
            $json = str_replace("'", '', $json);
            $json = json_decode($json, true);
        }
        return $json;
    }

    public function payRedirect(Request $request){
        $paymentID = 'PAYID-MBAJNRY46603827KP778481J'; $request->get('paymentId');
        $payerId = '45B7LBPT9WVNG'; $request->get('PayerID');

        $apiContext = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                $this->config['paypal']['pay_pal_client_id'],
                $this->config['paypal']['pay_pal_secret']
            )
        );
        $payment = \PayPal\Api\Payment::get($paymentID, $apiContext);
        $execute = new \PayPal\Api\PaymentExecution();
        $execute->setPayerId($payerId);
        try{
            $payment = $payment->execute($execute, $apiContext);//执行,从paypal获取支付结果
            Log::info($payment);
            $paymentState = $payment->getState();//Possible values: created, approved, failed.
            $invoiceNum = $payment->getTransactions()[0]->getInvoiceNumber();
            $payNum = $payment->getTransactions()[0]->getRelatedResources()[0]->getSale()->getId();//这是支付的流水单号，必须保存，在退款时会使用到
            $total = $payment->getTransactions()[0]->getRelatedResources()[0]->getSale()->getAmount()->getTotal();//支付总金额
            $transactionState = $payment->getTransactions()[0]->getRelatedResources()[0]->getSale()->getState();//Possible values: completed, partially_refunded, pending, refunded, denied.
            if($paymentState == 'approved' && $transactionState == 'completed'){
                //处理成功的逻辑，例如：判断支付金额与订单金额，更新订单状态等
                return "success";//返回成功标识
            }else{
                //paypal回调错误,paypal状态不正确
                return "error";//返回错误标识
            }
        }catch(\Exception $e){
            return($e->getMessage());
        }
    }
}