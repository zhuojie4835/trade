<?php
/**
 * 前台配置文件
 * 所有除开系统级别的前台配置
 */
return array(
		
    // 预先加载的标签库
    'TAGLIB_PRE_LOAD'     =>    'OT\\TagLib\\Article,OT\\TagLib\\Think',
        
    /* 主题设置 */
    // 'DEFAULT_THEME' =>  'default',  // 默认模板主题名称

    /* 数据缓存设置 */
    'DATA_CACHE_PREFIX' => 'onethink_', // 缓存前缀
    'DATA_CACHE_TYPE'   => 'File', // 数据缓存类型

    /* 文件上传相关配置 */
    'DOWNLOAD_UPLOAD' => array(
        'mimes'    => '', //允许上传的文件MiMe类型
        'maxSize'  => 5*1024*1024, //上传的文件大小限制 (0-不做限制)
        'exts'     => 'jpg,gif,png,jpeg,zip,rar,tar,gz,7z,doc,docx,txt,xml', //允许上传的文件后缀
        'autoSub'  => true, //自动子目录保存文件
        'subName'  => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => './Uploads/Download/', //保存根路径
        'savePath' => '', //保存路径
        'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt'  => '', //文件保存后缀，空则使用原后缀
        'replace'  => false, //存在同名是否覆盖
        'hash'     => true, //是否生成hash编码
        'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
    ), //下载模型上传配置（文件上传类配置）

    /* 编辑器图片上传相关配置 */
    'EDITOR_UPLOAD' => array(
        'mimes'    => '', //允许上传的文件MiMe类型
        'maxSize'  => 2*1024*1024, //上传的文件大小限制 (0-不做限制)
        'exts'     => 'jpg,gif,png,jpeg', //允许上传的文件后缀
        'autoSub'  => true, //自动子目录保存文件
        'subName'  => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => './Uploads/Editor/', //保存根路径
        'savePath' => '', //保存路径
        'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt'  => '', //文件保存后缀，空则使用原后缀
        'replace'  => false, //存在同名是否覆盖
        'hash'     => true, //是否生成hash编码
        'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
    ),

    /* 模板相关配置 */
    'TMPL_PARSE_STRING' => array(
        '__STATIC__' => __ROOT__ . '/Public/static',
        '__ADDONS__' => __ROOT__ . '/Public/Addons',
        '__IMG__'    => __ROOT__ . '/Public/images',
        '__CSS__'    => __ROOT__ . '/Public/css',
        '__JS__'     => __ROOT__ . '/Public/js',
    ),

    /* SESSION 和 COOKIE 配置 */
	'SESSION_PREFIX' => 'trade_customer_',        //session前缀
    'COOKIE_PREFIX'  => 'trade_customer_', // Cookie前缀 避免冲突

    /**
     * 附件相关配置
     * 附件是规划在插件中的，所以附件的配置暂时写到这里
     * 后期会移动到数据库进行管理
     */
    'ATTACHMENT_DEFAULT' => array(
        'is_upload'     => true,
        'allow_type'    => '0,1,2', //允许的附件类型 (0-目录，1-外链，2-文件)
        'driver'        => 'Local', //上传驱动
        'driver_config' => null, //驱动配置
    ), //附件默认配置

    'ATTACHMENT_UPLOAD' => array(
        'mimes'    => '', //允许上传的文件MiMe类型
        'maxSize'  => 5*1024*1024, //上传的文件大小限制 (0-不做限制)
        'exts'     => 'jpg,gif,png,jpeg,zip,rar,tar,gz,7z,doc,docx,txt,xml', //允许上传的文件后缀
        'autoSub'  => true, //自动子目录保存文件
        'subName'  => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => './Uploads/Attachment/', //保存根路径
        'savePath' => '', //保存路径
        'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt'  => '', //文件保存后缀，空则使用原后缀
        'replace'  => false, //存在同名是否覆盖
        'hash'     => true, //是否生成hash编码
        'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
    ), //附件上传配置（文件上传类配置）
	'PWD_SALT' => '68739cae48904d91496b40a3f808e6c5',//密码加盐
    'LOGIN_TIMEOUT'=>1800,//登录有效时间

    'WXPAY' => array(
        'APPID'=>'wx0673e06ca2aa08e9',
        'MCHID'=>'1250389101',
        'KEY' => 'tuyoulituyoulituyoulituyouli6688',
        'APPSECRET' => '68a9ebfb60ce563d1810cb44d7dbb7c6',
        'COMPANY' => '图有利',
        'NOTIFY_URL' => 'http://121.43.116.138/weipay/index.php/home/index/notify',
        'TRADE_TYPE' => 'NATIVE',
        'CURL_TIMEOUT'=>30,
        'SSLCERT_PATH'=>'',
        'SSLKEY_PATH'=>''
    ),

    'ALIPAY' => array(
        'app_id' => "2016080400164364",
        //商户私钥，您的原始格式RSA私钥
        'merchant_private_key' => "MIIEowIBAAKCAQEAyxY+EsKeA9i5LL66avZdye94gBlMib5CAX8iCYPfKey3WA2IN9QiXlTentLUx55y7zHI/qQomShK3otjl6ri+Sfo71X4Z0iKmatORhQoYOwPC2BBwctAMo2YEE/sSid1uYnkE+tVmzNlfQXHwQYn9EWbbiYUPmb765UIZk5ciFFFYbRM0OV4WPXH4FYgLR1fjFcxYOHa8tGzrSF2gV6TeFGoRuYosuXyJIXQAarwrDqY/kuQmFf8d0SpzPpmLR+rhaUms828ZSjI/hVQFGZ5bkTVl1m5Y7UXI3ZmWlEyWs2Dc7GE4qDxmgBZcVIGgt9kS+1vT1z3C24dvpl32Z4a4QIDAQABAoIBAQCAuYR216zYu1IELpByo94m1QcICwEcfd/Qmwi0B0Y4iLZdtZYV7Pwr1peVDAWa0bAANQo1fU/OZF+wV6G0zLg5PTbEHTXqIWzYomBmwvglFvsiNsz3TSFP6bfs/vvCtOhFxkUu6wfD6/v0FunaS1Cf4E74rmI+e4BKfphoYFr+e4MZDPxd5Idm+RSEo5anX9J/dj8F1DB4EeaBCHScrUgcf6dt6+UULkPX6M3eHoRIMxehSYkkfQEebROqm8S2BPrXiFA8alqGFiWCBhF64fHteoBhCmDVx0bi3TBp3YsdkKTMsrkLS5Rr6ETnUMUONdyxTl7Y1CzE8hz4PXtnBzQBAoGBAPZK5fi+qnAjSuNa4o92lKbZ96mNthwG+GEkm30XaVUTKxnLqcg53jcolRThVk9gXdrkoUXWz4FFfgMftCZkpttHXd7ZWrMVVlzdQkFO9URTRkBXRZpFnoZAverxZxGG0tDiukZqr12j9xp9p4BhZCgzfeGvybW3y5YNuDEMevJRAoGBANMXZabbIVZ6vIu+H48qmZ7XpAt8Lr0xsfBhPHR9Mt3AobaN2fn5RLd4ZiHnFrd9drzwd19zRx8mvlSwlMQ/CFNusrX9PZM02YY7hiZbOZC2PlGitvQ/oyu6ySA7WPo67E+7fdogdCGPKdqmU2e+EiFBde/WbfkeAFb3V4eBw2uRAoGALFZzbAI3AJT56E/2NUltnVPj59whCo6erC5A55YNWklnGu+1EtyICnn9zJJ8TTHV84/xEHeJR4ZRFxLgBYFdIGCKn5GVaGPQ9krGoKcsNC6hmPedCha6YQTKq77lhRw2W3BVhFO0WjEoTNaODuh5dVs/sB9LOrFprwutOvc6MuECgYBvPfBC6035TfsYxZvKDdAhJlD8AHh9GbhFDmkWlnf85qwSEVUi59rvuRJRoX+WORce4LZ35b9ASmGZLeloNjOEDF0/jPIrJnnPxISgqAiBG5lh2hcCRpsNy4R8aOouDGlGZ28eSWYJ3XFYGbjwffj974pgVydjVg0lx3koHxlGEQKBgDqCevaz8XJxpZt7QyYX7LJ6+OxfB8WQyZVQkrWl2pfq/EKdyaI2cWfopBXP2dEI06jZXmlLYWe6berYbTNU+bRkjBFKLx2ljaXydsk4xfhIwOpsh0xhBHYqynnfN51LT4C9Nqi8Lj604JfDxyqdEVsRBFgDF6jDpGrED2VfVTSR",
        //异步通知地址
        'notify_url' => "http://www.vw87china.com/wxpay/index.php",
        //同步跳转
        'return_url' => "http://www.vw87china.com/wxpay/return.php",
        //编码格式
        'charset' => "UTF-8",
        //签名方式
        'sign_type'=>"RSA2",
        //支付宝网关
        'gatewayUrl' => "https://openapi.alipaydev.com/gateway.do",
        //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
        'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAyxY+EsKeA9i5LL66avZdye94gBlMib5CAX8iCYPfKey3WA2IN9QiXlTentLUx55y7zHI/qQomShK3otjl6ri+Sfo71X4Z0iKmatORhQoYOwPC2BBwctAMo2YEE/sSid1uYnkE+tVmzNlfQXHwQYn9EWbbiYUPmb765UIZk5ciFFFYbRM0OV4WPXH4FYgLR1fjFcxYOHa8tGzrSF2gV6TeFGoRuYosuXyJIXQAarwrDqY/kuQmFf8d0SpzPpmLR+rhaUms828ZSjI/hVQFGZ5bkTVl1m5Y7UXI3ZmWlEyWs2Dc7GE4qDxmgBZcVIGgt9kS+1vT1z3C24dvpl32Z4a4QIDAQAB",
    ),
    // 支付宝pc网页配置
    'ALIPAYPC' => array(
        'partner' => "2088511871416352",
        'seller_id' => "2088511871416352",
        'key' => "l9uqru2ykvoa7n0czog2fun21fan1wa1",
        'notify_url' => "http://www.baidu.com",
        'return_url' => "http://www.baidu.com",
        'sign_type'=>strtoupper('MD5'),
        'input_charset' => strtolower('utf-8'),
        // 'cacert' => VENDOR_PATH.'pcpay_md5\\cacert.pem',
        'transport' => "http",
        'payment_type' => "1",
        'service' => "create_direct_pay_by_user",
        'anti_phishing_key' => "",
        'exter_invoke_ip' => ""
    ),
);
