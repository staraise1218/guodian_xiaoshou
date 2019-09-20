/**
 * @imgArr          商品图片 用于配送方式页
 * @address         【地址信息】 [fulladdress: 详细地址； mobile：地址电话； consignee：收货人； address_id：地址id]
 * @isChooseAddress 【选择地址标识】 [is: 0 未修改， 1 修改了]
 */

/**订单数据
  * @user_id 	    是 	用户id
  * @address_id 	是 	收货地址id
  * @ID_number 	    是 	身份证号
  * @buy_method 	是 	配送方式 1 到店自提 2 快递送货
  * @pay_points 	是 	使用的积分数
  * @action 	    是 	动作：立即购买： buy_now  购物车结算： cart
  * 
  * @coupon_id 	    否 	【优惠券id】
  * 
  * @goods_id 	    否 	【立即购买时传入 商品id】
  * @goods_num 	    否 	【立即购买时传入 商品数量】
  * @item_id 	    否 	【立即购买时传入 商品规格id】
  * 
  * @consignee 	    否  【到店自提】 	姓名
  * @mobile 	    否 	【到店自提】    手机号
  * 
  * @dosubmit 	    否 	【提交订单时传入1】
  * 
  * @YH             优惠券信息
  * 
  * @reMSG          结算错误的返回
  */

/**【TIPS】
 * action       ==========》    判断 立即购买  购物车结算
 * buy_method   ==========》    判断 到店自提  快递送货
 */


// 用户基本信息
let myUsetInfo = localStorage.getItem('USERINFO');
myUsetInfo = JSON.parse(myUsetInfo);
console.log(myUsetInfo)
let user_id = myUsetInfo.user_id;
let buy_method = 2;
let pay_points = 0;
let action = getParam('action');
let coupon_id = -1;
let goods_id = getParam('goods_id');
let item_id = getParam('item_id') || "";
let goods_num = getParam('goods_num');
let ID_number = localStorage.getItem('ID_number') || '';         // 身份证
let consignee = '';         // 到点自提 -- 姓名
let mobile = '';            // 到店自提 -- 手机号
let address = {};           // address 信息
let imgArr = [];
let YH = localStorage.getItem('YH') // 优惠券信息
YH = JSON.parse(YH);




/**
 * 输入框信息 判断
 */
let userNameStatus = 0;     // 1 正确   联系人姓名
let userNameVal = '';       // 联系人姓名
let userPhoneStatus = 0;    // 1 正确   联系人电话
let userPhoneVal = 0;       // 联系人电话
let shfenCardStatus = 0;    // 1 正确   身份证


let posData = {
    user_id: user_id,
    address_id: address.address_id || '',
    ID_number: ID_number,
    buy_method: buy_method,
    pay_points: pay_points,
    action: action
};

// 
switch(action) {
    case 'buy_now':
        posData.goods_id = goods_id;
        posData.goods_num = goods_num;
        posData.item_id = item_id;
        console.log('************************立即购买************************')
        break;
    case 'cart':
        delete posData.goods_id;
        delete posData.goods_num;
        delete posData.item_id;
        console.log('************************购物车************************');
        break;
    default:
        console.log('action*********************传参出错************************');
        break;
}

/**=======================================================================
 *                      跳转
 * =======================================================================
 */
// 跳转选择地址
$('.address2').on('click', function () {
    // isChooseAddress.is = 0
    // isChooseAddress = JSON.stringify(isChooseAddress);
    // localStorage.setItem('isChooseAddress', isChooseAddress);
    window.location.href = './addressChoose.html';
})

// 跳转选择配送方式
$('.peison_method').on('click', function () {
    localStorage.setItem('buy_method', buy_method);
    window.location.href = './peisongMethod.html?imgArr=' + imgArr;
})

// 跳转地址
$('.toChooseAddress').on('click', function () {
    if(reMSG == "必须传递商品规格") {
        window.history.back();
    } else {
        window.location.href = './addressChoose.html';
    }
})

// 选择优惠券
$('.chooseYH').click(function () {
    if($(this).attr('data-status') == 1) {
        window.location.href = './myCouponChoose.html?action=' + action + '&goods_id=' + goods_id + '&item_id=' + item_id + '&goods_num=' + goods_num;
    }
})
/**=======================================================================
 *                      判断
 * =======================================================================
 */
