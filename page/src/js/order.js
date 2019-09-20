/**
 * @user_id     【用户id】
 * @type        【订单状态类型：不传参默认全部，待付款：WAITPAY；待发货：WAITSEND； 待收货：WAITRECEIVE；已完成：FINISH；已取消：CANCEL】
 * @page 	    【页码，从1开始】
 */

let myUsetInfo = localStorage.getItem('USERINFO');
myUsetInfo = JSON.parse(myUsetInfo);
console.log(myUsetInfo)
let user_id = myUsetInfo.user_id;
let page = 1;
let type = getParam('type');






console.log(type)
var len = ''
switch (type) {
    case 'WAITPAY':     // 代付款
        len = $('.tabBar li').length;
        $('.tabBar li').removeClass('active');
        $('.tabBar .WAITPAY_').addClass('active');
        break;
    case 'WAITSEND':    // 待发货
        len = $('.tabBar li').length;
        $('.tabBar li').removeClass('active');
        $('.tabBar .WAITSEND_').addClass('active');
        break;
    case 'WAITRECEIVE': // 待收货
        len = $('.tabBar li').length;
        $('.tabBar li').removeClass('active');
        $('.tabBar .WAITRECEIVE_').addClass('active');
        break;
    case 'FINISH':      // 已完成
        len = $('.tabBar li').length;
        $('.tabBar li').removeClass('active');
        $('.tabBar .FINISH_').addClass('active');
        break;
    case 'CANCEL':      // 已完成
        len = $('.tabBar li').length;
        $('.tabBar li').removeClass('active');
        $('.tabBar .CANCEL_').addClass('active');
        break;
    case 'ALL':         // 全部
        len = $('.tabBar li').length;
        $('.tabBar li').removeClass('active');
        $('.tabBar .ALL_').addClass('active');
        break;
    default:
        len = $('.tabBar li').length;
        $('.tabBar li').removeClass('active');
        $('.tabBar .ALL_').addClass('active');
        break;
}


// 关闭物流弹窗
$('body').delegate('.close', 'click', function () {
    $('.alert-box').hide();
    $('.alert-yunshu').hide();
})
/**
 * 初始化函数执行
 */
createList(user_id, page, type); // 加载列表

// 按钮操作
$('.item-wrap').delegate('.btn-wrap span', 'click', function (event) {
    console.log($(this))
    event.stopPropagation();    //  阻止事件冒泡
    console.log($(this).attr('data-action'))
    console.log($(this).attr('data-orderid'))
    switch($(this).attr('data-action')) {
        case 'pay':         // 去付款
            pay($(this).attr('data-orderid'));
            break;
        case 'cancel':      // 取消
            cancelOrder($(this).attr('data-orderid'));
            break;
        case 'del':         // 删除
            delOrder($(this).attr('data-orderid'));
            break;
        case 'shouhuo':     // 确认收货
            shouhuo($(this).attr('data-orderid'));
            break;
        case 'see':         // 查看物流
            $('.alert-box').show();
            $('.tips_loading').show();
            $('.alert-yunshu').show();
            getWuLiu();

            break;
        default:
            console.log('**************************传参出错*************************');
            break;
    }
})


// 跳转详情
$('.content').delegate('.to', 'click', function () {
    console.log($(this).attr('data-order_id'))
    window.location.href = './orderDetail.html?order_id=' + $(this).attr('data-order_id')
})



/**
 * Tab 切换
 */
$('.tabBar li').on('click', function () {
    type = $(this).attr('data-type');
    type == 'ALL' ? '' : type;
    $('.tabBar li').removeClass('active');
    $(this).addClass('active')
    $('.content .item-wrap').css('display', 'none');
    console.log($(this).attr('data-type'))
    createList(user_id, page, type); // 加载列表
    for (var i = 0; i < $('.content .item-wrap').length; i++) {
        if ($('.content .item-wrap').eq(i).attr('data-type') == $(this).attr('data-type')) {
            $('.content .item-wrap').eq(i).css('display', 'block')
        }
    }
})













/**=================================================================================
 *                  定义函数
 * =================================================================================
 */
