
let user_id = '';
let getUserInfoMsg = '';
let loadFlag = false;

if(localStorage.getItem('USERINFO') && localStorage.getItem('USERINFO') != 'null') {
    let myUsetInfo = localStorage.getItem('USERINFO');
    myUsetInfo = JSON.parse(myUsetInfo);
    console.log(myUsetInfo)
    user_id = myUsetInfo.user_id;
} else {
    user_id = 0;
}


var years=[];
var month=[];
var day=[];
var myDate = new Date();
myDate.getFullYear();    //获取完整的年份(4位,1970-????)
myDate.getMonth();       //获取当前月份(0-11,0代表1月)
myDate.getDate();
var _data=[];
for(i=0; i<myDate.getFullYear()-1980; i++){
    //年
    var obj={};
    var yer=1980+i+1;
    obj.value=1980+i+1;
    var _data2=[];
    for(n=0; n<12; n++){
        //月
        var obj2={};
        if(n<9){
            obj2.value='0'+(n+1);
        }else{
            obj2.value=n+1;
        }
        var _data3=[];
        if(n==1){
            var cond1 = yer % 4 == 0;  //条件1：年份必须要能被4整除
            var cond2 = yer % 100 != 0;  //条件2：年份不能是整百数
            var cond3 = yer % 400 ==0;
            var cond = cond1 && cond2 || cond3;
            //闰年
            if(cond){
                for(y=0; y<29; y++){
                    //日
                    var obj3={};
                    if(y<9){
                        obj3.value='0'+(y+1);
                    }else{
                        obj3.value=y+1;
                    }
                    _data3.push(obj3)
                }
            }else{
                for(y=0; y<28; y++){
                    //日
                    var obj3={};
                    if(y<9){
                        obj3.value='0'+(y+1);
                    }else{
                        obj3.value=y+1;
                    }
                    _data3.push(obj3)
                }
            }
        }else if(n==0||n==2||n==4||n==6||n==7||n==9||n==11){
            for(y=0; y<31; y++){
                //日
                var obj3={};
                if(y<9){
                    obj3.value='0'+(y+1);
                }else{
                    obj3.value=y+1;
                }
                _data3.push(obj3)
            }
        }else{
            for(y=0; y<30; y++){
                //日
                var obj3={};
                if(y<9){
                    obj3.value='0'+(y+1);
                }else{
                    obj3.value=y+1;
                }
                _data3.push(obj3)
            }
        }
        obj2.childs=_data3;
        _data2.push(obj2);
    }
    obj.childs=_data2;
    _data.push(obj)
}
var mobileSelect1 = new MobileSelect({
    trigger: '#trigger1',
    title: '日期选择',
    wheels: [
        {data:_data}
    ],
    transitionEnd:function(indexArr, data){
        console.log(data);
    },
    callback:function(indexArr, data){
        console.log(data);
        $("#trigger1").text(data[0].value + '-' + data[1].value+'-'+data[2].value);
        save.birDay = data[0].value + '-' + data[1].value+'-'+data[2].value;
        changeInfo("2")
    }
});



/**
 * 
 * 
 * 
 */

let cache = {
    phone : '',
    sex : '',
    birDay : '',
    name : '',
    IDCard: '',
    IDname: ''
}

let save = {
    phone : '',
    sex : '',
    birDay : '',
    name : '',
    IDCard: '',
    IDname: ''
}

$('.list_wrap').delegate('li p', 'click', function () {
    console.log($(this).attr('data-status'))
    switch($(this).attr('data-status')) {
        case "name":
            if($(this).attr('data-default') == 1) {
                return;
            }
            $('.name_save').show();
            $('.bg').show();
            break;
        case "sex":
            // $('.bg').show();
            // $('.save_sex').show();
            break;
        case "phone":
            if($(this).attr('data-default') == 1) {
                return;
            }
            $('.save_phone').show();
            $('.bg').show();
            break;
        case "idCard": // 身份证
            $('.bg').show();
            $('.save_IDcard').show();
            
        default:
            console.log('*')
    }
})



$('.save .title .r').click(function () {
    $('.name_save').hide();
    $('.save_sex').hide();
    $('.save_phone').hide();
    $('.bg').hide();
    $('.save_IDcard').hide();
})

$('.bg').click(function () {
    $('.name_save').hide();
    $('.save_sex').hide();
    $('.save_phone').hide();
    $('.bg').hide();
    $('.save_IDcard').hide();
})





// 验证姓名
$('#info_user_name').on('input', function () {
    console.log($(this).val())
    if(phoneRgx.test($(this).val())) {
        $('.save_phone .sub button').addClass('active');
        cache.phone = $(this).val();
    }
})


