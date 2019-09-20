/**
 * @user_id     【用户id】
 * @order_id    【订单id】
 * @wuliuStatus 【物流加载成功 0 1】
 * @orderName   【商品标题】
 * @kuaidiName  【快递标题】
 * @orderMSG    【订单信息】
 * @invoice_no  【快递单号】
 */
let order_id = getParam('order_id');
let wuliuStatus = 0;

let myUsetInfo = localStorage.getItem('USERINFO');
myUsetInfo = JSON.parse(myUsetInfo);
console.log(myUsetInfo)
let user_id = myUsetInfo.user_id;

let orderName = '';
let kuaidiName = '';
let invoice_no = "";

/**=================================================================================
 *          加载
 * =================================================================================
 */
createOrder(order_id, user_id); // 加载订单详情


/**=================================================================================
 *          点击
 * =================================================================================
 */
$('.ctr').delegate('span', 'click', function () {
    console.log($(this).attr('data-type'))
    console.log($(this).attr('data-order'))
    switch ($(this).attr('data-type')) {
        case 'pay':
            pay($(this).attr('data-order'));
            break;
        case 'cancel':
            cancelOrder($(this).attr('data-order'));
            break;
        case 'pingjia':
            pay($(this).attr('data-order'));
            break;
    }
})





// 加载订单详情
function createOrder(order_id) {
    $.ajax({
        type: 'post',
        url: GlobalHost + '/Api/order/order_detail',
        data: {
            order_id: order_id,
            user_id: user_id
        },
        success: function (res) {
            console.log(res);
            let data = res.data;
            orderMSG = data;
            orderName = data.goods_list[0].goods_name;
            invoice_no = data.invoice_no;
            // getCountDown(data.add_time)
            /**判断订单状态
             * @order_status_code   【待付款：WAITPAY，待发货：WAITSEND， 待收货：WAITRECEIVE，待评价：WAITCCOMMENT，REFUND 已申请退款】
             * @class[tips]         【金色提示】
             * @class[order-track]  【订单跟踪】
             */
            if (data.add_time) {
                var time_ = formatDateCom(data.add_time);
                console.log(time_)
            }
            switch (data.order_status_code) {
                case 'WAITPAY': // 待付款
                    var timestamp = data.add_time
                    timestamp *= 1000
                    timestamp = Number(timestamp)
                    setInterval(function () {
                        var nowTime = new Date();
                        var endTime = new Date(timestamp * 1000);
                        var t = endTime.getTime() - nowTime.getTime();
                        var hour = Math.floor(t / 1000 / 60 / 60 % 24);
                        var min = Math.floor(t / 1000 / 60 % 60);
                        var sec = Math.floor(t / 1000 % 60);

                        if (hour < 10) {
                            hour = "0" + hour;
                        }
                        if (min < 10) {
                            min = "0" + min;
                        }
                        if (sec < 10) {
                            sec = "0" + sec;
                        }
                        countDownTime = hour + "小时" + min + "分" + sec + "秒";
                        $('.tips').html(`<div class="writpay">
                                <div class="title">
                                    <p>待付款</p>
                                    <p class="price">￥${data.total_amount}</p>
                                </div>
                                <div class="title">
                                    <p>${"剩余" + hour + "小时" + min + "分" + sec + "秒 系统将自动取消订单"}</p>
                                    <p class="price">应付金额</p>
                                </div>
                            </div>`)
                    }, 200);
                    // $('.tips').html(`<div class="writpay">
                    //                     <div class="title">
                    //                         <p>待付款</p>
                    //                         <p class="price">￥${data.total_amount}</p>
                    //                     </div>
                    //                     <div class="title">
                    //                         <p>123</p>
                    //                         <p class="price">应付金额</p>
                    //                     </div>
                    //                 </div>`)
                    $('.order-track').html(`<div class="showWuLiu order-title more text-df md">
                                                <div class="writpay">
                                                    <p class="w-1">订单跟踪</p>
                                                    <p class="w-1 text-xs">您的订单已提交，等待系统确认</p>
                                                    <p class="text-xs op-4">${time_}</p>
                                                </div>
                                            </div>`)
                    // getCountDown($('.tips .time'), data.add_time);
                    getWuLiu()
                    break;
                case 'WAITSEND': // 待发货   TODO: 缺少UI
                    $('.tips').html('');
                    $('.order-track').html('')
                    break;
                case 'WAITRECEIVE': // 待收货
                        $('.tips').html(`<div class="waitreceive">
                                            <div class="left">
                                                <p class="text-lg">卖家已发货</p>
                                                <p>还剩</p>
                                            </div>
                                            <div class="right">
                                                <img src="./src/img/icon/daishouhuo.png" alt="">
                                            </div>
                                        </div>`);
                        $('.order-track').html(`<div  class="showWuLiu order-title more text-df md" data-order_id="${data.order_id}">
                                                <div class="waitreceive">
                                                    <p class="w-1">运输中</p>
                                                    <p class="w-1 text-xs">顺丰快递单号：123</p>
                                                </div>
                                            </div>`)
                        getWuLiu(); // 加载物流信息
                    break;
                case 'WAITCCOMMENT': // 待评价
                    $('.tips').html(`<div class="waitccomment">
                                        <p>${data.order_status_desc}</p>
                                        <img src="./src/img/icon/daishouhuo.png" alt="">
                                    </div>`);
                    $('.order-track').html(`<div class="order-title more text-df md">
                                                    <div class="showWuLiu waitccomment">
                                                    <p class="w-1">已签收</p>
                                                    <p class="w-1 text-xs">${data.shipping_name}： 没有数据</p>
                                                </div>
                                            </div>`)
                        getWuLiu(); // 加载物流信息
                    break;
                case 'CANCEL': // 已取消
                    $('.tips').html(`<div class="CANCEL">
                                        <p>已取消</p>
                                    </div>`);
                    $('.order-track').html('')
                    break;
                case "REFUND": // 退货单
                    let pay_status_str = '';
                    switch(data.pay_status) {
                        case "1": // 卖家确认中
                            pay_status_str = "卖家确认中";
                            break;
                        case "3": // 已退款
                            pay_status_str = "已退款";
                            break;
                        case "4": // 已拒绝
                            pay_status_str = "已拒绝";
                            break;
                        default:
                            console.log('判断退款状态出错')
                            break;
                    }
                    $('.tips').html(`<div class="waitccomment">
                        <p>${data.order_status_desc}</p>
                        <img src="./src/img/icon/daishouhuo.png" alt="">
                    </div>`);
                    $('.order-track').html(`<div data-type="REFUND" class="order-title more text-df md">
                            <div class="showWuLiu waitccomment">
                            <p class="w-1">退款</p>
                            <p class="w-1 text-xs">${data.order_status_desc}： ${pay_status_str}</p>
                        </div>
                    </div>`)
                    break;
                default:
                    break;
            }

            /**判断配送方式
             * @buy_method  【配送方式 1 到店自提 2 快递送货】
             */
            switch (data.buy_method) {
                case '1':
                    $('.buy_method .con').text('到店自提');
                    break;
                case '2':
                    $('.buy_method .con').text('快递送货');
                    break;
            }

            /**收货地址
             * @class[address-con consignee]    收货人 + 手机号
             * @class[address-con fulladdress]  详细地址
             */
            $('.address-con .consignee').text(data.consignee == '' ? '收货人信息为空' : data.consignee + ' ' + data.mobile == '' ? '手机号码为空' : data.mobile);
            $('.address-con .fulladdress').text(data.fulladdress == '' ? '地址信息为空' : data.fulladdress);

            /**身份证号
             * @class[ID_number]
             */
            $('.ID_number .con').text(data.ID_number == '' ? '身份证信息为空' : data.ID_number);

            /**商品
             * 
             */
            let listStr = '';
            data.goods_list.forEach(item => {
                listStr += `<li class="list-item pd-df bd-df">
                                <div class="poster">
                                    <img src="${GlobalHost + item.original_img}" alt="">
                                </div>
                                <div class="info">
                                    <p>${item.goods_name}</p>
                                    <div class="tag-wrap op-4">
                                        <div class="left">
                                            <span class="tag">${item.spec_key_name}</span>
                                        </div>
                                        <p class="count">x${item.goods_num}</p>
                                    </div>
                                    <p class="price">￥${item.member_goods_price}</p>
                                </div>
                            </li>`
            });
            $('.shop-content .content-list').html(listStr);

            /**价格 订单
             * @shop_count      【商品数目】
             * @total_amount    【总金额】
             * @order_sn        【订单编号】
             * @add_time        【订单创建时间】
             */
            $('.shop_count').text('共' + 1 + '件商品');
            $('.total_amount').text('￥' + data.total_amount);
            $('.order_sn').html(`订单编号：${data.order_sn} <span data-copy="${data.order_sn}" class="fuzhi">复制</span>`);
            data.add_time = formatDateCom(data.add_time);
            console.log(data)

            $('.ID_number .con').text(data.ID_number)
            $('.add_time').text('下单时间：' + data.add_time);

            /**按钮显示
             * 0 隐藏  1 显示
             * @pay_btn  	    【支付】
             * @cancel_btn   	【取消】
             * @comment_btn  	【评价】
             */
            if (data.pay_btn == 1) {
                $('.ctr').append(`<span data-order="${data.order_id}" data-type="pay">支付</span>`)
            }
            if (data.cancel_btn == 1) {
                $('.ctr').append(`<span data-order="${data.order_id}" data-type="cancel">取消</span>`)
            }
            if (data.comment_btn == 1) {
                $('.ctr').append(`<span data-order="${data.order_id}" data-type="pingjia">评价</span>`)
            }
            $('.ctr span').addClass('cancelbtn')
            $('.ctr span:last').addClass('paybtn')
            if (data.pay_btn == 0 && data.cancel_btn == 0 && data.comment_btn == 0) {
                $('.ctr').hide();
            }
        }
    })
}

