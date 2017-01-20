$(function(){
	update();
	var tabsSwiper = new Swiper('#user-tabs-container',{
		speed:500,
		onSlideChangeStart: function(){
			$(".tabs .active").removeClass('active')
			$(".tabs li").eq(tabsSwiper.activeIndex).addClass('active')
		}
	})
	$(".swiper-wrapper").height();
	$(".user_box_one").find(".hide_ul").eq(0).css("height",'34px');
	$(".tabs li").on('touchstart mousedown',function(e){
		e.preventDefault()
		$(".tabs .active").removeClass('active')
		$(this).addClass('active')
		tabsSwiper.swipeTo( $(this).index() )
	})
	$(".tabs li").click(function(e){
		e.preventDefault()
	});
	$("#clickOpenNav").on("click",function(){
		$("body").toggleClass("open_user_box");
	});

	$(".hidethis").height($(window).height()-57);

	$(document).on('click','.oBtn',function(){
		var uid = $(this).data("uid");
		if(uid == 3) {
			var gd_detail = $(this).data('gd_detail');
			var cancel_msg = $(this).data('cancel_msg');
			var gid = $(this).data('gid');
			$("#gid").val(gid);
			$("#gd_detail").html(gd_detail);
			$("#cancel_msg").html(cancel_msg);
		}
		$(".hidden_box"+uid).addClass("show_box");
	});
	
	$(".closeBox").on("click",function(){
		$(".hidden_box").removeClass("show_box");
	})

	$(document).on('click','.click_dl',function(){
		$(".hide_ul").height("0");
		$(this).find(".hide_ul").height("30px");
	});

	//撤销挂单
	$("#cancel-btn").on('click',function(){
		var gid = $("#gid").val();
		$.post(cancel_url,{gid:gid},function(data){
			alert(data.msg);
			$(".hidden_box").removeClass("show_box");
			if(data.status == 1) {
				update();
			}
		});
	});
	
	//弹出隐藏层
    function showDiv(show_div, bg_div) {
        document.getElementById(show_div).style.display = 'block';
        document.getElementById(bg_div).style.display = 'block';
        var bgdiv = document.getElementById(bg_div);
        bgdiv.style.width = document.body.scrollWidth;
        $("#"+bg_div).height($(document).height());
    }
    ;
    //关闭弹出层
    function closeDiv(show_div, bg_div)
    {
        document.getElementById(show_div).style.display = 'none';
        document.getElementById(bg_div).style.display = 'none';
    }
	
	//小数加法函数，解决js加法bug
    function accAdd(arg1, arg2) {
        var r1, r2, m, c;
        try {
            r1 = arg1.toString().split(".")[1].length;
        }
        catch (e) {
            r1 = 0;
        }
        try {
            r2 = arg2.toString().split(".")[1].length;
        }
        catch (e) {
            r2 = 0;
        }
        c = Math.abs(r1 - r2);
        m = Math.pow(10, Math.max(r1, r2));
        if (c > 0) {
            var cm = Math.pow(10, c);
            if (r1 > r2) {
                arg1 = Number(arg1.toString().replace(".", ""));
                arg2 = Number(arg2.toString().replace(".", "")) * cm;
            } else {
                arg1 = Number(arg1.toString().replace(".", "")) * cm;
                arg2 = Number(arg2.toString().replace(".", ""));
            }
        } else {
            arg1 = Number(arg1.toString().replace(".", ""));
            arg2 = Number(arg2.toString().replace(".", ""));
        }
        return (arg1 + arg2) / m;
    }
	
	function update() {
        $.ajax({
            type:'post',
            url:current_url,
            data:{act:'position',ignore:1},
            dataType:'json',
            success:function(data){
                if(data.status == 1) {
                    var total_market_value = data.total_market_value;
                    var total_profit = data.total_profit;
                    var html = gd_html = '';
                    var list = data.p_list;
                    var gd_list = data.gd_list;
                    var userinfo = data.user_info;
                    var assets;
                    
                    for(x in list) {
                        var info = list[x];
                        html += '<li>'+
                                    '<dl class="click_dl">'+
                                        '<dd class="lhdd"><span>'+info.short_name+' '+info.product_number+'<br/>'+info.market_value+'</span></dd>'+
                                        '<dd class="lhdd">'+info.now_price+'<br/>'+info.average_price+'</dd>'+
                                        '<dd class="lhdd">'+info.volume+'<br/>'+info.can_sell+'</dd>'+
                                        '<dd><a href="javascript:;">'+info.profit+'</a></dd>'+
                                        '<div class="clear"></div>'+
                                        '<div class="hide_ul">'+
                                        //     '<a href="javascript:;">行情</a>'+
                                        //     '<a class="bgw" href="{:U('delivery')}">提货</a>'+
                                        //     '<a href="javascript:;"  data-uid="1">挂单买入</a>'+
                                        //     '<a href="javascript:;"  data-uid="2">挂单卖出</a>'+
                                        '</div>'+
                                    '</dl>'+
                                '</li>';
                    }
                    assets = accAdd(accAdd(total_market_value,userinfo.free_money),userinfo.freeze_money);
                    assets = parseFloat(assets).toFixed(2);
                    free_money= parseFloat(userinfo.free_money).toFixed(2);
                    freeze_money= parseFloat(userinfo.freeze_money).toFixed(2);
                    $("#p_list").html(html);
                    $("#total_market_value").html(total_market_value);
                    $("#total_profit").html(total_profit);
                    $("#name").html(userinfo.name);
                    $("#mobile").html(userinfo.mobile);
                    $("#free_money").html(free_money);
                    $("#freeze_money").html(freeze_money);
                    $("#assets").html(assets);

                    for(x in gd_list) {
                        var info = gd_list[x];
                        var style = "";
                        if(info.gd_status == 1 || info.gd_status == 2) {
                            style = 'bgw oBtn';
                        }
                        gd_html += '<li>'+
                                '<dl class="click_dl">'+
                                    '<dd class="lhdd"><span>'+info.direct_text+' '+info.short_name+info.product_number+'<br/>'+info.volume_p+'@'+info.price+'</span></dd>'+
                                    '<dd>'+info.volume+'</dd>'+
                                    '<dd>'+info.gd_status_text+'</dd>'+
                                    '<dd><a  href="javascript:;">'+info.create_time+'</a></dd>'+
                                    '<div class="clear"></div>'+
                                    '<div class="hide_ul two_hide">'+
                                        '<a class="bgw" href="'+info.url+'">行情</a>'+
                                        '<a class="'+style+'" data-uid="3" data-cancel_msg="'+info.cancel_msg+'" data-gd_detail="'+info.gd_detail+'" data-gid="'+info.gid+'">撤单</a>'+
                                    '</div>'+
                                '</dl>'+
                            '</li>'
                    }
                    $("#gd_list").html(gd_html);
                }
            },
            error:function(){}
        });
    }
    setInterval(update,30000);
});

    

    
    
    