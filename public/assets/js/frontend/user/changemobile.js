mui.init()
//发送验证码
document.getElementById("yzm-btn").addEventListener('tap',function(){
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
    mui.ajax('/api/sms/send',{
        data:{mobile:mobile,event:'changemobile'},
        dataType:'json',//服务器返回json格式数据
        type:'post',//HTTP请求类型
        timeout:10000,//超时时间设置为10秒；
        //headers:{'Content-Type':'application/json'},
        success:function(data){
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
        },
        error:function(data){
            layer.msg('发送失败请重试');
            //0.5秒后刷新页面
            setTimeout(function(){
                window.location.reload();//刷新当前页面.
            },500);
        }
    });
})
//修改手机号
document.getElementById("edit-btn").addEventListener('tap',function(){
    var mobile = $(" input[ name='mobile' ] ").val(),
        captcha = $(" input[ name='captcha' ] ").val()
    mui.ajax('/index/user/changemobile',{
        data:{mobile:mobile,captcha:captcha},
        dataType:'json',//服务器返回json格式数据
        type:'post',//HTTP请求类型
        timeout:10000,//超时时间设置为10秒；
        //headers:{'Content-Type':'application/json'},
        success:function(data){
            //console.log(data);
            if(data.code == 1){
                layer.msg(data.msg);
                //1秒后刷新页面
                setTimeout(function(){
                    window.location = data.url;//跳转
                },1000);
            }else{
                layer.msg(data.msg);
                //1秒后刷新页面
                /*setTimeout(function(){
                    window.location.reload();//刷新当前页面.
                },1000);*/
            }
        },
        error:function(data){
            layer.msg('修改失败');
            //1秒后刷新页面
            setTimeout(function(){
                window.location.reload();//刷新当前页面.
            },1000);
        }
    });
})