// 点击复制
$('body').delegate('.fuzhi', 'click', function () {
    console.log($(this).attr('data-copy'))
    // console.log(copyToClipboard($(this).attr('data-copy')))
    // copyToClipboard($(this).attr('data-copy'))
    copyToClipboard($(this).attr('data-copy'))
    // createAlert($('.alert-tips'), 'alert_tips', msg);
})



// 物流显示
$('body').delegate('.order-title.more', 'click', function () {
    console.log($(this).attr('data-type'))
    switch($(this).attr('data-type')) {
        case "REFUND":
            getTuiKuan()
            break;
        default:
            $('.alert-box').show();
            $('.alert-yunshu').show();
            console.log(wuliuStatus)
            break;
    }
    // if(wuliuStatus == 0) {
    //     $('.loading').text('物流信息加载中...').show();
    // } else {
    //     $('.loading').hide();
    // }
})

// 关闭物流弹窗
$('body').delegate('.close', 'click', function () {
    $('.alert-box').hide();
    $('.alert-yunshu').hide();
})

// 关闭物流弹窗
$('.alert-box').on('click', function () {
    $('.alert-box').hide();
    $('.alert-yunshu').hide();
})

$('.loading_').on('click', function () {
    $('.alert-box').hide();
    $('.alert-yunshu').hide();
})

