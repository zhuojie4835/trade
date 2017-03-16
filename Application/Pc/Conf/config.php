<?php

return array(
    /* 模板相关配置 */
    'TMPL_PARSE_STRING' => array(
        '__STATIC__' => __ROOT__ . '/Public/',
        '__IMG__'    => __ROOT__ . '/Public/Pc/images',
        '__CSS__'    => __ROOT__ . '/Public/Pc/css',
        '__JS__'     => __ROOT__ . '/Public/Pc/js',
    ),

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
        'notify_url' => "http://www.vw87china.com",
        //同步跳转
        'return_url' => "http://www.baidu.com",
        //编码格式
        'charset' => "UTF-8",
        //签名方式
        'sign_type'=>"RSA2",
        //支付宝网关
        'gatewayUrl' => "https://openapi.alipaydev.com/gateway.do",
        //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
        'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAyxY+EsKeA9i5LL66avZdye94gBlMib5CAX8iCYPfKey3WA2IN9QiXlTentLUx55y7zHI/qQomShK3otjl6ri+Sfo71X4Z0iKmatORhQoYOwPC2BBwctAMo2YEE/sSid1uYnkE+tVmzNlfQXHwQYn9EWbbiYUPmb765UIZk5ciFFFYbRM0OV4WPXH4FYgLR1fjFcxYOHa8tGzrSF2gV6TeFGoRuYosuXyJIXQAarwrDqY/kuQmFf8d0SpzPpmLR+rhaUms828ZSjI/hVQFGZ5bkTVl1m5Y7UXI3ZmWlEyWs2Dc7GE4qDxmgBZcVIGgt9kS+1vT1z3C24dvpl32Z4a4QIDAQAB",
    ),
);
