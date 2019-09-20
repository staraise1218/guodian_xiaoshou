/**
 * API 公共
 */
var GlobalHost = 'http://www.guodianjm.com'
// var GlobalHost = 'http://guodian.staraise.com.cn'


/**
 * 正则
 */
// 姓名
let userNameRgx = /^[a-zA-Z0-9_\u4e00-\u9fa5]+$/;
// 手机号
let phoneRgx = /^((13[0-9])|(14[0-9])|(15[0-9])|(17[0-9])|(18[0-9]))\d{8}$/;
// 身份证
let shenfenCardRgx = /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/;

/**
 * 区分Android ios
 */
var u = navigator.userAgent, 
app = navigator.appVersion;
var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1; //g
var isIOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端
if (isAndroid) {
    console.log("安卓机！")
}
if (isIOS) {
    console.log("苹果机！")
}



/** 
 * 获取指定的URL参数值 
 * 参数：paramName URL参数 
 * 调用方法:getParam("name") 
 */
function getParam(paramName) {
    paramValue = "", isFound = !1;
    if (this.location.search.indexOf("?") == 0 && this.location.search.indexOf("=") > 1) {
        arrSource = unescape(this.location.search).substring(1, this.location.search.length).split("&"), i = 0;
        while (i < arrSource.length && !isFound) arrSource[i].indexOf("=") > 0 && arrSource[i].split("=")[0].toLowerCase() == paramName.toLowerCase() && (paramValue = arrSource[i].split("=")[1], isFound = !0), i++
    }
    return paramValue == "" && (paramValue = null), paramValue
}






/**
 * 回退 1
 */
// $('.back').on('click', function () {
//     window.history.back(-1);
// })
if (isAndroid) {
    $('.back').on('click', function () {
        window.android.goBack();
    //     console.log(window.history.__proto__.constructor)
    //     if(window.android.goBack() != 'undefined') {
    //     } else {
    //         // window.history.back(-1);
    //     }
    })
} else {
    $('.back').on('click', function () {
        window.history.back(-1);
    })
}

/**
 * 时间戳转时间
 */
function formatDate(date) {
    if (date.length == 10) {
        date = date * 1000
    }
    date = Number(date)
    date = new Date(date);
    var y = date.getFullYear();
    var m = date.getMonth() + 1;
    m = m < 10 ? '0' + m : m;
    var d = date.getDate();
    d = d < 10 ? ('0' + d) : d;
    return y + '-' + m + '-' + d;
}
function formatDateCom(time) {
    time*= 1000
    time = Number(time)
    var date = new Date(time);
    Y = date.getFullYear() + '-';
    M = (date.getMonth()+1 < 10 ? '0'+(date.getMonth()+1) : date.getMonth()+1) + '-';
    D = date.getDate() + ' ';
    h = date.getHours() + ':';
    m = date.getMinutes() + ':';
    s = date.getSeconds(); 
    console.log(Y+M+D+h+m+s);
    return Y+M+D+h+m+s
}

/**倒计时
 * @param {*END时间戳} timestamp 
 * 调用 getCountDown(1451556000)
 */
function getCountDown(timestamp, callback){
    timestamp*= 1000
    timestamp = Number(timestamp)
    var countDownTime = '';
    setInterval(function(){
        var nowTime = new Date();
        var endTime = new Date(timestamp * 1000);

        var t = endTime.getTime() - nowTime.getTime();
        var hour=Math.floor(t/1000/60/60%24);
           var min=Math.floor(t/1000/60%60);
           var sec=Math.floor(t/1000%60);

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
        console.log(hour + "小时" + min + "分" + sec + "秒")
        return countDownTime
    },1000);
}



/**
 * 删除数组中的某一位
 */
function remove(arr, value) {
    var i = arr.length;
    while (i--) {
        if (arr[i] === value) {
            return i;
        }
    }
    return false;
}


/**
 * 猜你喜欢
 * @el      【挂在元素】
 * @user_id 【用户id】
 * @num     【加载数量】
 */
