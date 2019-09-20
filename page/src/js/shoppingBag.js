/**定义数据
 * @user_id         【用户id】
 * @goodsList       【购物车商品数据】
 * @cartArr         【购物车所有商品   {id, goods_num，selected}】 购物车id ，商品数量 ，商品选中状态： 1 选中状态 0 未被选中
 * @cart = JSON.scriptfy(cartArr)
 * @count           【选中商品的数量】
 */

/**缓存数据
  * 
  */

/**
 * =================================================
 *          公共变量
 * =================================================
 */
// alert(localStorage.getItem('USERINFO'))
// let myUsetInfo = localStorage.getItem('USERINFO');
// myUsetInfo = JSON.parse(myUsetInfo);
// console.log(myUsetInfo)
let user_id = ''; //myUsetInfo.user_id;
let goodsList = [];
let count = 0;
let loadCOUNT = 0;

// alert('url :' + window.location.href)
var u = navigator.userAgent, 
    app = navigator.appVersion;
var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1; //g
var isIOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端
if (isAndroid) {
    // alert("安卓机！")
    user_id = getParam('user_id')
}
if (isIOS) {
    // alert("苹果机！")
    if(localStorage.getItem('USERINFO') != '') {
        let myUsetInfo = localStorage.getItem('USERINFO');
        myUsetInfo = JSON.parse(myUsetInfo);
        user_id = myUsetInfo.user_id;
    } else {
        user_id = getParam('user_id')
    }
}
// alert('user_id :' + user_id)



// 全选
$('.chooseAll .left-choose').on('click', function (e) {
    // alert('全选')
    if($(this).attr('data-selected') == 0) {
        $(this).find('.choose-icon').prop('src', './src/img/icon/圆1.png');
        $(this).attr('data-selected', '1');
        goodsList.forEach(item => {
            item.selected = 1;
        })
        console.log(goodsList)
        getPrice(); // 计算价格
        $('.srco-item').attr('data-selected', 1);
        $('.srco-item .choose').hide();
        $('.srco-item .choose-active').show();
        // console.log('全选')
    } else if($(this).attr('data-selected') == 1) {
        $(this).find('.choose-icon').prop('src', './src/img/icon/圆.png');
        $(this).attr('data-selected', '0');
        $('.srco-item').attr('data-selected', 0);
        $('.srco-item .choose-active').hide();
        $('.srco-item .choose').show();
        // console.log('取消全选')
        goodsList.forEach(item => {
            item.selected = 0;
        })
        console.log(goodsList)
        getPrice(); // 计算价格
    }
})


/**===========================================================
 *                  触发的执行
 * =======================================================
 */

$('.footer .right').on('click', function () {
    // alert('结算')
    toPay();
    // window.location.href = './jieshuan.html?action=cart';
})
/**==========================================================
 *                  函数执行
 * ==========================================================
 */
getShopCartList(); // 加载购物车列表

/**
 * =====================================================
 *                  函数定义
 * =====================================================
 */
