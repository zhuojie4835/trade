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
		    $timeout_express="1m";
		    $payRequestBuilder = new \AlipayTradeWapPayContentBuilder();
		    $payRequestBuilder->setBody($body);
		    $payRequestBuilder->setSubject($subject);
		    $payRequestBuilder->setOutTradeNo($out_trade_no);
		    $payRequestBuilder->setTotalAmount($total_amount);
		    $payRequestBuilder->setTimeExpress($timeout_express);

		    $config = C('ALIPAY');
		    $payResponse = new \AlipayTradeService($config);
		    $result=$payResponse->wapPay($payRequestBuilder,$config['return_url'],$config['notify_url']);

		    return ;
			var_dump(C('ALIPAY'));
		}

		$this->display('index');
	}

	/*
	统一收单线下交易查询
	 */
	public function wapquery() {
		
	}
	/*
	pc网页支付
	 */
	public function pcpay() {
		if(IS_POST) {
			vendor("AliPay.pcpay_md5.alipay_config");
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
}