/**
 * @type 0 未使用； 1 已使用； 2 已失效
 */

let myUsetInfo = localStorage.getItem('USERINFO');
myUsetInfo = JSON.parse(myUsetInfo);
console.log(myUsetInfo)
let user_id = myUsetInfo.user_id;
let type = Number(getParam('type'));
let page = 1;

// 领券
$('#lingquan').click(function () {
    window.location.href = 'myCouponLingqu.html'
})

getLoading (page)


// 加载页面时判断 type
switch (type) {
    case 0:
        $('.weishiyong').show();
        $('.nav .nav-item').removeClass('active');
        $('.nav .nav-item:eq(0)').addClass('active');
        break;
    case 1:
        $('.yishiyong').show();
        $('.nav .nav-item').removeClass('active');
        $('.nav .nav-item:eq(1)').addClass('active');
        break;
    case 2:
        $('.yishixiao').show();
        $('.nav .nav-item').removeClass('active');
        $('.nav .nav-item:eq(2)').addClass('active');
        break;
}

// 点击时判断 type
$('.nav .nav-item').on('click', function () {
    console.log($(this).index())
    switch ($(this).index()) {
        case 0:
            type = 0;
            getLoading(page);
            $('.weishiyong').show();
            $('.yishiyong').hide();
            $('.yishixiao').hide();
            $('.nav .nav-item').removeClass('active');
            $('.nav .nav-item:eq(0)').addClass('active');
            break;
        case 1:
            type = 1;
            getLoading(page);
            $('.yishiyong').show();
            $('.weishiyong').hide();
            $('.yishixiao').hide();
            $('.nav .nav-item').removeClass('active');
            $('.nav .nav-item:eq(1)').addClass('active');
            break;
        case 2:
            type = 2;
            getLoading(page);
            $('.yishixiao').show();
            $('.weishiyong').hide();
            $('.yishiyong').hide();
            $('.nav .nav-item').removeClass('active');
            $('.nav .nav-item:eq(2)').addClass('active');
            break;
    }
})




// 获取优惠券数据
function getLoading (page) {
    $.ajax({
        type: 'post',
        url: GlobalHost + '/Api/user/coupon',
        data: {
            user_id: user_id,
            type: type,
            page: page
        },
        success: function(res) {
            console.log(res)
            let list ='';
            switch(type) {
                case 0:
                    if(res.data.length == 0) {
                        list = `<div class="empty">
                                    <img src="./src/img/icon/tips_empty.png" alt="">
                                </div>`
                    } else {
                        res.data.forEach(item => {
                            var startTime = formatDateCom(item.use_start_time);
                            var useTime = formatDateCom(item.use_time);
                            var endTime = formatDateCom(item.use_end_time);
                        list += `<li>
                                    <div>
                                        <p>
                                            <i>￥</i><b>${item.money}</b>
                                        </p>
                                        <p><span>${item.use_scope}</span></p>
                                    </div>
                                    <div>
                                        ${endTime}
                                    </div>
                                </li>`
                        })
                    }
                    $('.weishiyong').html(list);
                    break;
                case 1:
                    if(res.data.length == 0) {
                        list = `<div class="empty">
                                    <img src="./src/img/icon/tips_empty.png" alt="">
                                </div>`
                    } else {
                        res.data.forEach(item => {
                            var startTime = formatDateCom(item.use_start_time);
                            var useTime = formatDateCom(item.use_time);
                            var endTime = formatDateCom(item.use_end_time);
                        list += `<li>
                                    <div>
                                        <p>
                                            <i>￥</i><b>${item.money}</b>
                                        </p>
                                        <p><span>${item.use_scope}</span></p>
                                    </div>
                                    <div>
                                        ${useTime}
                                    </div>
                                </li>`
                        })
                    }
                    $('.yishiyong').html(list);
                    break;
                case 2:
                    if(res.data.length == 0) {
                        list = `<div class="empty">
                                    <img src="./src/img/icon/tips_empty.png" alt="">
                                </div>`
                    } else {
                        res.data.forEach(item => {
                            var startTime = formatDateCom(item.use_start_time);
                            var useTime = formatDateCom(item.use_time);
                            var endTime = formatDateCom(item.use_end_time);
                        list += `<li>
                                    <div>
                                        <p>
                                            <i>￥</i><b>${item.money}</b>
                                        </p>
                                        <p><span>${item.use_scope}</span></p>
                                    </div>
                                    <div>
                                        ${endTime}
                                    </div>
                                </li>`
                        })
                    }
                    $('.yishixiao').html(list);
                    break;
            }
        }
    })
}



