/**
 * @pay_status          【1 待审核  3 已退款  4 拒绝退款】
 * @user_id
 * @page
 */

let myUsetInfo = localStorage.getItem('USERINFO');
myUsetInfo = JSON.parse(myUsetInfo);
console.log(myUsetInfo)
let user_id = myUsetInfo.user_id;
let pay_status = getParam('pay_status');
let page = 1;


















createList()

// 加载订单列表
function createList(page, type) {
    $.ajax({
        type: 'post',
        url: GlobalHost + '/Api/order/order_list',
        data: {
            user_id: user_id,
            type: "REFUND",
            page: page
        },
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
                                            <p class="text-df color_3">副标题</p>
                                            <p class="text-df color_3">副标题</p>
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
                                <p class="title">订单金额：￥1231231231</p>
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








$('body').delegate('.to', 'click', function () {
    console.log($(this).attr('data-order_id'))
    window.location.href = './orderDetail.html?order_id=' + $(this).attr('data-order_id') 
})