// 判断优惠券
if(YH.STATUS == '1' || YH.STATUS == '2') {
    $('.chooseYH').text(YH.name);
    coupon_id = YH.id;
    YH.STATUS = 2;
    var str = JSON.stringify(YH)
    localStorage.setItem('YH', str);
    getorderInfo();
}

// 判断地址是否选择了
let isChooseAddress = '';
if(localStorage.getItem('isChooseAddress')) {
    isChooseAddress = localStorage.getItem('isChooseAddress');
    isChooseAddress = JSON.parse(isChooseAddress);
    if(isChooseAddress.is == 1) {
        address = isChooseAddress;
        console.log('address',address)
        createAddress(address.consignee, address.mobile, address.fulladdress);
    }
}

console.log(isChooseAddress)

// 是否输入了身份证号
if(ID_number) {
    $('#shenfenCardVal').text(ID_number);
    posData.ID_number = ID_number;
}
// 判断自提人信息
if(localStorage.getItem('consignee') != null && localStorage.getItem('mobile') != null) {
    $('#username-userphone').text(localStorage.getItem('consignee') + '-' + localStorage.getItem('mobile'));
    posData.consignee = localStorage.getItem('consignee');
    posData.mobile = localStorage.getItem('mobile');
}


/**
 * 判断配送方式
 */

buy_method = Number(localStorage.getItem('buy_method') || 2);
console.log(buy_method)
switch(buy_method) {
    case 1:
        $('.address').css('display','block');
        $('.address2').hide();
        $('.peisong-title').text('自提');
        break;
    case 2:
        $('.address').hide();
        $('.address2').show();
        $('.peisong-title').text('快递送货');
        break;
    default:
        console.log('判断配送方式出错')
        break;
}



/**=======================================================================
 *                      页面加载时执行的函数
 * =======================================================================
 */
getorderInfo(); // 获取订单详细信息


/**=======================================================================
 *                      通过动作执行函数
 * =======================================================================
 */
// 支付
$('#submit').on('click', function () {
    toPay()
})

// $('.alert-info .btn-cancel ').on('click', function () {
//     // $('.alert-info').hide()
//     console.log(123)
//     history.back(-1)
// })




/**=======================================================================
 *                      函数定义
 * =======================================================================
 */
// 获取订单详细信息
function getorderInfo() {
    $.ajax({
        type: 'POST',
        url: GlobalHost + '/Api/cart/cart2',
        data: {
            user_id: user_id,
            goods_id: goods_id,
            action: action,
            item_id: item_id,
            goods_num: goods_num
        },
        success: function (res) {
            console.log(res)
            if(res.code == 200) {
                if(localStorage.getItem('changeAddress')) {
                    address = JSON.parse(localStorage.getItem('changeAddress'))
                    console.log(address)
                    localStorage.removeItem('changeAddress');
                } else {
                    address = res.data.address; // TODO:
                }
                let goodsList = '';
                let goods = []
                switch(action) {
                    case 'cart':
                        goods = res.data.cartList.cartList;
                        goods.forEach((item, index) => {
                            goodsList += `<li class="shop-wrap border-bottom">
                                            <div class="poster">
                                                <img src="${GlobalHost + item.goods.original_img}" alt="">
                                            </div>
                                            <div class="right">
                                                <p class="text-df">${item.goods.goods_name}</p>
                                                <div class="text-xs color_3 tag-wrap">
                                                    <p class="tag">暂无标签</p>
                                                    <p class="num">x${item.goods_num}</p>
                                                </div>
                                                <p class="price">￥${item.goods.shop_price}</p>
                                            </div>
                                        </li>`
                            imgArr[index] = GlobalHost + item.goods.original_img;
                        });
                        break;
                    case 'buy_now':
                        goods = res.data.cartList.cartList;
                        goods.forEach((item, index) => {
                            goodsList += `<li class="shop-wrap border-bottom">
                                            <div class="poster">
                                                <img src="${GlobalHost + item.result.buy_goods.goods.original_img}" alt="">
                                            </div>
                                            <div class="right">
                                                <p class="text-df">${item.result.buy_goods.goods.goods_name}</p>
                                                <div class="text-xs color_3 tag-wrap">
                                                    <p class="tag">暂无标签</p>
                                                    <p class="num">x${item.result.buy_goods.goods_num}</p>
                                                </div>
                                                <p class="price">￥${item.result.buy_goods.goods.shop_price}</p>
                                            </div>
                                        </li>`
                            imgArr[index] = GlobalHost + item.result.buy_goods.goods.original_img
                        });
                        break;
                }
                $('.goodsList-box').html(goodsList);
                $('#total_fee_1').text('￥ 正在计算...');
                $('#total_fee_2').text('￥ 正在计算...');
                console.log(YH.STATUS)
                if(YH.STATUS == '0') {
                    if(res.data.couponList.length > 0) {
                        $('.chooseYH').text('选择优惠券');
                        $('.chooseYH').attr('data-status', '1')
                    } else {
                        $('.chooseYH').text('无可用优惠券');
                    }
                }
                if(res.data.couponList.length > 0) {
                    $('.chooseYH').attr('data-status', '1')
                }
                // $('.address2 .user-name-number').text(address.consignee + '  ' + address.mobile);
                // $('.address2 .user-address-f').text(address.fulladdress);
                console.log(isChooseAddress)
                if(isChooseAddress.is != 1) {
                    createAddress(address.consignee, address.mobile, address.fulladdress)
                }
                imgArr = JSON.stringify(imgArr);
                getPrice(); // 计算价格
            }
        }
    })
}
/**
 * 渲染地址
 * @param {*收货人} userName 
 * @param {*联系电话} phone 
 * @param {*详细地址} fulladdress 
 */