// 加载购物车列表
function getShopCartList () {
    // alert('加载购物车列表')
    $.ajax({
        type: 'POST',
        url: GlobalHost + '/Api/cart/index',
        data: {
            user_id: user_id,
            city_code: 110100
        },
        success: function (res) {
            console.log(res)
            res.data.forEach( item => {
                item.selected = 0;
            })
            goodsList = res.data;
            console.log(goodsList)
            let goodsListStr = '';
            res.data.forEach((item, index)  => {
                goodsListStr += `
                <li class="srco-item good-item" data-selected="${item.selected}" data-id="${item.id}" data-scroll="right">
                    <div class="left">
                        <div class="choose-wrap" >
                            <img class="icon-lg choose" style="display:${item.selected == 0 ? 'block' : 'none'}" src="./src/img/icon/圆.png" alt="">
                            <img class="icon-lg choose-active" style="display:${item.selected == 0 ? 'none' : 'block'}" src="./src/img/icon/圆1.png" alt="">
                        </div>
                        <div class="commity">
                            <img class="poster" src="${GlobalHost + item.goods.original_img}" alt="">
                            <img class="sq"  src="./src/img/icon/sq.png" alt="img" style="display: none">
                            <img class="yd"  src="./src/img/icon/yd.png" alt="img" style="display: none">
                            
                        </div>
                    </div>
                    <div class="right">
                        <p>AUDEMARS PIGUET</p>
                        <p>${item.goods_name}</p>
                        <p class="price">￥ ${item.goods_price}</p>
                    </div>
                    <div class="del" data-id="${item.id}">移除</div>
                </li>`
            });
            if(res.data.length == 0) {
                $('.commodityList').html(`<div class="empty">
                                                <img src="./src/img/icon/empty_shopCart.png" alt="">
                                            </div>`);
            } else {
                $('.commodityList').html(goodsListStr);
            }
            $('.loading-tips').hide();
        }
    })
}

// 获取价格信息
function getPrice () {
    var cart = [];
    console.log(goodsList)
    goodsList.forEach( (item, index) => {
        cart[index] = {
            id: item.id,
            goods_num: item.goods_num,
            selected: item.selected
        }
    })
    cart = JSON.stringify(cart);
    $.ajax({
        type: 'post',
        url: GlobalHost + '/Api/cart/AsyncUpdateCart',
        data: {
            user_id: user_id,
            cart: cart
        },
        success: function (res) {
            console.log(res)
            $('.footer .price').text('￥ 正在计算');
            if(res.code == 200) {
                $('.footer .price').text('￥ ' + res.data.result.total_fee);
                $('.footer .right').text('去结算 （' + res.data.result.goods_num +'）');
                count = res.data.result.goods_num
            }
        }
    })
}

// 结算
function toPay () {
    // alert(user_id)
    if(count == 0) {
        alert('你的购物车中没有商品')
    } else {
        $.ajax({
            type: 'post',
            url: GlobalHost + '/Api/cart/cart2',
            data: {
                user_id: user_id,
                action: 'cart'
            },
            success: function (res) {
                console.log(res)
                // alert(res.code)
                if(res.code == 200) {
                    if(res.data.address.length == 0) {
                        alert('请先填写您的地址')
                    }
                    localStorage.setItem('YH', JSON.stringify({STATUS:0}));
                    window.location.href = './jieshuan.html?action=cart';
                } else {
                    alert(res.msg)
                }
            }
        })
    }
}











/** 
 * ===============================================
 *          左右滑动判断
 * ===============================================
 */

