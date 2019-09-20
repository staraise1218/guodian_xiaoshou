/**
 * 全局变量
 * @action      【动作，添加地址ADD，修改地址EDIT】
 * @user_id     【用户id】
 * @address_id  【地址id】
 * @consignee 	【收货人】
 * @mobile 	    【联系电话】
 * @province 	【省】  {name, id, index}
 * @city 	    【市】  {name, id, index}
 * @district 	【区】  {name, id, index}
 * @address 	【详细地址】
 * @is_default 	【是否默认 0 否 1 是】
 * @getJson     【城市数据】
 * @lactChoose  【最后选择的是什么】 [province,city,district]
 * @changeStatus 【输入框状态】 [changeNume: '修改姓名'  ； changNumber: '修改手机号'     ; changeAddress: '修改详细地址']
 */
let myUsetInfo = localStorage.getItem('USERINFO');
myUsetInfo = JSON.parse(myUsetInfo);
console.log(myUsetInfo)
let user_id = myUsetInfo.user_id || '';
let action = getParam('action');
let address_id = getParam('address_id');
let consignee = '';
let mobile = '';
let province = {
    id: '',
    name: '未选择省分',
    index: 0
}
let city = {
    id: '',
    name: '未选择城市',
    index: 0
}
let district = {
    id: '',
    name: '未选择区域',
    index: 0
}
let address = '';
let is_default = 0;
let lactChoose = '';
let changeStatus = '';






/**
 * 选择地址
 */
// 弹窗
$('#address-alert').on('click', function () {
    $('.alert-box').css('display','block');
    $('.alert-address').slideToggle();
})

// 关闭弹窗
$('.alert-box').on('click', function () {
    $('.alert-box').css('display','none');
    $('.alert-address').slideToggle();
    createCity();
})

// 关闭弹窗
$('.alert-address .tips-top img').on('click', function () {
    $('.alert-box').css('display','none');
    $('.alert-address').slideToggle();
    // createCity();
})
// 确定地址
$('.alert-address .tips-top i').on('click', function () {
    $('.alert-box').css('display','none');
    $('.alert-address').slideToggle();
    createCity();
})

// user-close
$('body').delegate('.user-close', 'click', function () {
    $('.user-wrapper').hide();
})


function createCity() {
    switch (lactChoose) {
        case 'province':
            $('.address-con_').text(province.name);
            break;
        case 'city':
            $('.address-con_').text(province.name + ' ' + city.name);
            break;
        case 'district':
            $('.address-con_').text(province.name + ' ' + city.name + ' ' + district.name);
            break;
        default:
            $('.address-con_').text('请选择地址');
            break;
    }
}


/**
 * 删除地址
 */


// 背景 隐藏弹窗
$('.alert-box-del').on('click', function () {
    $('.alert-box-del').fadeToggle('fast');
    $('.alert-del').fadeToggle('fast');
})
// 删除按钮 打开弹窗
$('.del-btn').on('click', function () {
    $('.alert-box-del').fadeToggle('fast');
    $('.alert-del').fadeToggle('fast');
})
// 取消按钮
$('.alert-del .cancel').on('click', function () {
    $('.alert-box-del').fadeToggle('fast');
    $('.alert-del').fadeToggle('fast');
})
// 删除按钮
$('.alert-del .del').on('click', function () {
    $('.alert-box-del').fadeToggle('fast');
    $('.alert-del').fadeToggle('fast');
})




/**
 * 获取城市接口
 */
$.ajax({
    type: 'post',
    url: GlobalHost + '/api/region/getJson',
    success: function(res) {
        console.log(res)
        let data = res.data;
        getJson = data;
        let provinceList = '';
        // 省
        data.forEach(province => {
            provinceList += `<li data-id="${province.id}">${province.name}</li>`;
        });
        $('.province').html(provinceList);
        $('.alert-address .choose').html(`<li class="active">${province.name}</li>
                                            <li>${city.name}</li>
                                            <li>${district.name}</li>`)
        $('.loading').hide()
    }
})

/**选择省份
 * 渲染城市
 * 返回省份
 */
$('.province').delegate('li','click', function () {
    $('.province li').removeClass('active');
    $(this).addClass('active');
    province.name = $(this).text();
    province.id = $(this).attr('data-id');
    city.name = '未选择城市';
    city.id = '';
    district.name = '未选择区域';
    district.id = '';

    $('.alert-address .choose li').removeClass('active');
    $('.alert-address .choose li').eq(0).addClass('active');
    $('.alert-address .choose li').eq(0).text(province.name);
    $('.alert-address .choose li').eq(1).text(city.name);
    $('.alert-address .choose li').eq(2).text(district.name);
    province.index = $(this).index();
    lactChoose = 'province';
    // 渲染城市
    let cityList = '';
    console.log(province)
    getJson[province.index].sub.forEach(city => {
        cityList += `<li data-id="${city.id}">${city.name}</li>`
    })
    $('.alert-address .city').html(cityList);
    $('.alert-address .district').html('');
    console.log(province)
})


/**选择城市
 * 渲染区域
 * 返回城市
 */