function createAddress(userName, phone, fulladdress) {
    $('.address2 .user-name-number').text(userName + '  ' + phone);
    $('.address2 .user-address-f').text(fulladdress);
}


// 获取价格信息
function getPrice () {
    var priceData = {
        user_id: user_id,                   // 	是 	用户id
        address_id: address.address_id,     // 	是 	收货地址id
        ID_number: ID_number,               // 	是 	身份证号
        // consignee: consignee,               // 	否 	姓名，当配送方式选择“到店自提”时传入
        // mobile: mobile,                     // 	否 	手机号，当配送方式选择“到店自提”时传入
        buy_method: buy_method,             // 	是 	配送方式 1 到店自提 2 快递送货
        coupon_id: coupon_id == -1 ? '' : coupon_id,                      // 	否 	优惠券id
        pay_points: 0,                      //  是 	使用的积分数
        action: action,
        goods_id: goods_id,
        goods_num: goods_num
    }
    if(item_id) {
        priceData.item_id = item_id;
    }
    $.ajax({
        type: 'post',
        url: GlobalHost + '/Api/cart/cart3',
        data: priceData,
        success: function (res) {
            console.log(res)
            let data = res.data;
            setTimeout(() => {
                if(res.code == 200) {
                    // $('.alert-info').show().text(res.msg)
                    // alert(res.msg);
                    $('#total_fee_1').text('￥' + data.order_amount);
                    $('#total_fee_2').text('￥' + data.total_amount);
                    // alert(res.msg);
                }
                if(res.code == 400 || res.code == 500) {
                    $('.alert-info').show() //TODO:
                    $('.addcon').text(res.msg)
                    reMSG = res.msg;
                    // setTimeout(() => {
                    //     $('.alert-info').hide();
                    // }, 1500);
                    // alert(res.msg);
                    $('#total_fee_1').text('￥ 计算失败');
                    $('#total_fee_2').text('￥ 计算失败');
                }
            }, 500)
        }
    })
}
// 提交订单
function toPay() {
    if(!ID_number) {
        // return
    }
    let payData = {
        user_id: user_id,                   // 	是 	用户id
        address_id: address.address_id,     // 	是 	收货地址id
        ID_number: ID_number,               // 	是 	身份证号
        buy_method: buy_method,             // 	是 	配送方式 1 到店自提 2 快递送货
        pay_points: 0,                      //  是 	使用的积分数
        action: action,                     //  是
        goods_id: goods_id,
        goods_num: goods_num,
        dosubmit: 1,
        coupon_id : coupon_id == -1 ? '' : coupon_id
    }
    // consignee: consignee,               // 	否 	姓名，当配送方式选择“到店自提”时传入
    // mobile: mobile,                     // 	否 	手机号，当配送方式选择“到店自提”时传入

    // console.log('buy_method', buy_method)
    // console.log(payData)
    switch(buy_method) {
        case 1 || '1': // 到店自提
            console.log('***********************到店自提****************************');
            posData.consignee = localStorage.getItem('consignee') || consignee;
            posData.mobile = localStorage.getItem('mobile') || mobile;
            console.log(payData)
            if(!posData.consignee) {
                createAlert($('.alert-tips'), 'alert_tips', '请输入姓名');
                return;
            }
            if(!posData.mobile) {
                createAlert($('.alert-tips'), 'alert_tips', '请输手机号');
                return;
            }
            if(!posData.ID_number) {
                createAlert($('.alert-tips'), 'alert_tips', '请输身份证号码');
                return;
            }
            break;
        case 2 || '2': // 快递送货
            console.log('***********************快递送货****************************');
            delete posData.consignee;
            delete posData.mobile;
            if(!payData.address_id) {
                createAlert($('.alert-tips'), 'alert_tips', '请选择地址');
                return;
            }
            if(!posData.ID_number) {
                createAlert($('.alert-tips'), 'alert_tips', '请输身份证号码');
                return;
            }
            break;
        default:
            console.log('***********************传参错误 toPay*****************************');
            return;
    }
    
    // createAlert($('.alert-tips'), 'alert_tips', '提示', )
    $.ajax({
        type: 'post',
        url: GlobalHost + '/Api/cart/cart3',
        data: payData,
        success: function (res) {
            console.log(res)
            pay(res.data.order_id)
        }
    })
}

