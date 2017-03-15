<?php

namespace Customer\Controller;
use Think\Controller;

class WxpayController extends Controller {
    
    public function _initialize()
    {
        //引入WxPayPubHelper
        vendor('WxPayPubHelper.WxPayPubHelper');
    }

    //生成二维码
    public function index()
    {
        //使用统一支付接口
        $unifiedOrder = new \UnifiedOrder_pub();
        //设置统一支付接口参数
        //设置必填参数
        $unifiedOrder->setParameter("body","图有利充值");//商品描述
        //自定义订单号，此处仅作举例
        $timeStamp = time();
        $out_trade_no = C('WxPay.pub.config.APPID')."$timeStamp";
        $unifiedOrder->setParameter("out_trade_no","$out_trade_no");//商户订单号 
        $unifiedOrder->setParameter("total_fee","1");//总金额
        $unifiedOrder->setParameter("notify_url", C('WXPAY.NOTIFY_URL'));//通知地址 
        $unifiedOrder->setParameter("trade_type",C('WXPAY.TRADE_TYPE'));//交易类型
       
        //获取统一支付接口结果
        $unifiedOrderResult = $unifiedOrder->getResult();
        // var_dump($unifiedOrder);die;
        //商户根据实际情况设置相应的处理流程
        if ($unifiedOrderResult["return_code"] == "FAIL") 
        {
            //商户自行增加处理流程
            echo "通信出错：".$unifiedOrderResult['return_msg']."<br>";
        } elseif ($unifiedOrderResult["result_code"] == "FAIL") {
            //商户自行增加处理流程
            echo "错误代码：".$unifiedOrderResult['err_code']."<br>";
            echo "错误代码描述：".$unifiedOrderResult['err_code_des']."<br>";
        } elseif($unifiedOrderResult["code_url"] != NULL) {
            //从统一支付接口获取到code_url
            $code_url = $unifiedOrderResult["code_url"];
            //商户自行增加处理流程
            //......
        }

        $this->assign('out_trade_no',$out_trade_no);
        $this->assign('code_url',$code_url);
        $this->assign('unifiedOrderResult',$unifiedOrderResult);
        
        // echo '<img src=".$."'
        $this->display();
    }

    public function notify()
    {
        //使用通用通知接口
        $notify = new \Notify_pub();
        //存储微信的回调
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $notify->saveData($xml);
         // var_dump($xml);
        //验证签名，并回应微信。
        //对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
        //微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
        //尽可能提高通知的成功率，但微信不保证通知最终能成功。
        if($notify->checkSign() == FALSE){
            $notify->setReturnParameter("return_code","FAIL");//返回状态码
            $notify->setReturnParameter("return_msg","签名失败");//返回信息
        }else{
            $notify->setReturnParameter("return_code","SUCCESS");//设置返回码
        }
        $returnXml = $notify->returnXml();
        echo $returnXml;
         
        //==商户根据实际情况设置相应的处理流程，此处仅作举例=======
         
        //以log文件形式记录回调信息
        // $log_ = new Log_();
        $log_name =  __ROOT__."/Public/notify_url.log";//log文件路径
         
        $this->log_result($log_name,"【接收到的notify通知】:\n".$xml."\n");
         
        if($notify->checkSign() == TRUE)
        {
            if ($notify->data["return_code"] == "FAIL") {
                //此处应该更新一下订单状态，商户自行增删操作
                log_result($log_name,"【通信出错】:\n".$xml."\n");
                $this->error("1");
            } elseif ($notify->data["result_code"] == "FAIL"){
                //此处应该更新一下订单状态，商户自行增删操作
                log_result($log_name,"【业务出错】:\n".$xml."\n");
                $this->error("失败2");
            } else {
                //此处应该更新一下订单状态，商户自行增删操作
                log_result($log_name,"【支付成功】:\n".$xml."\n");
                $this->success("支付成功！");
            }
             
            //商户自行增加处理流程,
            //例如：更新订单状态
            //例如：数据库操作
            //例如：推送支付完成信息
        }
    }

    //查询订单
    public function orderQuery()
    {  
        // out_trade_no='+$('out_trade_no').value,
        //退款的订单号
        if (!isset($_POST["out_trade_no"])) {
            $out_trade_no = " ";
        } else {
            $out_trade_no = $_POST["out_trade_no"];
            //使用订单查询接口
            $orderQuery = new \OrderQuery_pub();
            //设置必填参数
            //appid已填,商户无需重复填写
            //mch_id已填,商户无需重复填写
            //noncestr已填,商户无需重复填写
            //sign已填,商户无需重复填写
            $orderQuery->setParameter("out_trade_no","$out_trade_no");//商户订单号 
            //非必填参数，商户可根据实际情况选填
            //$orderQuery->setParameter("sub_mch_id","XXXX");//子商户号  
            //$orderQuery->setParameter("transaction_id","XXXX");//微信订单号
            
            //获取订单查询结果
            $orderQueryResult = $orderQuery->getResult();
            
            //商户根据实际情况设置相应的处理流程,此处仅作举例
            if ($orderQueryResult["return_code"] == "FAIL") {
                $this->error($out_trade_no);
            } elseif ($orderQueryResult["result_code"] == "FAIL"){
                // $this->ajaxReturn('','支付失败！',0);
                $this->error($out_trade_no);
            } else {
                $i=$_SESSION['i'];
                $i--;
                $_SESSION['i'] = $i;
                //判断交易状态
                switch ($orderQueryResult["trade_state"]) {
                    case SUCCESS: 
                        $this->success("支付成功！");
                        break;
                    case REFUND:
                        $this->error("超时关闭订单：".$i);
                        break;
                    case NOTPAY:
                        $this->error("超时关闭订单：".$i);
                        // $this->ajaxReturn($orderQueryResult["trade_state"], "支付成功", 1);
                        break;
                    case CLOSED:
                        $this->error("超时关闭订单：".$i);
                        break;
                    case PAYERROR:
                        $this->error("支付失败".$orderQueryResult["trade_state"]);
                        break;
                    default:
                        $this->error("未知失败".$orderQueryResult["trade_state"]);
                       break;
                }
            }   
        }
    }
}

?>