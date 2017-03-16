/**
 * Created by 文涛 on 2017/3/13.
 */

$(function(){
    //登录选项卡切换
    $('#switch_userlogin').click(function(){

        $('#user_login_box').css('display','block');
        $('#wechat_login_box').css('display','none');
        $('#switch_bottom').animate({left:'-70px',width:'196px'});
    });

    $('#switch_wechatlogin').click(function(){

        $('#user_login_box').css('display','none');
        $('#wechat_login_box').css('display','block');
        $('#switch_bottom').animate({left:'126px',width:'196px'});
    }).click();

    //登录提交
    $("#login_form").submit(function(){
        if($('#username').val()==""){
            alert("请填写账号。");
            $('#username').focus();
            return false;
        }
        if($('#password').val()==""){
            alert("请填写密码。");
            $('#password').focus();
            return false;
        }

    })

    //注册获取验证
    var reg_yzm ={
        regphone:/^(13|14|15|16|17|18|19)[0-9]{9}$/,
        password:/^.{6,11}$/
    };
    $(".get_yzm").click(function(){
        var InterValObj; //timer变量，控制时间
        var count = 5; //间隔函数，1秒执行
        var curCount;//当前剩余秒数
        var reg_phone =$("#reg_phone").val();
        if(!reg_yzm.regphone.test(reg_phone)){
            alert("手机号格式不正确");
            return false;
        };
        //倒计时
        //设置button效果，开始计时
        curCount = count;
        $(".get_yzm").attr("disabled", "true");
        $(".get_yzm").css({"background": "#aaa"});//改变颜色
        $(".get_yzm").val( + curCount + "秒再获取");
        InterValObj = window.setInterval(SetRemainTime, 1000); //启动计时器，1秒执行一次

        //timer处理函数
        function SetRemainTime(){
            if (curCount == 1) {
                window.clearInterval(InterValObj);//停止计时器
                $(".get_yzm").removeAttr("disabled");//启用按钮
                $(".get_yzm").css({"background":'#fa4d32'});//改变颜色
                $(".get_yzm").val("重发验证码");
            }
            else {
                curCount--;
                $(".get_yzm").val( + curCount + "秒再获取");
            }
        };

    });


    //注册验证
    $("#reg_button").click(function(){
        var reg_pwd =$("#reg_pwd").val();
        if(!reg_yzm.password.test(reg_pwd)){
            alert("密码长度应在6~11位");
            return false;
        }
    })


});




