// 支付页面
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
                alert(res.msg)
                console.log('********************************支付报错******************************')
            }
        }
    })
}

/**================================================================================
 *                      弹窗部分
 * ================================================================================
 */
/**
 * 自提人信息弹窗
 */
// 显示自提联系人信息
$('.address').on('click', function () {
    $('.user-wrapper').css('display', 'block');
})

// 关闭自提联系人信息
$('.user-close').on('click', function () {
    $('.user-wrapper').css('display', 'none');
})

// 判断用户名
$('#user-name').on('input', function () {
    console.log(userNameRgx.test($(this).val()));
    if (userNameRgx.test($(this).val())) {
        userNameStatus = 1;
        userNameVal = $(this).val();
    } else {
        userNameStatus = 0;
    }
    if (userNameStatus == 1 && userPhoneStatus == 1) {
        $('.user-wrapper .submit').addClass('user-btn-active')
    } else {

        $('.user-btn-active').removeClass('user-btn-active')
    }
})

// 判断用户电话
$('#user-phone').on('input', function () {
    console.log(phoneRgx.test($(this).val()));
    if (phoneRgx.test($(this).val())) {
        userPhoneStatus = 1;
        userPhoneVal = $(this).val();
    } else {
        userPhoneStatus = 0;
    }
    if (userNameStatus == 1 && userPhoneStatus == 1) {
        $('.user-wrapper .submit').addClass('user-btn-active')
    } else {
        $('.user-btn-active').removeClass('user-btn-active')
    }
})

// 自提联系人信息 提交
$('#user-submit').on('click', function () {
    if (userNameStatus == 1 && userPhoneStatus == 1) {
        $('#username-userphone').text(userNameVal + '-' + userPhoneVal);
        $('.user-wrapper').css('display', 'none');
        consignee = userNameVal;
        mobile = userPhoneVal;
        posData.consignee = userNameVal;
        posData.mobile = userPhoneVal;
        console.log(posData)
        localStorage.setItem('consignee', userNameVal);
        localStorage.setItem('mobile', userPhoneVal);
    }
})








/**
 * 身份证弹窗
 */
// 显示输入身份证号
$('.shenfen-btn').on('click', function () {
    $('.shenfen-wrap').css('display', 'block');
})

// 关闭输入身份证号
$('.shenfen-close').on('click', function () {
    $('.shenfen-wrap').css('display', 'none');
})

// 判断身份证
$('#shenfenCard').on('input', function () {
    if (shenfenCardRgx.test($(this).val())) {
        shfenCardStatus = 1;
        ID_number = $(this).val();
        $('.shenfen-wrap .submit').addClass('shenfenCard-active');
    } else {
        shfenCardStatus = 0;
        $('.shenfenCard-active').removeClass('shenfenCard-active');
        localStorage.setItem('ID_number', '');
    }
})

// 身份证提交
$('#shenfenCard-submit').on('click', function () {
    if (shfenCardStatus == 1) {
        $('#shenfenCardVal').text(ID_number);
        $('.shenfen-wrap').css('display', 'none');
        posData.ID_number = ID_number;
        localStorage.setItem('ID_number', ID_number);
    }
})