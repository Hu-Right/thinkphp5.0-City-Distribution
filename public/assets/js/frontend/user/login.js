mui.init({});

//获得本界面所有的button
var button = document.getElementsByTagName('button');

// console.log(button);
//密码登录
button['0'].onclick = function(){
    var mobile = $(" input[ name='mobile' ] ").val(),
        password = $(" input[ name='password' ] ").val()
    $.ajax({
        type: "POST", //用POST方式传输
        dataType: "json", //数据格式:JSON
        url: '/index/user/login', //目标地址
        data: {mobile:mobile,password:password}, //post携带数据
        error: function (data) {
            //console.log(1);
            layer.msg('登录失败');
            //1秒后刷新页面
            setTimeout(function(){
                window.location.reload();//刷新当前页面.
            },1000);
        }, //请求错误时的处理函数
        success: function (data){
            //console.log(data);
            if(data.code == 1){
                layer.msg(data.msg);
                //1秒后跳转
                setTimeout(function(){
                    //跳转
                    mui.openWindow({
                        url: data.url,
                        id:'home',
                        createNew:true
                    });
                },1000);
            }else{
                layer.msg(data.msg);
            }
        } //请求成功时执行的函数
    });
}

//获取验证码
button['1'].onclick = function(){
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
    $.ajax({
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
        }, //请求成功时执行的函数
    });
}

//验证码登录
button['2'].onclick = function(){
    var phone = $(" input[ name='phone' ] ").val(),
        captcha = $(" input[ name='captcha' ] ").val()
    $.ajax({
        type: "POST", //用POST方式传输
        dataType: "json", //数据格式:JSON
        url: '/index/user/mobilelog', //目标地址
        data: {mobile:phone,captcha:captcha}, //post携带数据
        error: function (data) {
            //console.log(1);
            layer.msg('登录失败');
            //1秒后刷新页面
            setTimeout(function(){
                window.location.reload();//刷新当前页面.
            },1000);
        }, //请求错误时的处理函数
        success: function (data){
            console.log(data.url);
            if(data.code == 1){
                layer.msg(data.msg);
                //1秒后跳转
                setTimeout(function(){
                    //跳转
                    mui.openWindow({
                        url: top.location.origin+data.url,
                        id:'home',
                        createNew:true
                    });
                },1000);
            }else{
                layer.msg(data.msg);
            }
        }, //请求成功时执行的函数
    });
}

//快速注册
button['3'].onclick = function(){
    mui.openWindow('mobilereg','',{})
}

//忘记密码
button['4'].onclick = function(){
    mui.openWindow('resetpwd','',{})
}


/*-------------------------------三方登陆--开始-------------------------------------------*/
mui.plusReady(function() {
    plus.oauth.getServices(function(services) {
        auths = services;
    }, function(e) {
        alert("获取登录服务列表失败：" + e.message + " - " + e.code);
    });
})

if(document.getElementById('loginByWX')){
    document.getElementById('loginByWX').addEventListener('tap',function() {
        // console.log("微信");
        // authLogin('weixin');
        layer.msg('敬请期待');
    })
}

if(document.getElementById('loginByQQ')){
    document.getElementById('loginByQQ').addEventListener('tap',function() {
        // console.log("QQ");
        authLogin('qq');
    })
}

// 登录操作
function authLogin(type) {
    var s;
    for (var i = 0; i < auths.length; i++) {
        if (auths[i].id == type) {
            s = auths[i];
            break;
        }
    }
    if (!s.authResult) {
        s.login(function(e) {
            mui.toast("登录认证成功！");
            authUserInfo(type);
        }, function(e) {
            mui.toast("登录认证失败！");
        });
    } else {
        mui.toast("已经登录认证！");
    }
}
//注销
function authLogout() {
    for (var i in auths) {
        var s = auths[i];
        if (s.authResult) {
            s.logout(function(e) {
                console.log("注销登录认证成功！");
                // alert("注销登录认证成功！");
            }, function(e) {
                console.log("注销登录认证失败！");
                // alert("注销登录认证失败！");
            });
        }
    }
}
// 登录认证信息
function authUserInfo(type) {
    var s;
    for (var i = 0; i < auths.length; i++) {
        if (auths[i].id == type) {
            s = auths[i];
            break;
        }
    }
    if (!s.authResult) {
        mui.toast("未授权登录！");
    } else {
        s.getUserInfo(function(e) {
            // console.log(JSON.stringify(s));
            var userInfo = s.userInfo;
            var openid = s.authResult.openid;
            // console.log("用户信息：" + JSON.stringify(userInfo));
            // console.log("用户openid：" + openid);
            // var binding_res = chenk_binding(type, openid);
            //检测是否绑定
            mui.ajax({
                type: "POST", //用POST方式传输
                dataType: "json", //数据格式:JSON
                url: '/index/user/chenkBinding', //目标地址
                data: {type:type, openid:openid}, //post携带数据
                async:false,
                success: function (data){
                    // console.log(JSON.stringify(data));
                    if(data.code == 1){
                        // layer.msg(data.msg);
                        //1秒后跳转
                        setTimeout(function(){
                            mui.openWindow({
                                url: top.location.origin+data.url,
                                id:'home',
                                createNew:true
                            });
                            // window.location = data.url;//跳转
                        },1000);
                    }else if(data.code == 0){
                        layer.msg(data.msg);
                        //1秒后刷新页面
                        setTimeout(function(){
                            window.location.reload();//刷新当前页面.
                        },1000);
                    }else if(data.code == 3){
                        // result = data.code;
                        getData(type,userInfo,openid);
                    }
                },//请求成功时执行的函数, //请求错误时的处理函数
                error: function (xhr,type,errorThrown) {
                    console.log(xhr.status);
                    // layer.msg('登录失败');
                    //1秒后刷新页面
                    /*setTimeout(function(){
                        window.location.reload();//刷新当前页面.
                    },1000);*/
                }
            });
            authLogout();
        }, function(e) {
            console.log("获取用户信息失败：" + e.message + " - " + e.code);
        });
    }
}
// 获取信息
function getData(type,data,openid) {
    switch (type){
        case 'weixin':
            // console.log(data);
            mui.openWindow({
                url:top.location.origin+'/index/user/otherLogin',
                id:'other_login',
                extras: {
                    type:type,
                    WX_openid: openid,
                    WX_unionid: data.unionid,
                    nickname: data.nickname,
                    avatar: data.headimgurl
                },
                createNew:true
            });
            break;
        case 'qq':
            // console.log(data);
            mui.openWindow({
                url:top.location.origin+'/index/user/otherLogin',
                id:'other_login',
                extras: {
                    type:type,
                    QQ_openid: openid,
                    nickname: data.nickname,
                    avatar: data.figureurl_qq_2
                },
                createNew:true
            });
            break;
        default:
            break;
    }
}
/*-------------------------------三方登陆--结束-------------------------------------------*/