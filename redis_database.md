* 用户表  user:uid (hash)

|字段名称    |类型     |备注|
| --------   | -----: | ----:  |
| uid     | int  | 用户id|
| mobile     | int  |     客户手机号码|
| name     | int  |       客户姓名|
| id_card_number     | varchar  |      身份证号码|
| agent_number     | varchar  |       代理编号|
| user_type     | int  |     客户类型|
| operator_number     | varchar  |       运营中心编号|
| agent_member_number     | varchar  |       所属会员编号|
| register_time     | int  |       创建时间|
| free_money     | float  |       可用资金|
| freeze_money     | float  |       冻结资金|

* 资金流水  follow (list)

|字段名称    |类型     |备注|
| --------   | -----: | ----:  |
| follow_number     | varchar  | 流水号|
| customer_id     | int  |     客户uid|
| customer_mobile     | int  |       客户手机|
| customer_name     | varchar  |      客户姓名|
| follow_type     | int  |       流水类型|
| business_description     | varchar  |     交易描述|
| money     | float  |       交易金额|
| freeze_money     | float  |       冻结金额|
| new_money     | float  |       变动后的金额|
| create_time     | int  |       创建时间|

* 商品交易信息表  product_trade : pid (hash)

|字段名称    |类型     |备注|
| --------   | -----: | ----:  |
| short_name     | varchar  | 商品简称|
| product_number     | varchar  |     商品编号|
| low_price     | float  |       最低价|
| high_price     | float  |       最高价|
| now_price     | float  |       最新价|
| min_price     | float  |       跌停价|
| max_price     | float  |       涨停价|
| open_price     | float  |       开盘价|
| close_price     | float  |       收盘价|
| total_number     | int  |      商品数量|
| left_number     | int  |       剩余数量|
| status     | int  |     状态|

* 认购记录  subscribe_record (list)

|字段名称    |类型     |备注|
| --------   | -----: | ----:  |
| pid     | int  |     产品id|
| customer_id     | int  |       用户id|
| price     | float  |      认购价格|
| volume     | int  |       认购数量|
| can_sell     | int  |       可卖数量|
| product_number     | varchar  |     商品编码|
| short_name     | varchar  |       商品简称|
| status     | int  |       商品状态|
| customer_name     | varchar  |        用户姓名|
| customer_mobile     | int  |       用户手机|
| create_time     | int  |       创建时间|

* 持仓表 position : uid : pid (hash) 

|字段名称    |类型     |备注|
| --------   | -----: | :----:  |
| uid     | int  |        用户id|
| short_name     | vachar  |        商品简称|
| product_number     | vachar  |        商品代码|
| average_price     | float  |        平均价格|
| volume     | int  |        持仓数量|
| can_sell     | int  |        可卖数量|
| status     | int  |        商品状态|

* 挂单记录 gd_record:gid (list)

|字段名称    |类型     |备注|
| --------   | -----: | :----:  |
| gid     | int  |        挂单自增id|
| uid     | int  |        客户id|
| mobile     | int  |        客户手机号码|
| name     | varchar  |        客户姓名|
| pid     | int  |        商品id|
| price     | float  |        价格|
| volume     | int  |        数量|
| volume_p     | int  |        数量，不随应价而减少|
| create_time     | int  |       创建时间|
| yj_time     | int  |       应价时间|
| status     | int  |       商品状态|
| gd_status     | int  |       挂单状态|
| direct     | varchar  |       挂单方向|
| product_number     | varchar  |       商品代码|
| short_name     | varchar  |       商品简称|

* 挂单买入价格集合表 gd_in_price : pid (set)

* 每个客户挂单gid集合表 gid_by_person : uid : pid (zset)

* 每口价格的gid集合表 gid_in_by_price : pid : price (zset)

* 每口价格的挂单详情表 gd_in_price_detail : pid : price (hash)

|字段名称    |类型     |备注|
| --------   | -----: | :----:  |
| pid     | int  |        商品id|
| price     | float  |        价格|
| volume     | int  |        数量|
| count     | int  |        笔数|
| status     | int  |       状态|
| direct     | int  |       挂单方向|
| product_number     | varchar  |       商品代码|
| short_name     | varchar  |       商品简称|

* 挂单卖出价格集合表 gd_out_price : pid (set)

* 挂单卖出每口价格的gid集合表 gid_out_by_price : pid : price (zset)

* 挂单卖出每口价格的挂单详情表 gd_out_price_detail : pid : price (hash)

|字段名称    |类型     |备注|
| --------   | -----: | :----:  |
| pid     | int  |        商品id|
| price     | float  |        价格|
| volume     | int  |        数量|
| count     | int  |        笔数|
| status     | int  |       状态|
| direct     | int  |       挂单方向|
| product_number     | varchar  |       商品代码|
| short_name     | varchar  |       商品简称|

* 成交记录表 deals (list)

|字段名称    |类型     |备注|
| --------   | -----: | :----:  |
| pid     | int  |        商品id|
| price     | float  |        价格|
| volume     | int  |        数量|
| trade_money     | flaot  |        成交金额|
| deals_type     | int  |       成交类型|
| customer_id     | int  |       客户id|
| customer_mobile     | int  |       客户手机号码|
| customer_name     | varchar  |       客户姓名|
| product_number     | varchar  |       商品代码|
| short_name     | varchar  |       商品简称|
| create_time     | int  |       创建时间|