function favorite(el, user_id, num) {
    $.ajax({
        type: 'POST',
        url: GlobalHost + '/Api/goods/recommendgoodslist',
        data: {
            user_id: user_id,
            num: num
        },
        success: function (res) {
            let recommendStr = '';
            console.log(res.data)
            $.each(res.data, function (index, item) {
                recommendStr += `<li class="good-item" data-goodid="${item.goods_id}">
                                    <div class="poster">
                                        <img src="${ GlobalHost + item.original_img }" class="com" />
                                        <img class="yd"  src="./src/img/icon/yd.png" alt="img" style="display: ${item.reserved == 1 ? 'block' : 'none'}">
                                        <img class="sq"  src="./src/img/icon/sq.png" alt="img" style="display: ${item.store_count == 0 ? 'block' : 'none'}">
                                    </div>
                                    <p>${item.goods_name}</p>
                                    <p class="price">价格：￥${item.shop_price}</p>
                                    <p class="del">官方公价：￥1,238,300</p>
                                </li>`
            });
            el.html(recommendStr)
        }
    })
}

$('.recommend').delegate('.good-item', 'click', function () {
    console.log($(this).attr('data-goodid'))
    window.location.href = 'commodity.html?goods_id=' + $(this).attr('data-goodid')
})





/**
 * 输入弹窗
 * @alert_name_phone    自提点联系人信息
 * @alert_IDcard        收货人身份证
 * @alert_tips          提示信息 【info 想要提示的信息】
 * @alert_name          输入姓名    TODO:
 * @alert_phone         输入手机号  TODO:
 * @alert_address       输入详细地址 TODO:
 */

function createAlert(el, str, info) {
    console.log('************************createAlert*********************')
    if (!info) {
        info = '未知错误'
    }
    let alert_name_phone = `<div class="alert-wrapper user-wrapper" style="display: block">
                                <div class="alert-content">
                                    <div class="be-center-2">
                                        <p class="w-df">自提点联系人信息</p>
                                        <img class="close-icon user-close" src="./src/img/icon/x.png" alt="">
                                    </div>
                                    <input id="user-name" type="text" placeholder="请添加联系人姓名"> 
                                    <input id="user-phone" type="text" placeholder="请添加联系人电话"> 
                                    <!-- <input type="button" value="确定" class="submit user-btn-active"> -->
                                    <input id="user-submit" type="button" value="确定" class="submit">
                                </div>
                            </div>`;

    let alert_IDcard = `<div class="alert-wrapper shenfen-wrap" style="display: block">
                            <div class="alert-content">
                                <div class="be-center-2">
                                    <p class="w-df">输入身份证号</p>
                                    <img class="close-icon shenfen-close" src="./src/img/icon/x.png" alt="">
                                </div>
                                <input id="shenfenCard" type="text" placeholder="请添加收货人身份证号">
                                <input id="shenfenCard-submit" type="button" value="确定" class="submit">
                                <!--  shenfenCard-active -->
                            </div>
                        </div>`;
    let alert_tips = `<div class="shoTost text-xs" style="display: block">${info}</div>`;
    let alert_name = ``;


    switch (str) {
        case 'alert_name_phone':
            el.html(alert_name_phone);
            break;
        case 'alert_IDcard':
            el.html(alert_IDcard);
            break;
        case 'alert_tips':
            el.html(alert_tips);
            el.show();
            setTimeout(() => {
                console.log(098)
                el.fadeOut()
            }, 500);
            break;
        default:
            console.log('***输入弹窗**传参不正确');
            break;
    }
}














$('body').delegate('#user-name-only', 'input', function () {
    console.log($(this).val())
})














/**
 * 复制到剪贴板
 * @param {*复制的内容} text 
 */
function copyToClipboard(text, el) {
    if (text.indexOf('-') !== -1) {
        let arr = text.split('-');
        text = arr[0] + arr[1];
    }
    var textArea = document.createElement("textarea");
    textArea.style.position = 'fixed';
    textArea.style.top = '0';
    textArea.style.left = '0';
    textArea.style.width = '2em';
    textArea.style.height = '2em';
    textArea.style.padding = '0';
    textArea.style.border = 'none';
    textArea.style.outline = 'none';
    textArea.style.boxShadow = 'none';
    textArea.style.background = 'transparent';
    textArea.value = text;
    document.body.appendChild(textArea);
    textArea.select();

    try {
        var successful = document.execCommand('copy');
        var msg = successful ? '成功复制到剪贴板' : '该浏览器不支持点击复制到剪贴板';
        alert(msg);
        createAlert($('.alert-tips'), 'alert_tips', msg);
        // return msg
    } catch (err) {
        alert('该浏览器不支持点击复制到剪贴板');
        createAlert($('.alert-tips'), 'alert_tips', '该浏览器不支持点击复制到剪贴板');
        // return msg
    }

    document.body.removeChild(textArea);
}





