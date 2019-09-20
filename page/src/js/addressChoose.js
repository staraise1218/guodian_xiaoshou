/**
 * 全局变量
 * @user_id  【用户id，测试用2】
 * @page     【地址列表页码，默认1，从1开始】
 * @isChooseAddress 【是否选择了地址】
 * @addressArr     【地址保存】
 */

let myUsetInfo = localStorage.getItem('USERINFO');
myUsetInfo = JSON.parse(myUsetInfo);
console.log(myUsetInfo)
let user_id = myUsetInfo.user_id || '';
let page = 1;
let isChooseAddress = {
    is: 0,              // 0 未选择， 1 选择地址了
    address_id: '',     // 地址id
    fulladdress: '',    // 地址全名
    consignee: '',      // 收货人
    mobile: ''          // 收货人电话
}

let addressArr = [];

if(localStorage.getItem('isChooseAddress')) {
    isChooseAddress = JSON.parse(localStorage.getItem('isChooseAddress'));
    console.log(isChooseAddress)
}

/**=================================================
 *              初始化加载函数
 * =================================================
 */
// 请求地址列表
createList (user_id, page);




/**=================================================
 *              跳转
 * =================================================
 */
// 跳转 添加
$('.submit').on('click', function () {
    window.location.href = './addressEdit.html?action=ADD'
})

// 跳转 修改
$('.address-list-wrap').delegate('.edit','click', function () {
    window.location.href = './addressEdit.html?action=EDIT&address_id=' + $(this).attr('data-address_id')
})

// 选择地址
$('.address-list-wrap').delegate('.list-item', 'click', function () {
    // isChooseAddress.is = 1;
    // isChooseAddress.address_id = $(this).attr('data-address_id');
    // isChooseAddress.fulladdress = $(this).attr('data-fulladdress');
    // isChooseAddress.consignee = $(this).attr('data-consignee');
    // isChooseAddress.mobile = $(this).attr('data-mobile');
    // isChooseAddress = JSON.stringify(isChooseAddress)
    // localStorage.setItem('isChooseAddress', isChooseAddress);
    var changeAddress = {};
    addressArr.forEach(item => {
        if($(this).attr('data-address_id') == item.address_id) {
            changeAddress = JSON.stringify(item);
            console.log(changeAddress)
            localStorage.setItem('changeAddress', changeAddress)
        }
    })
    
    window.history.back(-1);
})






/**=================================================
 *              定义函数
 * =================================================
 */
/**
 * @methods {加载地址列表}
 */
function createList (user_id, page, status){
    $.ajax({
        type: 'post',
        url: GlobalHost+ '/Api/Address/address_list',
        data: {
            user_id: user_id,
            page: page
        },
        success: function(res) {
            console.log(res)
            let data = res.data;
            let addressList = '';
            addressArr = data;
            data.forEach(item => {
                addressList += 
                                `<li class="list-item bg-df" 
                                    data-address_id="${item.address_id}" 
                                    data-fulladdress="${item.fulladdress}" 
                                    data-consignee="${item.consignee}" 
                                    data-mobile="${item.mobile}">
                                    <div class="con">
                                        <div class="left">
                                            <p class="f-x-c">
                                                <b class="mr-df h-df">${item.consignee}</b>
                                                <b class="mr-df h-df">${item.mobile}</b>
                                                <span class="tag-df text-xs" style="display: ${item.is_default == 1 ? 'inline-block' : 'none'}">默认</span>
                                            </p>
                                            <p class="text-df h-df">${item.fulladdress}</p>
                                        </div>
                                        <div class="edit" data-address_id="${item.address_id}">
                                            <img src="./src/img/icon/写信息.png" class="icon-edit" alt="">
                                        </div>
                                    </div>
                                    <img src="./src/img/icon/line.png" alt="">
                                </li>`
            });
            if(status == 'delete') {
                $('.address-list-wrap').html(addressList);
            } else {
                $('.address-list-wrap').append(addressList);
            }
        }
    })
}