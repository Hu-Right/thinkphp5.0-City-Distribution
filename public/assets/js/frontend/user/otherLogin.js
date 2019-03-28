mui.init()

// openwindow 传参
var type = '',
    QQ_openid = '',
    WX_openid = '',
    WX_unionid = '',
    nickname = '',
    avatar = '';

mui.plusReady(function() {
    // 接收父级页面传的参数
    var self = plus.webview.currentWebview();
    type = self.type;
    if(type == 'qq'){
        QQ_openid = self.QQ_openid,
        nickname = self.nickname,
        avatar = self.avatar;
    }else if(type == 'weixin'){
        WX_openid = self.WX_openid,
        WX_unionid = self.WX_unionid,
        nickname = self.nickname,
        avatar = self.avatar;
    }

})

//获取验证码
document.getElementById('yzm-btn').addEventListener('tap',function(){
    var phone = $(" input[ name='phone' ] ").val();
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
    if(phone == ''){
        layer.msg('手机号不能为空');
        //0.5秒后刷新页面
        setTimeout(function(){
            window.location.reload();//刷新当前页面.
        },500);
        return false;
    }
    mui.ajax({
        type: "POST", //用POST方式传输
        dataType: "json", //数据格式:JSON
        url: '/api/sms/send', //目标地址
        data: {mobile:phone,event:'login'}, //post携带数据
        error: function (data) {
            layer.msg('发送失败请重试');
            //0.5秒后刷新页面
            setTimeout(function(){
                window.location.reload();//刷新当前页面.
            },500);
        }, //请求错误时的处理函数
        success: function (data){
            layer.msg(data.msg);
        } //请求成功时执行的函数
    });
})

//登录
document.getElementById('login').addEventListener('tap',function(){
    var phone = $(" input[ name='phone' ] ").val(),
        captcha = $(" input[ name='captcha' ] ").val();
    if(type == 'qq'){
        var data = {
            type:type,
            QQ_openid:QQ_openid,
            nickname:nickname,
            avatar:avatar,
            mobile:phone,
            captcha:captcha
        };
    }else if(type == 'weixin'){
        var data = {
            type:type,
            WX_openid:WX_openid,
            WX_unionid:WX_unionid,
            nickname:nickname,
            avatar:avatar,
            mobile:phone,
            captcha:captcha
        };
    }

    mui.ajax({
        type: "POST", //用POST方式传输
        dataType: "json", //数据格式:JSON
        url: '/index/user/otherLogin', //目标地址
        data: data, //post携带数据
        error: function (xhr,type,errorThrown) {
            // console.log(xhr.status);
            layer.msg('绑定失败!');
            //1秒后刷新页面
            setTimeout(function(){
                //跳转
                mui.openWindow({
                    url:top.location.origin+'/index/user/login',
                    id:'login',
                    createNew:true
                });
            },1000);
        }, //请求错误时的处理函数
        success: function (data){
            console.log(JSON.stringify(data));
            layer.msg(data.msg);
            //1秒后刷新页面
            setTimeout(function(){
                //跳转
                mui.openWindow({
                    url: top.location.origin+data.url,
                    id:'home',
                    createNew:true
                });
            },1000);
        }//请求成功时执行的函数
    });
})