mui.init()

//获得本界面所有的button
var button = document.getElementsByTagName('button');

//console.log(button);
//获取验证码
button['0'].onclick = function(){
    var mobile = $(" input[ name='mobile' ] ").val();
    var btn = $(this);
    var count = 60;
    var resend = setInterval(function(){
        count--;
        if (count > 0){
            btn.html(count+"S");
        }else {
            clearInterval(resend);
            btn.html("获取验证码").removeAttr('disabled style');
        }
    }, 1000);
    btn.attr('disabled',true).css('cursor','not-allowed');
    if(mobile == ''){
        layer.msg('手机号不能为空');
        //0.5秒后刷新页面
        setTimeout(function(){
            window.location.reload();//刷新当前页面.
        },500);
        return false;
    }
    $.ajax({
        type: "POST", //用POST方式传输
        dataType: "json", //数据格式:JSON
        url: '/api/sms/send', //目标地址
        data: {mobile:mobile,event:'register'}, //post携带数据
        error: function (data) {
            layer.msg('发送失败请重试');
            //0.5秒后刷新页面
            setTimeout(function(){
                window.location.reload();//刷新当前页面.
            },500);
        }, //请求错误时的处理函数
        success: function (data){
            //console.log(data);
            if(data.code == 0){
                layer.msg(data.msg);
                //0.5秒后刷新页面
                setTimeout(function(){
                    window.location.reload();//刷新当前页面.
                },500);
            }else{
                layer.msg(data.msg);
            }
            //
        }, //请求成功时执行的函数
    });
}

//账号注册
button['1'].onclick = function(){
    var mobile = $(" input[ name='mobile' ] ").val(),
        referee = $(" input[ name='referee' ] ").val(),
        captcha = $(" input[ name='captcha' ] ").val(),
        password = $(" input[ name='password' ] ").val(),
        confirmpw = $(" input[ name='confirmpw' ] ").val()
    $.ajax({
        type: "POST", //用POST方式传输
        dataType: "json", //数据格式:JSON
        url: '/index/user/mobilereg', //目标地址
        data: {mobile:mobile,referee:referee,captcha:captcha,password:password,confirmpw:confirmpw}, //post携带数据
        error: function (xhr,type,errorThrown) {
            // console.log(type);
            // console.log(xhr.status);
            layer.msg('注册失败');
            //1秒后刷新页面
            /*setTimeout(function(){
                window.location.reload();//刷新当前页面.
            },1000);*/
        }, //请求错误时的处理函数
        success: function (data){
            // console.log(data);
            // return false;
            if(data.code == 1){
                layer.msg(data.msg);
                //1秒后跳转
                setTimeout(function(){
                    window.location = data.url;//跳转
                },1000);
            }else{
                layer.msg(data.msg);
                //1秒后刷新页面
                setTimeout(function(){
                    window.location.reload();//刷新当前页面.
                },1000);
            }
        }, //请求成功时执行的函数
    });
}

//服务条款
button['2'].onclick = function(){

}

//已有账号
button['3'].onclick = function(){
    mui.openWindow('login','',{})
}