$('.city').delegate('li','click', function () {
    $('.city li').removeClass('active');
    $(this).addClass('active');
    city.name = $(this).text();
    city.id = $(this).attr('data-id');
    district.name = '未选择区域';
    district.id = '';
    $('.alert-address .choose li').removeClass('active');
    $('.alert-address .choose li').eq(1).addClass('active');
    $('.alert-address .choose li').eq(1).text(city.name);
    $('.alert-address .choose li').eq(2).text(district.name);
    city.index = $(this).index();
    lactChoose = 'city';
    // 渲染区域
    let districtList = '';
    getJson[province.index].sub[city.index].sub.forEach(district => {
        districtList += `<li data-id="${district.id}">${district.name}</li>`
    })
    $('.alert-address .district').html(districtList);
})

/**
 * 选择区域
 */
$('.district').delegate('li', 'click', function () {
    $('.district li').removeClass('active');
    $(this).addClass('active');
    district.name = $(this).text();
    district.id = $(this).attr('data-id');
    $('.alert-address .choose li').removeClass('active');
    $('.alert-address .choose li').eq(2).addClass('active');
    $('.alert-address .choose li').eq(2).text(district.name);
    district.index = $(this).index();
    lactChoose = 'district';
})






// 收货人弹窗
$('.change_userName').on('click', function () {
    if(changeStatus != 'changeNume') {
        $('#change_info').val('');
        $('.alert-wrapper .active').removeClass('active');
    }
    $('#change_info').attr('placeholder','请输入用户名');
    $('.alert-wrapper').show();
    changeStatus = 'changeNume';
})

// 手机号弹窗
$('.change_userPhone').on('click', function () {
    if(changeStatus != 'changeNumber') {
        $('#change_info').val('');
        $('.alert-wrapper .active').removeClass('active');
    }
    $('#change_info').attr('placeholder','请输入手机号');
    $('.alert-wrapper').css('display','block');
    changeStatus = 'changeNumber';
})

// 详细地址号弹窗
$('.change_address').on('click', function () {
    console.log(changeStatus)
    if(changeStatus != 'changeAddress') {
        $('#change_info').val('');
        $('.alert-wrapper .active').removeClass('active');
    }
    $('#change_info').attr('placeholder','请输入详细地址');
    $('.alert-wrapper').css('display','block');
    changeStatus = 'changeAddress';
    console.log(changeStatus)
})




/**
 * 弹窗修改
 */
$('#change_info').on('input', function () {
    switch(changeStatus) {
        case 'changeNume':
            if($(this).val().length >= 2) {
                $('#alert-submit').addClass('active');
                consignee = $(this).val();
            } else {
                $('#alert-submit').removeClass('active');
            }
            break;
        case 'changeNumber':
            if(phoneRgx.test($(this).val())) {
                $('#alert-submit').addClass('active');
                mobile = $(this).val();
            } else {
                $('#alert-submit').removeClass('active');
            }
            break;
        case 'changeAddress':
            if($(this).val().length >= 2) {
                $('#alert-submit').addClass('active');
                address = $(this).val();
            } else {
                $('#alert-submit').removeClass('active');
            }
            break;
    }
})

/**
 * 确定修改
 */
$('.alert-wrapper').delegate('.active','click', function () {
    switch(changeStatus) {
        case 'changeNume':
            $('.user_name_con').text(consignee);
            break;
        case 'changeNumber':
            $('.user_phone_con').text(mobile);
            break;
        case 'changeAddress':
            $('.user_address_con').text(address);
            break;
        default:
            console.log('*****参数错误*******');
            break;
    }
    $('.alert-wrapper').hide();
})



$('.submit_address').on('click', function () {
    $.ajax({
        type: 'post',
        url: GlobalHost + '/api/address/add_address',
        data: {
            user_id: user_id,
            consignee: consignee,
            mobile: mobile,
            province: province.id,
            city: city.id,
            district: district.id,
            address: address,
            is_default: is_default
        },
        success: function (res) {
            console.log(res);
            if(res.code == 200) {

            }
            switch (res.code) {
                case 200:
                    createAlert($('.alert'), 'alert_tips', res.msg);
                    setTimeout(() => {
                        history.back(-1)
                    }, 1500);
                    break;
                case 400:
                    createAlert($('.alert'), 'alert_tips', res.data);
            }
        }
    })
})


if(action == 'EDIT') {
    console.log('修改地址')
    getAddressCon(address_id, user_id)
}



function getAddressCon(id, user_id) {
    $.ajax({
        type: 'post',
        url: GlobalHost + '/Api/address/detail',
        data: {
            address_id: id,
            user_id: user_id
        },
        success: function (res) {
            console.log(res)
            let data = res.data;
            address = data.address;
            address_id = data.address_id;
            consignee = data.consignee;
            province = data.province;
            city = data.city;
            district = data.district;
            mobile = data.mobile;
            $('.user_name_con').text(consignee);
            $('.user_phone_con').text(mobile);
            $('.address-con_').text(province + city + district);
            $('.user_address_con').text(address);
        }
    })
}