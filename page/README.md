[toc]

***

### 我的订单传参
- 传参实例： `myOrder.html?type=ALL`

|参数|说明|备注|
|---|---|---|
|ALL|全部||
|WAITPAY|待付款|
|WAITRECEIVE|待收货|
|FINISH|已完成|
|CANCEL|已取消|
||退货单|无 tab 页面|



***

### 文章页面 【探索国典、鉴定机制、消费者告知书、经营信息、隐私权策略】
- 传参实例： `/page/article.html?type=5`

|参数|说明|备注|
|---|---|---|
|type=1|探索国典||
|type=2|鉴定机制|
|type=3|消费者告知书|
|type=4|经营信息|
|type=5|隐私权策略|
|type=6|我的闲置|
|type=7|我的养护|


***

### 支付成功失败页面
- 传参实例： `/page/orderSuccess.html?status=success`

|参数|说明|备注|
|---|---|---|
|status=success|支付成功||
|status=fail|支付失败||



***

### 优惠券页面
- 传参实例： `/page/myCoupon.html?type=0`

|参数|说明|备注|
|---|---|---|
|type=0|未使用||
|type=1|已使用||
|type=2|已失效||


***

# 页面说明

|页面|链接|说明|传参方式|备注|bug|其他|
|---|---|----|---|---|---|---|
|传参页面|empty|传id等信息|user_id=xx|
|退出登录|out.html|
|购物袋|shoppingBag|ok|user_id=xx|
|详情页|commodity|ok|goods_id=xxx|
|发现-文章详情|article2.html|article_id=xx|**点赞功能未作**
|待付款|orderWait |弃用||
|待收货|orderReceive |弃用||
|退货单|orderReturn |**loading**||
|全部订单|orderAll |弃用||
|会员中心|myMember|ok|**静态页**
|优惠券|myCoupon|ok|激活优惠券不做|传参在下面|
|优惠券领取|myCouponLingqu|ok||
|选择优惠券|myCouponChoose|ok|
|我的积分|myIntegral|升级中|**loading**
|我的闲置|article|传参在上面|
|我的养护|article|传参在上面|
|文章页（探索国典）|article|ok|type=1|
|鉴定机制|article.html|ok|type=2
|消费者告知书|myInform  改为 article.html|CHANGE|type=3
|经营信息|myManagement 改为 article.html|CHANGE|type=4|
|隐私权政策|myPrivacy 改为 article.html |CHANGE|type=5
|我的订单|myOrder|ok|订单状态类型：全部 ALL ，待付款： WAITPAY ；待发货： WAITSEND ； 待收货： WAITRECEIVE ；已完成： FINISH ；已取消： CANCEL|下面有详细文档
|订单详情|orderDetail|ok|
|订单详情-配送方式|peisongMethod|ok|
|结算中心|jieshuan|ok|
|选择地址|addressChoose|ok|
|选择管理列表|addressList|ok|
|编辑地址-添加地址|addressEdit|ok|
|分享|升级中
|银联支付页面|payLoad|接口返回的html|status=pay|需要IOS Android 加返回按钮
|支付成功、失败页面|orderSuccess.html||success：支付成功； fail：支付失败
|在线客服|news|
|客服与帮助|kfAndHelp.html|
|测试页面|MINE|||自己测试用

***

# 服务器地址



- 正式地址： `http://www.guodianjm.com/page/xxx.html?xxx=xxx`
- 测试地址： `http://guodian.staraise.com.cn/page/页面?参数`
- 测试后台:  `http://guodian.staraise.com.cn/admin`

***

- UI 蓝湖 地址

> https://lanhuapp.com/web/#/item


