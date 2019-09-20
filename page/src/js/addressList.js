/**
 * 全局变量
 * @user_id  【用户id，测试用2】
 * @page     【地址列表页码，默认1，从1开始】
 * @del_address_id  【要删除的地址id】
 */



let myUsetInfo = localStorage.getItem('USERINFO');
myUsetInfo = JSON.parse(myUsetInfo);
console.log(myUsetInfo)
let user_id = myUsetInfo.user_id || '';
let page = 1;
let del_address_id = '';

// 请求地址列表
createList (user_id, page);

// 跳转 添加
$('.submit').on('click', function () {
    window.location.href = './addressEdit.html?action=ADD'
})

// 跳转 修改
$('.address-list-wrap').delegate('.edit-btn','click', function () {
    window.location.href = './addressEdit.html?action=EDIT&address_id=' + $(this).attr('data-address_id')
})


// 背景 隐藏弹窗
$('.alert-box').on('click', function () {
    $('.alert-box').fadeToggle('fast');
    $('.alert-del').fadeToggle('fast');
})
// 删除按钮 打开弹窗
$('.address-list-wrap').delegate('.del-btn','click', function () {
    $('.alert-box').fadeToggle('fast');
    $('.alert-del').fadeToggle('fast');
    del_address_id = $(this).attr('data-address_id');
})
// 取消按钮
$('.alert-del .cancel').on('click', function () {
    $('.alert-box').fadeToggle('fast');
    $('.alert-del').fadeToggle('fast');
})
// 删除按钮
$('.alert-del .del').on('click', function () {
    $('.alert-box').fadeToggle('fast');
    $('.alert-del').fadeToggle('fast');
    $.ajax({
        type: 'post',
        url: GlobalHost + '/Api/Address/del_address',
        data: {
            user_id: user_id,
            address_id: del_address_id
        },
        success: function (res) {
            console.log(res)
            createList (user_id, page, 'delete');
        }
    })
})













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
            data.forEach(item => {
                addressList += `<li class="list-item bg-df">
                                    <div class="con">
                                        <div class="left">
                                            <p class="f-x-c">
                                                <b class="mr-df h-df">${item.consignee}</b>
                                                <b class="mr-df h-df">${item.mobile}</b>
                                                <span class="tag-df text-xs" style="display: ${item.is_default == 1 ? 'inline-block' : 'none'}">默认</span>
                                            </p>
                                            <p class="text-df h-df">${item.fulladdress}</p>
                                        </div>
                                        <div class="ctr text-xs">
                                            <div class="default">
                                                <span style="display: ${item.is_default == 0 ? 'inline-block' : 'none'}" class="isDefault"></span>
                                                <span style="display: ${item.is_default == 0 ? 'inline-block' : 'none'}" class="setting">设置默认地址</span>
                                            </div>
                                            <div class="right">
                                                <p data-address_id="${item.address_id}" class="edit-btn">
                                                    <img src="./src/img/icon/写信息.png" alt="">
                                                    <span>编辑</span>
                                                </p>
                                                <p data-address_id="${item.address_id}" class="del-btn">
                                                    <img src="./src/img/icon/删除.png" alt="">
                                                    <span>删除</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
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