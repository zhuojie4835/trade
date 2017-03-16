/**
 * Created by SXMAPS-BI-023 on 2017/3/7.
 */
$(function(){
    // 静态页面，加入公共头尾
    $('body').prepend('<div id="head_"></div>');
    $('#head_').load('../common/head.html');
    $('body').append('<div id="foot_"></div>');
    $('#foot_').load('../common/foot.html');


});