// 关闭退款
$('.tuikuan_bg').click(function () {
    $('.tuikuan_bg').hide();
    $('.tuikuan').hide();
})

// 查看物流
function getWuLiu() {
    $.ajax({
        type: 'post',
        url: GlobalHost + '/Api/order/getExpressInfo',
        data: {
            invoice_no: invoice_no
        },
        success: function (res) {
            if(invoice_no == "") {
                $('.loading_').text('暂无物流信息');
                return;
            }
            wuliuStatus = 1
            if (res.code == 200) {
                console.log(res)
                kuaidiName = res.data.cname
                var head = `<div class="top">
                                <p>运输中</p>
                                <div class="yunshu-title">
                                    <div class="left">
                                        <img src="${GlobalHost + orderMSG.goods_list[0].original_img}" alt="">
                                    </div>
                                    <div class="right">
                                        <p>${orderMSG.goods_list[0].goods_name}</p>
                                        <p>${res.data.cname}： ${res.data.no}</p>
                                    </div>
                                </div>
                            </div>            
                            <ul class="list-wrap">`
                var bottom = `</ul>
                                <div class="bottom-tips text-xs">
                                    <img src="./src/img/icon/待收货/hei.png" alt=""> 
                                    <p>本数据由<em>快递公司</em>提供</p>
                                </div>
                                <div class="close">
                                    <img src="./src/img/icon/close.png" alt="">
                                </div>`
                var listbody = '';
                res.data.list.forEach(item => {
                    var time = item.time.split(" ");
                    // console.log(item.time.split(" "))
                    listbody += `<li class="list">
                                <div class="date">
                                    <p class="time">${time[0]}</p>
                                    <p class="day">${time[1]}</p>
                                </div>
                                <div class="info">
                                    <img src="./src/img/icon/待收货/hei.png" alt="" class="info-icon">
                                    <div class="info-con">
                                        <p class="left">${item.content}</p>
                                    </div>
                                </div>
                            </li>`
                })
                $('.alert-yunshu').html(head + listbody + bottom)
            } else {
                $('.alert-yunshu .loading_').text(res.msg)
            }
        }
    })
}


