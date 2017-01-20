$(function(){
	$(document).on('click','.oBtn',function(){
		checkLogin();
		var type = $(this).data('type');
		var text = $(this).data('text');
		
		$("#trade_title").html(text);
		if(type == 'gd_in' || type == 'gd_out') {
			$("#trade_text").html('<label>挂单价格:</label><input type="hidden" name="trade_type" value="gd"><input type="text" class="jisuan" name="price" placeholder="请输入挂单价格">');
			$("#trade_volume").html('<label>挂单数量:</label><input type="text" name="volume" class="jisuan" placeholder="请输入挂单数量" />');
			$("#trade_money").html('<label>挂单金额:</label><span id="jisuan_money"></span>');
			type == 'gd_in' && $("#trade_btn").html('<input type="hidden" name="direct" value="b"><input class="sub_btn" type="button" value="挂单买入" />');
			type == 'gd_out' && $("#trade_btn").html('<input type="hidden" name="direct" value="s"><input class="sub_btn" type="button" value="挂单卖出" />');
		} else if(type == 'yj_in') {
			var price = $(this).data('price');
			$("#trade_text").html('<label>买入价格:</label><span>'+price+'</span><input type="hidden" name="trade_type" value="yj"><input type="hidden" name="direct" value="s"><input type="hidden" class="jisuan" name="price" value='+price+'>');
			$("#trade_volume").html('<label>买入数量:</label><input type="text" name="volume" class="jisuan" placeholder="请输入买入数量" />');
			$("#trade_btn").html('<input class="sub_btn" type="button" value="直接买入" />');
			$("#trade_money").html('<label>买入金额:</label><span id="jisuan_money"></span>');
		} else if(type == 'yj_out') {
			var price = $(this).data('price');
			$("#trade_text").html('<label>卖出价格:</label><span>'+price+'</span><input type="hidden" name="trade_type" value="yj"><input type="hidden" name="direct" value="b"><input type="hidden" class="jisuan" name="price" value='+price+'>');
			$("#trade_volume").html('<label>卖出数量:</label><input type="text" name="volume" class="jisuan" placeholder="请输入卖出数量" />');
			$("#trade_btn").html('<input class="sub_btn" type="button" value="直接卖出" />');
			$("#trade_money").html('<label>卖出金额:</label><span id="jisuan_money"></span>');
		}
		$(".hidden_box").addClass("show_box");
	
	});

	$(".closeBox").on("click",function(){
		$(".hidden_box").removeClass("show_box");
	});

	//计算金额
	$(document).on('change','.jisuan',function(){
		var price = parseFloat($("#trade_form").find('input[name="price"]').val());
		var volume = parseInt($("#trade_form").find('input[name="volume"]').val());
		price = price.toFixed(2);
		$("#trade_form").find('input[name="price"]').val(price);
		
		var amount = parseFloat(price*volume).toFixed(2);
		if(price && volume) {
			$("#jisuan_money").html(amount);
		} else {
			$("#jisuan_money").html('');
		}
	});
	$(document).on('click','.sub_btn',function(){
		var price = parseFloat($("#trade_form").find('input[name="price"]').val());
		var volume = parseInt($("#trade_form").find('input[name="volume"]').val());
		var direct = $("#trade_form").find('input[name="direct"]').val();
		var trade_type = $("#trade_form").find('input[name="trade_type"]').val();
		var url;
		trade_type == 'gd' ? url = gd_url : '';
		trade_type == 'yj' ? url = yj_url : '';
		
		$.ajax({
			type:'post',
			url:url,
			data:{price:price,volume:volume,direct:direct,pid:pid},
			dataType:'json',
			success:function(data){
				alert(data.msg);
				getQuota();
				if(data.status == 1) {
					$(".hidden_box").removeClass("show_box");
				}
			},
			error:function(){
				alert('操作失败');
			}
		});
	});
	//登录检查
	function checkLogin() {
		$.get(isLoginUrl,function(data){
			if(data.status == 0) {
				window.location = loginUrl;
			}
		});
	}
	
	function getQuota() {
		var id = pid;
		$.ajax({
    		type:'post',
    		url:updateQuotaUrl,
    		data:{id:id,ignore:1},
    		success:function(data){
				if(data.status == 1) {
					var gd_in_quota =  data.gd_in_quota;
					var gd_out_quota =  data.gd_out_quota;
					var product_info = data.product_info;
					var change,change_percent;
					var gd_in_html = '';
					var gd_out_html = '';
					var style,style1='';
					var sign_flag = '';
					
					change = parseFloat(product_info.now_price-product_info.open_price).toFixed(2);
					if(change>0) {
						sign_flag = '+';
						$(".art_pro_col").removeClass('green');
						$(".art_pro_col").addClass('red');
						// $("#arrow").attr('src','__IMG__/red-icon.png');
					} else if(change<0) {
						$(".art_pro_col").removeClass('red');
						$(".art_pro_col").addClass('green');
						// $("#arrow").attr('src','__IMG__/green-icon.png');
					} else {
						// $("#arrow").remove();
					}
					
					change_percent = (change/product_info.open_price)*100;
					change_percent = parseFloat(change_percent).toFixed(2);
					
					var length = gd_in_quota.length;	
					for(var i=length; i>0; i--) {
						quota_in = gd_in_quota[i-1];
							
						if(length-i == 0) {
							style = 'bgweight';
						} else {
							style = '';
						}
						gd_in_html += '<li>'+
										'<dl>'+
											'<dd><span>买</span><em class="'+style+'">'+(length-i+1)+'</em></dd>'+
											'<dd class="numVal">'+quota_in.price+'</dd>'+
											'<dd>'+quota_in.volume+'('+quota_in.count+')</dd>'+
											'<dd><a data-type="yj_out" data-text="应价卖出确认" data-price='+quota_in.price+' class="oBtn bgy" href="javascript:;">应价卖出</a></dd>'+
											'<div class="clear"></div>'+
										'</dl>'+
									'</li>';
					}
					for(i=0;i<5-length;i++) {
						gd_in_html += '<li>'+
										'<dl>'+
											'<dd><span>买</span><em>'+(length+1+i)+'</em></dd>'+
											'<dd class="numVal">--</dd>'+
											'<dd>--</dd>'+
											'<dd>--</dd>'+
											'<div class="clear"></div>'+
										'</dl>'+
									'</li>';
					}

					for(i=0; i<5; i++) {
						quota_out = gd_out_quota[4-i];
						if(i == 4) {
							style = 'bgweight';
						} else {
							style = '';
						}
						if(quota_out) {
							gd_out_html += '<li>'+
										'<dl>'+
											'<dd><span>卖</span><em class="'+style+'">'+(5-i)+'</em></dd>'+
											'<dd class="numVal">'+quota_out.price+'</dd>'+
											'<dd>'+quota_out.volume+'('+quota_out.count+')</dd>'+
											'<dd><a data-type="yj_in" data-text="应价买入确认" data-price='+quota_out.price+' class="oBtn bgg" href="javascript:;">应价买入</a></dd>'+
											'<div class="clear"></div>'+
										'</dl>'+
									'</li>';
						} else {
							gd_out_html += '<li>'+
										'<dl>'+
											'<dd><span>卖</span><em>'+(5-i)+'</em></dd>'+
											'<dd class="numVal">--</dd>'+
											'<dd>--</dd>'+
											'<dd>--</dd>'+
											'<div class="clear"></div>'+
										'</dl>'+
									'</li>';
						}
					}
					
					$(".product_title").html(product_info.short_name+' '+product_info.product_number);
					$("#high_price").html(product_info.high_price);
					$("#low_price").html(product_info.low_price);
					$("#total_number").html(product_info.total_number);
					$("#left_number").html(product_info.total_number);
					$("#now_price").html(product_info.now_price);
					$("#change").html(sign_flag+change);
					$("#change_percent").html(sign_flag+change_percent+'%');
					$("#gd_in_html").html(gd_in_html);
					$("#gd_out_html").html(gd_out_html);
					$("#trade_price").html('<em>'+product_info.now_price+'</em>跌停:<em>'+product_info.min_price+'</em>涨停:<em>'+product_info.max_price+'</em>');
				}
    		},
    		error:function(){
    		},
    		dataType:'json'
    	});
	}
	getQuota();
	setInterval(getQuota,30000);
	$("#refresh").on('click',function(){
		getQuota();
	});
});
