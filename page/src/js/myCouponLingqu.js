let page = 1;

let myUsetInfo = localStorage.getItem('USERINFO');
myUsetInfo = JSON.parse(myUsetInfo);
console.log(myUsetInfo)
let user_id = myUsetInfo.user_id;






getList(page);





















// 获取优惠券列表
function getList(page) {
    $.ajax({
        type: 'POST',
        url: GlobalHost + '/Api/user/couponlist',
        data: {
            user_id: user_id,
            page: page
        },
        success: function (res) {
            console.log(res)
            let list = '';
            let start = '';
            let end = '';
            res.data.forEach(item => {
                start = formatDateCom(item.use_start_time);
                end = formatDateCom(item.use_end_time);
                list += `<li data-id="${item.id}">
                            <div>
                                <p>
                                    <i>￥</i><b>${item.money}</b>
                                </p>
                                <p><span>${item.name}</span></p>
                            </div>
                            <div>
                                ${start} ${end}
                            </div>
                        </li>`
            })
            $('.yishixiao').html(list)
        }
    })
}





// 点击
$('.yishixiao').delegate('li', 'click', function () {
    let id = $(this).attr('data-id');
    lingqu(id);
})










function lingqu(id) {
    $.ajax({
        type: 'POST',
        url: GlobalHost + '/Api/user/getCoupon',
        data: {
            user_id: user_id,
            coupon_id: id
        },
        success: function (res) {
            console.log(res)
            $('.lingqu_bg').show();
            $('.lingqu .title').text('温馨提示');
            $('.lingqu p').text(res.msg);
            $('.lingqu').show()
        }
    })
}





$('.lingqu .btn').click(function () {
    $('.lingqu_bg').hide();
    $('.lingqu').hide()
})