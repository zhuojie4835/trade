/**
 * Created by SXMAPS-BI-035 on 2017-03-14.
 */


$(function(){
    $(".lc-popItemcon").scroll(function() {
        var scrolltop_=$(this).scrollTop();
        var btn_=$(this).find(".btn");
        if(scrolltop_ >10){
            btn_.addClass("active");
            btn_.attr("disabled",false);
        }
        /*         else{
         btn_.removeClass("active");
         btn_.attr("disabled",true);
         }*/
    });
});
