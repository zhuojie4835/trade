```
流水号：T开头    后台入金    1
        S开头    认购        2
        G开头    挂单成交    3(买入) 6(卖出)
		Y开头    应价成交    4(卖出)  7(买入)
		R开头    注册奖励    5


		挂单状态：1   等待成交
				  2   部分成交
				  3   全部成交
				  4   撤单
				  //5   系统撤单
				  6   部分撤单

		成交类型：1   挂单买入成交
				  2   挂单卖出成交
				  3   应价买入成交
				  4   应价卖出成交

持仓对应场景
subscribe  认购    volume +  can_sell +
gd_out  挂单卖出   volume 平   can_sell -
gd_in   挂单买入   volume 平   can_sell 平
yj_in_yj 应价买入应价方  volume + can_sell +
yj_in_gd 应价买入挂单方  volume - can_sell 平
yj_out_gd 应价卖出挂单方  volume + can_sell +
yj_out_yj 应价卖出应价方  volume - can_sell -


场景对应的操作总结
注册
登录
后台充值
前台充值

认购      
	1.流水 follow 
	2.认购记录 subscribe_record 
	3.更新用户资金 user:uid 
	4.更新产品剩余数量 product_trade:pid
	5.持仓 position:uid:pid  数量、可卖、均价

挂单卖出  
	1.挂单记录 gd_record:gid  (hash) 
	2.冻结持仓  position:uid:pid
	3.挂单卖出价格集合 gd_out_price:pid (set)
	4.每个客户挂单gid集合  gid_by_person:uid  (zset) 
	5.挂单卖出每口价格的gid集合 gid_out_by_price:pid:price (zset)
	6.更新每口价格的数量、笔数 gd_out_price_detail:pid:price (hash)

应价买入  
	1.修改挂单记录状态 gd_record:gid  包括挂单数量、状态、应价时间
	2.挂单方资金变化  user:uid  
	3.挂单方库存  position:uid:pid  重新计算成本价
	4.挂单方流水  follow
	5.更新每口价格的数量、笔数 gd_out_price_detail:pid:price
	6.应价记录   yj_record:yid  (hash)       ????
	7.应价方流水 follow
	8.应价方资金变化 user:uid
	9.应价方库存变化 position:pid:uid
	10.价格吃完时把当前价格从挂单卖出价格集合中删除，从每口价格gid集合表中删除  gd_out_price_detail:pid:price  gd_out_price:pid
	11.更新商品交易信息 now_price high_price low_price
	12.把gid从 gid_in_by_price:pid:price 中删除（如果需要）
	13.挂单方、应价方成交记录 deals (list)
	14.更新成交量、成交额
	note:注意挂单方和应价方是同一人的特殊情况

挂单买入  
	1.资金冻结 user:uid
	2.挂单记录 gd_record:gid  (hash)
	3.挂单买入价格集合 gd_in_price:pid (set)
	4.每个客户挂单gid集合  gid_by_person:uid  (zset) 
	5.挂单买入每口价格的gid集合 gid_in_by_price:pid:price (zset)
	6.更新每口价格的数量、笔数 gd_in_price_detail:pid:price (hash)

应价卖出
	1.修改挂单记录状态 gd_record:gid 包括挂单数量、状态、应价时间
	2.挂单方资金变化  user:uid  扣除冻结资金
	3.挂单方库存  position:uid:pid  重新计算成本价
	4.挂单方流水  follow
	5.更新每口价格的数量、笔数 gd_out_price_detail:pid:price
	6.应价记录   yj_record:yid  (hash)        ????
	7.应价方流水 follow
	8.应价方资金变化 user:uid
	9.应价方库存变化 position:pid:uid
	10.价格吃完时把当前价格从挂单卖出价格集合中删除，从每口价格gid集合表中删除  gd_in_price_detail:pid:price  gd_out_price:pid
	11.更新商品交易信息 now_price high_price low_price
	12.把gid从 gid_in_by_price:pid:price 中删除（如果需要）
	13.挂单方、应价方成交记录 deals (list)
	14.更新成交量、成交额

撤销买入挂单
	1.修改gd_record:gid 状态，数量   (hash)
	

撤销卖出挂单
	1.修改gd_record:gid 状态，数量  (hash)
	2.从挂单每口价格集合表中删除 gid_out_by_price:pid:price (zset)
	3.更新每口价格的数量、笔数 gd_out_price_detail:pid:price (hash) 如果volume为0时，撤销记录
	4.把gid从 gd_out_price:pid 中删除 (set 如果需要)
	5.冻结商品划入到可卖 position:pid:uid

日结撤销买入挂单
	1.挂单状态是等待成交、已撤销、部分成交
	2.从挂单每口价格集合表中删除   gid_in_by_price:pid:price (zset)
	3.更新每口价格的数量、笔数    gd_in_price_detail:pid:price (hash)
	4.把gid从 gd_in_price:pid   中删除(set 如果需要)
	5.扣除冻结资金 user:uid
	6.删除等待成交、已撤销的记录 gd_record:gid(hash) gid_by_person:uid(zset)
	7.部分成交记录的状态改成部分撤销


定时任务
workerman 每3秒同步至Mysql Admin/Coordinate/coordinateRedis

身份证号码：
431281199205012648
421222199202010119
342623198903096810
422302198211132712
430821198912144835
430821198912144819
430821198807064817


今日任务：完成应价

note:
1.
2.用户产品信息如果修改需要同步到redis
3.应价逻辑代码
4.挂单买入成交时，可用余额优化
5.应价方法太长，但是如果需要优化，必须十分谨慎
6.成交记录、资金流水冗余customer_type
bug
1.后台修改状态时没有同步到redis
2.绑定代理提示客户类型错误

血泪史：
1、验证下单的来源的合法性
2、验证重复下单
3、是否存在特殊字符
4、投资金额是不是在限制范围内
5、账户属性
6、验证商品是否可交易是否存在
7、验证资金账户账户是否一致有没有被串改
8、验证商品交易日 非交易日 商品停盘
9、验证层级的合法性
10、验证买家余额
11、买单余额进行变动（加锁进行防止出入金影响余额）
12、返佣异步处理
13、账户发生每笔变动记录流水和日志（流水记清楚发送者 接受者 什么类型的资金变动）
14、读取手续费配置和返佣比例设置 扣款 返佣会发现账对不上（交易所一开始设置好了参数盘中不能设置）
15、任何API接口调整不要动输入输出参数 （实在没有办法必须调整接口 沟通好各部门）


交易流程：
1.交易日开市前半个小时清理数据（开盘价、收盘价、最高价、最低价、涨跌额）
2.正常交易
3.休市后半个小时处理挂单
4.休市后一个小时日结（生产当日行情、持仓）

```
