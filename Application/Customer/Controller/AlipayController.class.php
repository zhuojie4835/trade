<?php 
namespace Customer\Controller;
use Think\Controller;

class AlipayController extends Controller {
	/*
	手机网页支付
	 */
	public function wappay() {
		if(IS_POST) {
			vendor('AliPay.wappay.service.AlipayTradeService');
			vendor('AliPay.wappay.buildermodel.AlipayTradeWapPayContentBuilder');
			
			$out_trade_no = $_POST['WIDout_trade_no'];
		    //订单名称，必填
		    $subject = $_POST['WIDsubject'];
		    //付款金额，必填
		    $total_amount = $_POST['WIDtotal_amount'];
		    //商品描述，可空
		    $body = $_POST['WIDbody'];
		    //超时时间
		    $timeout_express="10m";
		    $payRequestBuilder = new \AlipayTradeWapPayContentBuilder();
		    $payRequestBuilder->setBody($body);
		    $payRequestBuilder->setSubject($subject);
		    $payRequestBuilder->setOutTradeNo($out_trade_no);
		    $payRequestBuilder->setTotalAmount($total_amount);
		    $payRequestBuilder->setTimeExpress($timeout_express);

		    $config = C('ALIPAY');
		    $payResponse = new \AlipayTradeService($config);
		    $result=$payResponse->wapPay($payRequestBuilder,$config['return_url'],$config['notify_url']);
		}

		$this->display('index');
	}

	/*
	统一收单线下交易查询
	 */
	public function tradequery() {
		vendor('AliPay.wappay.service.AlipayTradeService');
		vendor('AliPay.wappay.buildermodel.AlipayTradeQueryContentBuilder');
		
		$out_trade_no = I('out_trade_no','');
		$trade_no = I('trade_no','');
	    // $out_trade_no = '2017316181520479';
	    $payQueryBuilder = new \AlipayTradeQueryContentBuilder();
	    $payQueryBuilder->setOutTradeNo($out_trade_no);
	    $payQueryBuilder->setTradeNo($trade_no);

	    $config = C('ALIPAY');
	    $response = new \AlipayTradeService($config);
	    $result = $response->Query($payQueryBuilder,$config['return_url'],$config['notify_url']);
	    if($result->code == 10000) {

	    } else {

	    }
	    var_dump($result);
	}

	/*
	统一收单交易关闭接口
	 */
	public function tradeclose() {
		vendor('AliPay.wappay.service.AlipayTradeService');
		vendor('AliPay.wappay.buildermodel.AlipayTradeCloseContentBuilder');
		
		$out_trade_no = I('out_trade_no','');
		$trade_no = I('trade_no','');
	    // $out_trade_no = '2017316181520479';
	    $tradeCloseBuilder = new \AlipayTradeCloseContentBuilder();
	    $tradeCloseBuilder->setOutTradeNo($out_trade_no);
	    $tradeCloseBuilder->setTradeNo($trade_no);

	    $config = C('ALIPAY');
	    $response = new \AlipayTradeService($config);
	    $result = $response->Close($tradeCloseBuilder,$config['return_url'],$config['notify_url']);
	    if($result->code == 10000) {

	    } else {

	    }
	    var_dump($result);
	}

	/*
	统一收单交易退款接口
	 */
	public function traderefund() {
		vendor('AliPay.wappay.service.AlipayTradeService');
		vendor('AliPay.wappay.buildermodel.AlipayTradeRefundContentBuilder');
		
		$out_trade_no = I('out_trade_no','');
		$trade_no = I('trade_no','');
	    // $out_trade_no = '2017316181520479';
	    $tradeRefundBuilder = new \AlipayTradeRefundContentBuilder();
	    $tradeRefundBuilder->setOutTradeNo($out_trade_no);
	    $tradeRefundBuilder->setTradeNo($trade_no);
	    $tradeRefundBuilder->setRefundAmount('0.01');

	    $config = C('ALIPAY');
	    $response = new \AlipayTradeService($config);
	    $result = $response->Refund($tradeRefundBuilder,$config['return_url'],$config['notify_url']);
	    if($result->code == 10000) {

	    } else {

	    }
	    var_dump($result);
	}