// 性别
$('.save_sex .item').click(function () {
    console.log($(this).attr('data-sex'));
    switch($(this).attr('data-sex')) {
        case "male":
            $('.bg').hide();
            $('.save_sex').hide();
            save.sex = '男';
            $('.list_wrap .list_item:eq(1) .r p').text(save.sex);
            break;
        case "female":
            $('.bg').hide();
            $('.save_sex').hide();
            save.sex = '女';
            $('.list_wrap .list_item:eq(1) .r p').text(save.sex);
            break;
        case "sub":
            $('.bg').hide();
            $('.save_sex').hide();
            break;
    }
})


// 验证手机
$('.save_phone').delegate('.active','click',function () {
    save.phone = cache.phone;
    $('.list_wrap .list_item:eq(2) .r p').text(save.phone);
    $('.bg').hide();
    $('.save_phone').hide();
})


$('#save_name').on('input', function () {
    console.log($(this).val());
    if($(this).val().length >= 2) {
        $('.name_save button').addClass('active');
        cache.name = $(this).val();
    }
})
$('.name_save').delegate('.active','click', function () {
    save.name = cache.name;
    $('.bg').hide();
    $('.name_save').hide();
    $('.list_wrap .list_item:eq(0) .r p').text(save.name);
})

// 验证身份证
$('#info_IDcard').on('input', function () {
    console.log($(this).val());
    if(shenfenCardRgx.test($(this).val())) {
        if(cache.IDCname) {
            $('.save_IDcard .sub button').addClass('active');
        } else {
            $('.save_IDcard .sub button.active').removeClass('active');
        }
        cache.IDCard = $(this).val();
    }
})
$('#info_IDname').on('input', function () {
    console.log($(this).val());
    if($(this).val().length >= 2) {
        cache.IDCname = $(this).val();
        if(cache.IDCard) {
            $('.save_IDcard .sub button').addClass('active');
        } else {
            $('.save_IDcard .sub button.active').removeClass('active');
        }
    }
})

$('.save_IDcard').delegate('.active', 'click', function () {
    console.log('验证身份证。。。')
})




getInfo ();
function getInfo () {
    $.ajax({
        type: "post",
        url: GlobalHost + '/Api/user/getUserInfo',
        data: {
            user_id: user_id
        },
        success: function (res) {
            console.log(res);
            getUserInfoMsg = res.data;
            loadFlag = true;
        }
    })
}

setInterval(() => {
    if(loadFlag) {
        if(getUserInfoMsg) {
            if(getUserInfoMsg.mobile) {
                $('.list_wrap .list_item:eq(3) .r p').text(getUserInfoMsg.mobile).attr('data-default', '1');
            }
            
            if(getUserInfoMsg.nickname) {
                $('.list_wrap .list_item:eq(0) .r p').text(getUserInfoMsg.nickname).attr('data-default', '1');
            }
            if(getUserInfoMsg.IDCard) {
                $('.list_wrap .list_item:eq(2) .r p').text(getUserInfoMsg.IDCard).attr('data-default', '1');
            }
            switch(getUserInfoMsg.sex) {
                case "0":
                    $('.list_wrap .list_item:eq(1) .r p').text("未完善");
                    break;
                case "1":
                    $('.list_wrap .list_item:eq(1) .r p').text("男");
                    break;
                case "2":
                    $('.list_wrap .list_item:eq(1) .r p').text("女");
                    break;
            }
            if(getUserInfoMsg.birthday) {
                $('.list_wrap .list_item:eq(4) .r p').text(getUserInfoMsg.birthday).attr('data-default', '1');
            }
            loadFlag = false;
        }
    }
}, 200);


/**
 * @param {*} status 
 * @param {*} val 
 */
// 昵称：nickname; 
// 姓名：realname; 
// 生日：birthday 格式：1990-12-12
function changeInfo(status) {
    switch(status) {
        case "0": // 姓名
            postInfo(save.name, "nickname");
            break;
        case "1": // 手机号
            break;
        case "2": // 生日
            console.log(save.birDay)
            postInfo(save.birDay, "birthday");
            break;
        case "3": // 身份证
            break;
    }
}

function postInfo(fieldValue, field) {
    console.log(fieldValue)
    $.ajax({
        type: 'post',
        url: GlobalHost + '/Api/user/changeField',
        data: {
            user_id: user_id,
            fieldValue: fieldValue,
            field: field
        },
        success: function (res) {
            console.log(res);
        },
        error: function(error) {
            console.log(error)
        }
    })
}



function yzIDcard(name, id) {
    $.ajax({
        type: 'postp',
        url: GlobalHost + '/Api/user/IDCardAuth',
        data: {
            user_id: user_id,
            realname: name,
            IDCard: id
        },
        success: function (res) {
            console.log(res);
        }
    })
}