// 退款信息
function getTuiKuan() {
    let head = '';
    let list = '';
    let footer = '';
    let refund_time = formatDateCom(orderMSG.refund_time);  // 退款时间1
    let dorefund_time = formatDateCom(orderMSG.dorefund_time);  // 退款时间1
    let pay_status_str = '';   // 退款状态
    switch(orderMSG.pay_status) {
        case "1": // 卖家确认中
            pay_status_str = "卖家确认中";
            break;
        case "3": // 已退款
            pay_status_str = "已退款";
            break;
        case "4": // 已拒绝
            pay_status_str = "已拒绝";
            break;
        default:
            console.log('判断退款状态出错')
            break;
    }
    console.log(refund_time)

    head = `<div class="top">
                <p>退款信息</p>
                <p>${orderMSG.order_status_desc}</p>
                <p>${refund_time}</p>
            </div>
            <div class="tuik-content">
                <div class="item">
                    <p>退款总金额</p>
                    <p>￥ ${orderMSG.order_amount}</p>
                </div>
                <div class="item">
                    <p>返回银行卡</p>
                    <p>￥ ${orderMSG.order_amount}</p>
                </div>
                <div class="jindu ${orderMSG.pay_status == '3' || orderMSG.pay_status == '4' ? 'active' : ''}">
                    <div class="jd-item ${orderMSG.pay_status != '1' ? 'active' : ''}">
                        <p>等待买家退款</p>
                        <span>${refund_time}</span>
                    </div>
                    <div class="jd-item ${orderMSG.pay_status == '3' ? 'activeSuccess' : ''} ${orderMSG.pay_status == '4' ? 'activeFail' : ''}">
                        <p>${pay_status_str}</p>
                        <span>${dorefund_time}</span>
                    </div>
                </div>
                <p class="tk-title">退款信息</p>
                <ul class="shop-list-wrap">`

    footer = `</ul><div class="footer">
                    <p>退款金额：￥ ${orderMSG.order_amount}</p>
                    <p>申请时间：${refund_time}</p>
                    <p>退款编号：${orderMSG.order_sn}</p>
                </div>
                <div class="btn">
                    <a href="tel:0000-00000000">联系买家</a>
                </div>`
    orderMSG.goods_list.forEach(item => {
        list += `<li>
                    <div class="left">
                        <img src="${GlobalHost + item.original_img}" alt="">
                    </div>
                    <div class="right">
                        <p class="t">${item.goods_name}</p>
                        <p class="price">￥ ${item.goods_price}</p>
                    </div>
                </li>`
    })
    $('.tuikuan').html(head + list + footer).show();
    $('.tuikuan_bg').show();
}


// 支付 页面
function pay(order_id) {
    $.ajax({
        type: 'post',
        url: GlobalHost + '/api/payment/getCode',
        data: {
            order_id: order_id,
            pay_code: 'unionpay'
        },
        success: function (res) {
            console.log(res)
            // 跳转到支付页面
            if (res.code == 200) {
                window.location.href = './payLoad.html?status=pay'
                localStorage.setItem('payMsg', res.data);
            } else {
                console.log('********************************支付报错******************************')
            }
        }
    })
}

// 取消订单
function cancelOrder(order_id) {
    $.ajax({
        type: 'post',
        url: GlobalHost + '/Api/order/cancel_order',
        data: {
            order_id: order_id,
            user_id: user_id
        },
        success: function (res) {
            console.log(res)
            if (res.code == 200) {
                alert(res.msg)
                createOrder(order_id, user_id);
            } else {
                // createAlert($('.alert-tips'), 'alert_tips', res.msg);
                alert(res.msg)
            }
        }
    })
}

// 删除订单
function delOrder(order_id) {
    $.ajax({
        type: 'post',
        url: GlobalHost + '/Api/order/del_order',
        data: {
            user_id: user_id,
            order_id: order_id
        },
        success: function (res) {
            console.log(res)
            if (res.code == 200) {
                alert(res.msg)
                createOrder(order_id, user_id);
            } else {
                alert(res.msg)
            }
        },
        error: function (error) {
            console.log('************************删除订单 报错*****************************')
        }
    })
}

// 确认收货
function shouhuo(order_id) {
    $.ajax({
        type: 'post',
        url: GlobalHost + '/Api/order/receive_order',
        data: {
            user_id: user_id,
            order_id: order_id
        },
        success: function (res) {
            console.log(res)
            if (res.code == 200) {
                alert(res.msg);
                createOrder(order_id, user_id);
            } else {
                alert(res.msg)
            }
        }
    })
}