	/*
	统一收单交易退款查询
	 */
	public function refundquery() {
		vendor('AliPay.wappay.service.AlipayTradeService');
		vendor('AliPay.wappay.buildermodel.AlipayTradeFastpayRefundQueryContentBuilder');
		
		$out_trade_no = I('out_trade_no','');
		$trade_no = I('trade_no','');
	    // $out_trade_no = '2017316181520479';
	    $refundqueryBuilder = new \AlipayTradeFastpayRefundQueryContentBuilder();
	    $refundqueryBuilder->setOutTradeNo($out_trade_no);
	    $refundqueryBuilder->setTradeNo($trade_no);
	    $refundqueryBuilder->setOutRequestNo($out_trade_no);

	    $config = C('ALIPAY');
	    $response = new \AlipayTradeService($config);
	    $result = $response->refundQuery($refundqueryBuilder,$config['return_url'],$config['notify_url']);
	    if($result->code == 10000) {

	    } else {

	    }
	    var_dump($result);
	}

	/*
	查询对账单下载地址
	 */
	public function billdownloadurlquery() {
		vendor('AliPay.wappay.service.AlipayTradeService');
		vendor('AliPay.wappay.buildermodel.AlipayDataDataserviceBillDownloadurlQueryContentBuilder');
		
		$bill_type = I('out_trade_no','');
		$bill_date = I('trade_no','');
	    // $out_trade_no = '2017316181520479';
	    $billdownloadurlqueryBuilder = new \AlipayDataDataserviceBillDownloadurlQueryContentBuilder();
	    $billdownloadurlqueryBuilder->setBillType('signcustomer');
	    $billdownloadurlqueryBuilder->setBillDate('2017-03-17');

	    $config = C('ALIPAY');
	    $response = new \AlipayTradeService($config);
	    $result = $response->downloadurlQuery($billdownloadurlqueryBuilder,$config['return_url'],$config['notify_url']);
	    if($result->code == 10000) {

	    } else {

	    }
	    var_dump($result);
	}

	/*
	pc网页支付
	 */
	public function pcpay() {
		if(IS_POST) {
			vendor("AliPay.pcpay_md5.lib.alipay_submit");
			/**************************请求参数**************************/
	        //商户订单号，商户网站订单系统中唯一订单号，必填
	        $out_trade_no = $_POST['WIDout_trade_no'];
	        //订单名称，必填
	        $subject = $_POST['WIDsubject'];
	        //付款金额，必填
	        $total_fee = $_POST['WIDtotal_fee'];
	        //商品描述，可空
	        $body = $_POST['WIDbody'];

	        $alipay_config = C('ALIPAYPC');
			/************************************************************/
			//构造要请求的参数数组，无需改动
			$parameter = array(
				"service"       => $alipay_config['service'],
				"partner"       => $alipay_config['partner'],
				"seller_id"  => $alipay_config['seller_id'],
				"payment_type"	=> $alipay_config['payment_type'],
				"notify_url"	=> $alipay_config['notify_url'],
				"return_url"	=> $alipay_config['return_url'],
				"anti_phishing_key"=>$alipay_config['anti_phishing_key'],
				"exter_invoke_ip"=>$alipay_config['exter_invoke_ip'],
				"out_trade_no"	=> $out_trade_no,
				"subject"	=> $subject,
				"total_fee"	=> $total_fee,
				"body"	=> $body,
				"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
				//其他业务参数根据在线开发文档，添加参数.文档地址:https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.kiX33I&treeId=62&articleId=103740&docType=1
		        //如"参数名"=>"参数值"	
			);

			//建立请求
			$alipaySubmit = new \AlipaySubmit($alipay_config);
			$html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
			echo $html_text;
		}

		$this->display('pc');
	}

	/*
	支付异步通知
	 */
	public function notify() {
		vendor("AliPay.pcpay_md5.lib.alipay_notify");

		$alipay_config = C('ALIPAYPC');
		//计算得出通知验证结果
		$alipayNotify = new \AlipayNotify($alipay_config);
		$verify_result = $alipayNotify->verifyNotify();

		if($verify_result) {//验证成功

			$out_trade_no = $_POST['out_trade_no'];//商户订单号
			$trade_no = $_POST['trade_no'];//支付宝交易号
			$trade_status = $_POST['trade_status'];//交易状态

			if($out_trade_no) {//查询订单
				#code...
			}
		    if($_POST['trade_status'] == 'TRADE_FINISHED') {

		        logResult(json_encode($_POST));
		    } elseif ($_POST['trade_status'] == 'TRADE_SUCCESS') {

		        logResult(json_encode($_POST));
		    }
		        
			echo "success";
		} else {//验证失败
		    
		    echo "fail";
		    logResult(json_encode($_POST));
		}
	}
}