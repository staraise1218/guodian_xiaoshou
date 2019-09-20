/**
 * @buy_method      【配送方式 1 到店自提 2 快递送货】
 */
let buy_method = Number(localStorage.getItem('buy_method'));

let myUsetInfo = localStorage.getItem('USERINFO');
myUsetInfo = JSON.parse(myUsetInfo);
console.log(myUsetInfo)
let user_id = myUsetInfo.user_id;


let imgArr = JSON.parse(getParam('imgArr'));




/**
 * 根据加载过来的配送方式渲染
 */
switch(buy_method) {
    case 1:
        $('.choose-wrap .btn-wrap span').removeClass('active');
        $('.choose-wrap .btn-wrap span:last').addClass('active');
        $('.peisong-con').hide();
        $('.ziti-con').show();
        zitidian();
        break;
    case 2:
        $('.choose-wrap .btn-wrap span').removeClass('active');
        $('.choose-wrap .btn-wrap span:first').addClass('active');
        $('.peisong-con').show();
        $('.ziti-con').hide();
        break;
    default:
        console.log('配送方式没有传过来')
        break;
}

/**
 * 渲染顶部图片
 */
let imgStr = '';
imgArr.forEach(item => {
    imgStr += `<img src="${item}" alt="" class="shop-item">`
})
$('.shop-list-wrap').html(imgStr)



/**==============================================================================
 *                      选择部分
 * ==============================================================================
 */

// 选择快递
$('.kuaidi').on('click', function () {
    $('.choose-wrap .btn-wrap span').removeClass('active');
    $(this).addClass('active');
    $('.peisong-con').show();
    $('.ziti-con').hide();
    buy_method = 2;
})

// 选择自提
$('.ziti').on('click', function () {
    $('.choose-wrap .btn-wrap span').removeClass('active');
    $(this).addClass('active');
    $('.peisong-con').hide();
    $('.ziti-con').show();
    zitidian();
    buy_method = 1;
})




// 确定
$('.submit').on('click', function () {
    localStorage.setItem('buy_method', buy_method);
    window.history.back(-1);
})




/**==============================================================================
 *                      函数定义
 * ==============================================================================
 */

function zitidian () {
    $.ajax({
        type: 'post',
        url: GlobalHost + '/api/cart/storeInfo',
        success: function (res) {
            console.log(res)
            if(res.code == 200) {
                res.data.storeInfo = res.data.storeInfo.replace(/[\r\n]/g, "</br>")
                $('.ziti-con .text').html(res.data.storeInfo);
            } else {
                console.log('接口报错了')
            }
        }
    })
}


