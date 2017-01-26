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
		price = Math.abs(price);
		volume = Math.abs(volume);
		price = price.toFixed(2);
		if(isNaN(price)) {
			$("#trade_form").find('input[name="price"]').val('');
		} else {
			$("#trade_form").find('input[name="price"]').val(price);
		}
		
		if(isNaN(volume)) {
			$("#trade_form").find('input[name="volume"]').val('');
		} else {
			$("#trade_form").find('input[name="volume"]').val(volume);
		}
		
		var amount = parseFloat(price*volume).toFixed(2);
		if(isFinite(price) && isFinite(volume)) {
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
				if(data.status == 1 || data.status == -1) {
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
				if(data.status == -1) {
					clearInterval(timeticket);
				}
    		},
    		error:function(){
    		},
    		dataType:'json'
    	});
	}
	getQuota();
	timeticket = setInterval(getQuota,60000);
	$("#refresh").on('click',function(){
		getQuota();
	});

	var dom = document.getElementById("container");
    var myChart = echarts.init(dom);
    var app = {};
    option = null;
    // 数据意义：开盘(open)，收盘(close)，最低(lowest)，最高(highest)
    var data0 = splitData([
        ['2013/5/9', 2246.96,2232.97,2221.38,2247.86,12,1200],
        ['2013/5/10', 2228.82,2246.83,2225.81,2247.67,12,1200],
        ['2013/5/13', 2247.68,2241.92,2231.36,2250.85,12,1200],
        ['2013/5/14', 2238.9,2217.01,2205.87,2239.93,12,1200],
        ['2013/5/15', 2217.09,2224.8,2213.58,2225.19,12,1200],
        ['2013/5/16', 2221.34,2251.81,2210.77,2252.87,12,1200],
        ['2013/5/17', 2249.81,2282.87,2248.41,2288.09,12,1200],
        ['2013/5/20', 2286.33,2299.99,2281.9,2309.39,12,1200],
        ['2013/5/21', 2297.11,2305.11,2290.12,2305.3,12,1200],
        ['2013/5/22', 2303.75,2302.4,2292.43,2314.18,12,1200],
        ['2013/5/23', 2293.81,2275.67,2274.1,2304.95,12,1200],
        ['2013/5/24', 2281.45,2288.53,2270.25,2292.59,12,1200],
        ['2013/5/27', 2286.66,2293.08,2283.94,2301.7,12,1200],
        ['2013/5/28', 2293.4,2321.32,2281.47,2322.1,12,1200],
        ['2013/5/29', 2323.54,2324.02,2321.17,2334.33,12,1200],
        ['2013/5/30', 2316.25,2317.75,2310.49,2325.72,12,1200],
        ['2013/5/31', 2320.74,2300.59,2299.37,2325.53,12,1200],
        ['2013/6/3', 2300.21,2299.25,2294.11,2313.43,12,1200],
        ['2013/6/4', 2297.1,2272.42,2264.76,2297.1,12,1200],
        ['2013/6/5', 2270.71,2270.93,2260.87,2276.86,12,1200],
        ['2013/6/6', 2264.43,2242.11,2240.07,2266.69,12,1200],
        ['2013/6/7', 2242.26,2210.9,2205.07,2250.63,12,1200],
        ['2013/6/13', 2190.1,2148.35,2126.22,2190.1,12,1200]
    ]);


    function splitData(rawData) {
        var categoryData = [];
        var values = []
        for (var i = 0; i < rawData.length; i++) {
            categoryData.push(rawData[i].splice(0, 1)[0]);
            values.push(rawData[i])
        }
        
        return {
            categoryData: categoryData,
            values: values
        };
    }

    option = {
        tooltip : {
            trigger: 'axis',
            formatter: function (params) {
                var res = params[0].name + '<br/>' + params[0].seriesName;
                res += '<br/>  开盘价 : ' + params[0].value[0] + '  <br/>最高价 : ' + params[0].value[3];
                res += '<br/>  收盘价 : ' + params[0].value[1] + '  <br/>最低价 : ' + params[0].value[2];
                res += '<br/>  成交量 : ' + params[0].value[4] + '  <br/>成交额 : ' + params[0].value[5];
                return res;
            }
        },
        grid: {
            left: '10%',
            right: '10%',
            bottom: '15%'
        },
        // series-candlestick.itemStyle.normal.color0:green,
        xAxis: {
            type: 'category',
            data: data0.categoryData,
            scale: true,
            boundaryGap : false,
            axisLine: {onZero: false},
            splitLine: {show: false},
            splitNumber: 20,
            min: 'dataMin',
            max: 'dataMax'
        },
        yAxis: {
            scale: true,
            splitArea: {
                show: true
            }
        },
        dataZoom: [
            {
                type: 'inside',
                start: 50,
                end: 100
            },
            {
                show: true,
                type: 'slider',
                y: '90%',
                start: 50,
                end: 100
            }
        ],
        series: [
            {
                name: '日K',
                type: 'candlestick',
                data: data0.values,
                itemStyle: {
                    normal: {
                        color0: '#4c9f1f'// 阴线填充颜色
                    }
                }, 
            }
        ]
    };

    ;
    if (option && typeof option === "object") {
        myChart.setOption(option, true);
    }
    
    $("#candlestick").css("display","none");
});