// 加载订单列表
function createList(user_id, page, type) {
    var posData = {
        user_id: user_id,
        page: page,
        type: type
    }
    $.ajax({
        type: 'post',
        url: GlobalHost + '/Api/order/order_list',
        data: posData,
        success: function (res) {
            console.log(res)
            let list = '';
            res.data.forEach(item => {
                var shopList = '';
                // <img src="./src/img/1.png" alt="">
                item.goods_list.forEach(el => {
                    shopList += `<li>
                                    <div class="shop-wrap border-bottom">
                                        <div class="poster">
                                            <img src="${GlobalHost + el.original_img}" alt="">
                                        </div>
                                        <div class="right">
                                            <p class="text-df">${el.goods_name}</p>
                                            <div class="text-xs color_3 tag-wrap">
                                                <p class="tag">${el.spec_key_name}</p>
                                                <p class="num">x${el.goods_num}</p>
                                            </div>
                                            <p class="price">￥${el.member_goods_price}</p>
                                        </div>
                                    </div>
                                </li>`
                })
                shopList = '<ul>' + shopList + '</ul>'
                list += `<li class="to" data-order_id='${item.order_id}'>
                            <p class="btc">
                                <span>订单号：${item.order_sn}</span>
                                <span class="status">${item.order_status_desc}</span>
                            </p>
                            ${shopList}
                            <div class="ctr">
                                <p class="title">订单金额：￥${item.total_amount}</p>
                                <div class="btn-wrap">
                                    <span data-action="pay" data-orderid="${item.order_id}" style="display: ${item.pay_btn == 1 ? 'inline-block' : 'none'}" class="btn">去付款</span>
                                    <span data-action="cancel" data-orderid="${item.order_id}" style="display: ${item.cancel_btn == 1 ? 'inline-block' : 'none'}" class="btn-cancel">取消</span>
                                    <span data-action="del" data-orderid="${item.order_id}" style="display: ${item.del_btn == 1 ? 'inline-block' : 'none'}" class="btn-cancel">删除</span>
                                    <span data-action="shouhuo" data-orderid="${item.order_id}" style="display: ${item.receive_btn == 1 ? 'inline-block' : 'none'}" class="btn-cancel">确认收货</span>
                                    <span data-action="see" data-orderid="${item.order_id}" style="display: ${item.shipping_btn == 1 ? 'inline-block' : 'none'}" class="btn wuliu">查看物流</span>
                                </div>
                            </div>
                        </li>`
            });
            switch (type) {
                case '':
                    $('.ALL').html(list).show();
                    break;
                case 'WAITPAY':
                    $('.WAITPAY').html(list).show();
                    break;
                case 'WAITRECEIVE':
                    $('.WAITRECEIVE').html(list).show();
                    break;
                case 'FINISH':
                    $('.FINISH').html(list).show();
                    break;
                case 'CANCEL':
                    $('.CANCEL').html(list).show();
                    break;
                default:
                    $('.ALL').html(list).show();
                    break;
            }
        }
    })
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
            if(res.code == 200) {
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
            if(res.code == 200) {
                // createAlert($('.alert-tips'), 'alert_tips', res.msg);
                createList(user_id, page, type)
                alert(res.msg)
                // createList(user_id, page, type)
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
            if(res.code == 200) {
                createList(user_id, page, type)
                alert(res.msg)
                // createList(user_id, page, type)
                // createList(user_id, page, type);
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
            if(res.code == 200) {
                createList(user_id, page, type)
                alert(res.msg);
                // createList(user_id, page, type)
                // createList(user_id, page, type);
            } else {
                alert(res.msg)
            }
        }
    })
}


// 订单退款
function shouhuo(order_id) {
    $.ajax({
        type: 'post',
        url: GlobalHost + '/api/order/return_goods',
        data: {
            user_id: user_id,
            order_id: order_id
        },
        success: function (res) {
            console.log(res)
            if(res.code == 200) {
                createList(user_id, page, type)
                alert(res.msg);
                // createList(user_id, page, type)
                // createList(user_id, page, type);
            } else {
                alert(res.msg)
            }
        }
    })
}


// 查看物流
function getWuLiu() {
    $.ajax({
        type: 'post',
        url: GlobalHost + '/Api/order/getExpressInfo',
        data: {
            invoice_no: '3711389943985'
        },
        success: function (res) {
            console.log(res)
            var head = `<div class="top">
                            <p>运输中</p>
                            <div class="yunshu-title">
                                <div class="left">
                                    <img src="./src/img/1.png" alt="">
                                </div>
                                <div class="right">
                                    <p>商品标题</p>
                                    <p>快递信息</p>
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
                var time=item.time.split(" ");
                console.log(item.time.split(" "))
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
            $('.alert-yunshu').html(head + listbody + bottom);
            $('.tips_loading').hide();
        }
    })
}



/**
 * 猜你喜欢
 * @el      【挂在元素】
 * @user_id 【用户id】
 * @num     【加载数量】
 */
favorite ($('.recommend'), user_id, 20);