$(".commodityList").delegate('.srco-item',"touchstart", function (e) {
    // console.log($(this).attr('data-scroll'))
    console.log(e)
    // 判断默认行为是否可以被禁用
    if (e.cancelable) {
        // 判断默认行为是否已经被禁用
        if (!e.defaultPrevented) {
            // e.preventDefault();
        }
    }
    startX = e.originalEvent.changedTouches[0].pageX,
    startY = e.originalEvent.changedTouches[0].pageY;
});
// // 商品上的操作
$(".commodityList").delegate('.srco-item',"touchend", function (e) {
    // 判断默认行为是否可以被禁用
    if (e.cancelable) {
        // 判断默认行为是否已经被禁用
        if (!e.defaultPrevented) {
            e.preventDefault();
        }
    }
    moveEndX = e.originalEvent.changedTouches[0].pageX,
    moveEndY = e.originalEvent.changedTouches[0].pageY,
        X = moveEndX - startX,
        Y = moveEndY - startY;
    //左滑
    if (X > 50) {
        console.log(X)
        console.log('右滑');
        if ($(this).attr('data-scroll') == 'left') {
            console.log($(this).attr('data-scroll'))
            $(this).attr('data-scroll', 'right')
                .animate({
                    left: 0
                }, 500);
        }
    }
    //右滑
    else if (X < -50) {
        console.log(X)
        console.log('左滑');
        if ($(this).attr('data-scroll') == 'right') {
            // console.log($(this).attr('data-scroll'))
            $(this).attr('data-scroll', 'left')
                .animate({
                    left: -$('.del').eq(0).width()
                }, 500);
        }
    }
    //下滑
    else if (Y > 0) {
        console.log(Y)
        console.log('下滑');
    }
    //上滑
    else if (Y < 0) {
        console.log(Y)
        console.log('上滑');
    }
    //单击
    else {
        // console.log('单击');
        console.log($(this).attr('data-id'))
        console.log($(this).attr('data-selected'))
        // 选择
        if ($(this).attr('data-scroll') == 'left') {
            console.log($(this).attr('data-scroll'))
            $(this).attr('data-scroll', 'right')
                .animate({
                    left: 0
                }, 500);
        } else {
            // 选中商品
            switch ($(this).attr('data-selected')) {
                case '0':
                    $(this).find('.choose').css('display','none')
                    $(this).find('.choose-active').css('display','block')
                    $(this).attr('data-selected', '1')
                    goodsList[$(this).index()].selected = 1;
                    console.log(goodsList)
                    console.log('选中')

                    // 判断是否都选中了
                    var count = 0;
                    goodsList.forEach(item => {
                        if(item.selected == 0) {
                            count++;
                        }
                    })
                    if(count == 0) {
                        $('.left-choose').find('.choose-icon').prop('src', './src/img/icon/圆1.png')
                        $('.left-choose').attr('data-selected', '1');
                    }
                    getPrice(); // 计算价格
                    break;
                case '1':
                    $(this).find('.choose').css('display','block')
                    $(this).find('.choose-active').css('display','none')
                    $(this).attr('data-selected', '0')
                    console.log($(this).index())
                    goodsList[$(this).index()].selected = 0;
                    
                    $('.left-choose').find('.choose-icon').prop('src', './src/img/icon/圆.png')
                    $('.left-choose').attr('data-selected', '0');
                    console.log('取消')
                    getPrice(); // 计算价格
                    break;
                default:
                    console.log('*************选中商品报错*************')
                    console.log($(this).attr('data-selected'))
                    break;
            }
        }
        if($(e.target).attr('class') == 'del') {
            let cart_id = JSON.stringify($(this).attr('data-id'))
            $.ajax({
                type: 'POST',
                url: GlobalHost + '/Api/cart/delete',
                data: {
                    user_id: user_id,
                    cart_ids: cart_id
                },
                success: function (res) {
                    console.log(res)
                    getShopCartList();
                }
            })
        }
    }
});



/**
 * 猜你喜欢 加载
 * @el      【挂在元素】
 * @user_id 【用户id】
 * @num     【加载数量】
 */
favorite ($('.recommend'), 20, 20);









    //定义的全局变量
    var disY, startY, endY;
    //触摸事件开始时触发
    $('body').on('touchstart', function (e) {
        startY = e.changedTouches[0].pageY;
    });
    //触摸事件移动中时触发
    $('body').on('touchmove', function (e) {
        endY = e.changedTouches[0].pageY;
        disY = endY - startY;
        if (disY > 30) {
            $('.status').css({
                display: 'block',
                height: disY + 'px',
            });
        }
    });
    //触摸事件结束时触发
    $('body').on('touchend', function (e) {
        endY = e.changedTouches[0].pageY;
        disY = endY - startY;
        if (disY > 72) {
            //定义一个定时器，返回下拉到一定的高度
            var timer = setInterval(function () {
                disY -= 13;
                if (disY <= 60) {
                    $('.status').css({
                        height: 52 + 'px',
                    });
                    clearInterval(timer);
                    refresh();
                }
                $('.status').css({
                    height: disY + 'px',
                });
            }, 75);
        }
    });
    //请求刷新数据
    function refresh() {
        getShopCartList(); // 加载购物车列表
    }