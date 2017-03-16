/**
 * Created by juhezaixianqianduanTeam on 2017-02-28.
 */


var JUI = {
    getJsonValue :  function (obj, name){
        var result = null;
        var value  = null;
        for(var key in obj){
            value = obj[key];
            if(key == name){
                return value;
            } else {
                if( typeof value == "object" ){
                    result = this.getJsonValue(value,name);
                };
            };
        };
        return result;
    },
    config : {
        debug : true
    },
    ajaxCode : {
        C200 : '处理成功',
        C500 : '处理失败',
        C501 : '未登录'
    },
    ajax_ : function(arg){
        var c = {
            method : arg.method || 'post',
            data : arg.data || '',
            url : arg.url || "",
            async : arg.async || true,
            dataType : arg.dataType || 'json',
            complete : function(su){
                log(su,this.data,this.url);
                arg.success && arg.success.apply(this, arguments);
            }
        };
        $.ajax({
            type: c.method, // you request will be a post request
            //crossDomain : true,
            xhrFields : { withCredentials : true },
            data: c.data, // javascript object with all my params
            url: c.url,
            dataType: c.dataType, // datatype can be json or jsonp
            success: c.complete
        });
    },
    alert_ : function(text,func,e) {
        // e参数为object类型，以后新增参数往e增加属性
        var btn = e ? e.btn : '<a class="info-close-btn info-modal-close" href="javascript:void(0);">确&nbsp;定</a>';

        if ($('.info-modal').size() > 0) {
            $('.info-modal').show();
        } else {
            var html = '<div class="info-modal" style="display: block;"><div class="info-icon"></div><i class="icon icon-close-1 info-modal-close"></i><div class="info-text"></div>'+btn+'</div>';
            $('body').prepend(html);
            $('.ainfo-close-btn').click(function(){
                $('.info-modal').hide();
                if(func){
                    func();
                };
            });
            $('.info-modal-close').click(function(){
                $('.info-modal').hide();
                if(func){
                    func();
                }
            });
        };
        $('.info-text').html(text);

    },
    tips : function(text, time, width_, showClass_){
        var class_ = showClass_ || 'tipsContr';
        var width = width_ || 220;
        if ($('.tips').size() > 0) {
            $('.tips').addClass(class_);
        } else {
            var html = '<div class="tips '+class_+'"><p class="tips-txt"></p></div>';
            $('body').prepend(html);
            console.log(html);
        };
        $('.tips').width(width);
        $('.tips-txt').text(text);
        var tipsTimer = setTimeout(function(){
            $('.tips').removeClass(class_);
        }, time*1000);
        clearTimeout('tipsTimer');
    },
    cookie : {
        set : function(objName, objValue, objHours){
            var str = objName + "=" + escape(objValue);
            if (objHours > 0) {//为0时不设定过期时间，浏览器关闭时cookie自动消失
                var date = new Date();
                var ms = objHours * 3600 * 1000;
                date.setTime(date.getTime() + ms);
                str += "; expires=" + date.toGMTString() + "; path=/";
            }
            document.cookie = str;
        },
        get : function(objName){
            var arrStr = document.cookie.split("; ");
            for (var i = 0; i < arrStr.length; i++) {
                var temp = arrStr[i].split("=");
                if (temp[0] == objName)
                    return unescape(temp[1]);
            }
        },
        // cookie 删除失效
        del : function(name){
            var date = new Date();
            date.setTime(date.getTime() - 10000);
            document.cookie = name + "=a; expires=" + date.toGMTString() + "; path=/";
        },
        getItem :function(name,item){
            var json = eval("(" + this.get(name) + ")");
            return json[item];
        }
    },
    supportCss3 : function (style) {
        var prefix = ['webkit', 'Moz', 'ms', 'o'],
            i,
            humpString = [],
            htmlStyle = document.documentElement.style,
            _toHumb = function (string) {
                return string.replace(/-(\w)/g, function ($0, $1) {
                    return $1.toUpperCase();
                });
            };

        for (i in prefix)
            humpString.push(_toHumb(prefix[i] + '-' + style));

        humpString.push(_toHumb(style));

        for (i in humpString)
            if (humpString[i] in htmlStyle) {
                // console.error(humpString[i]);
                return true;
            }

        return false;
    },
    eleContain : function(tar, num){
        var target = $(tar);
        function eleC() {

            target.each(function (e) {
                var a = $(window).scrollTop() + $(window).height(),
                    b = target.eq(e).offset().top + num;
                if (a >= b) {
                    $(this).addClass('active');
                };
            });
        };
        window.onload = window.onscroll = eleC;
    },
    showDialog : function(target){
        this.hideDialog();
        $(target).add('.dialog').show();
    },
    hideDialog : function(){
        $('div[class^="dialog"]').hide();
    },
    clickPro : function(triger, target, func){
        var tri = triger,
            tar = $(target);

        $('body').delegate(tri,'click',function(e){
            e.stopPropagation();
            if($(e.target).hasClass('disable'))return false;

            var idx = $(e.target).index(tri);
            console.log(idx);
            if(tar.eq(idx).is(':hidden')){
                tar.hide();
                tar.eq(idx).show();
            }else{
                tar.hide();
                if(typeof func == 'object')func();
            };
        });

        $('body').click(function(){
            $(target).hide();
        });
    },
    getQueryString : function(name){
        var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
        var r = window.location.search.substr(1).match(reg);
        if(r!=null) return  unescape(r[2]); return null;
    },
    locationHtml : function(){
        return location.href.replace(location.search,"");
    },
    date: function(fmt_,d){
        var t = (typeof d == 'number' ? new Date(d) :  d) || new Date(),
            fmt = fmt_ || "yyyy-MM-dd hh:mm:ss";
        var o = {
            "M+": t.getMonth() + 1, //月份
            "d+": t.getDate(), //日
            "h+": t.getHours(), //小时
            "m+": t.getMinutes(), //分
            "s+": t.getSeconds(), //秒
            "q+": Math.floor((t.getMonth() + 3) / 3), //季度
            "S": t.getMilliseconds() //毫秒
        };
        if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (t.getFullYear() + "").substr(4 - RegExp.$1.length));
        for (var k in o)
            if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
        return fmt;
    },
    toggle : function(name){
        var num = name || 'loading';
        var obj = {
            loading: '<div class="loading"><img src="img/loading.gif" /></div>'
        };
        var tar = $('.'+num);
        if(!tar.size()){
            $('body').prepend(obj[num]);
        };
        if(tar.is(':hidden')){
            tar.show();
        }else{
            tar.hide();
        };
    },
    ding: function(target,class_,func){
        var tar = $(target);
        if(tar.size()>0){
            var top = tar.offset().top;
            var c = class_ || 'fixedNav';
            window.onscroll = function(){
                if($('body').scrollTop()>=top){
                    $('body').addClass(c);
                    func();
                }else{
                    $('body').removeClass(c);
                };
            };
        };
    },
    addEvent : function(el, type, fn, capture){
        // 修正
        var f = (function(){
            var _eventCompat = function(event) {
                var type = event.type;
                if (type == 'DOMMouseScroll' || type == 'mousewheel') {
                    event.delta = (event.wheelDelta) ? event.wheelDelta / 120 : -(event.detail || 0) / 3;
                }
                //alert(event.delta);
                if (event.srcElement && !event.target) {
                    event.target = event.srcElement;
                }
                if (!event.preventDefault && event.returnValue !== undefined) {
                    event.preventDefault = function() {
                        event.returnValue = false;
                    };
                }
                /*
                 ......其他一些兼容性处理 */
                return event;
            };
            if (window.addEventListener) {
                return function(el, type, fn, capture) {
                    if (type === "mousewheel" && document.mozHidden !== undefined) {
                        type = "DOMMouseScroll";
                    }
                    el.addEventListener(type, function(event) {
                        fn.call(this, _eventCompat(event));
                    }, capture || false);
                }
            } else if (window.attachEvent) {
                return function(el, type, fn, capture) {
                    el.attachEvent("on" + type, function(event) {
                        event = event || window.event;
                        fn.call(el, _eventCompat(event));
                    });
                }
            }
            return function() {};
        })();
        console.log(f);
    },
    browser : function(){
        var browser = {
            versions:function(){
                var u = navigator.userAgent, app = navigator.appVersion;
                return {//移动终端浏览器版本信息
                    trident: u.indexOf('Trident') > -1, //IE内核
                    presto: u.indexOf('Presto') > -1, //opera内核
                    webKit: u.indexOf('AppleWebKit') > -1, //苹果、谷歌内核
                    gecko: u.indexOf('Gecko') > -1 && u.indexOf('KHTML') == -1, //火狐内核
                    mobile: !!u.match(/AppleWebKit.*Mobile.*/), //是否为移动终端
                    ios: !!u.match(/(i[^;]+;( U;)? CPU.+Mac OS X)/), //ios终端
                    android: u.indexOf('Android') > -1 || u.indexOf('Linux') > -1, //android终端或者uc浏览器
                    iPhone: u.indexOf('iPhone') > -1 , //是否为iPhone或者QQHD浏览器
                    iPad: u.indexOf('iPad') > -1, //是否iPad
                    webApp: u.indexOf('Safari') == -1 //是否web应该程序，没有头部与底部
                };
            }(),
            language:(navigator.browserLanguage || navigator.language).toLowerCase()
        };

        return browser.versions;

    },
    change : function(){
        window.onresize = change;
        // 最小高
        $('.main').css('min-height',$(window).height()-276);
    },
    log : function () {
        var that = this;
        window.log = function () {
            if(!that.config.debug) return false;
            console.log.apply(console, arguments);
        };
    },
    init : function(){
        var that = this;
        that.log();
        log('%c欢迎使用JUI.js', 'padding:2px 6px;background-color:#3196cb; border-raidus:5px; color:#fff;')



    }
};

$(function(){
    JUI.init();

});


