/**
 * Created by SXMAPS-BI-035 on 2017-03-14.
 */
$(function(){
    //回到顶部
    $("#gotop").click(function () {
        var speed=200;
        $('body,html').animate({ scrollTop: 0 }, speed);
        return false;
    });
});