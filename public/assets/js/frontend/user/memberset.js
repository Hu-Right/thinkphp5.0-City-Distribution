mui.init()

//支付顺序
document.getElementById("zhifu").addEventListener('tap',function(){
    mui.openWindow('paymentsort',{})
})
// 只允许佩戴保温箱的跑男接单
document.getElementById("mySwitch-one").addEventListener("toggle",function(event){
    if(event.detail.isActive){
        //console.log("你启动了---只允许佩戴保温箱的跑男接单");
        mui.ajax("/index/user/memberset",{
            type: 'post',
            data:{isincubator:1},
            dataType:'json',//服务器返回json格式数据
            type:'post',//HTTP请求类型
            success: function (data) {
                //console.log(data);
            },
            error: function (data) {
                layer.msg('修改失败');
                setTimeout(function(){
                    window.location.reload();//刷新当前页面.
                },1000);
            }
        });
    }else{
        //console.log("你关闭了---只允许佩戴保温箱的跑男接单");
        mui.ajax("/index/user/memberset",{
            type: 'post',
            data:{isincubator:0},
            dataType:'json',//服务器返回json格式数据
            type:'post',//HTTP请求类型
            success: function (data) {
                //console.log(data);
            },
            error: function (data) {
                layer.msg('修改失败');
                setTimeout(function(){
                    window.location.reload();//刷新当前页面.
                },1000);
            }
        });
    }
})
// 取货时，不需要跑男给客户打电话
document.getElementById("mySwitch-two").addEventListener("toggle",function(event){
    if(event.detail.isActive){
        //console.log("你启动了---取货时，不需要跑男给客户打电话");
        mui.ajax("/index/user/memberset",{
            type: 'post',
            data:{iscall:1},
            dataType:'json',//服务器返回json格式数据
            type:'post',//HTTP请求类型
            success: function (data) {
                //console.log(data);
            },
            error: function (data) {
                layer.msg('修改失败');
                setTimeout(function(){
                    window.location.reload();//刷新当前页面.
                },1000);
            }
        });
    }else{
        //console.log("你关闭了---取货时，不需要跑男给客户打电话");
        mui.ajax("/index/user/memberset",{
            type: 'post',
            data:{iscall:0},
            dataType:'json',//服务器返回json格式数据
            type:'post',//HTTP请求类型
            success: function (data) {
                //console.log(data);
            },
            error: function (data) {
                layer.msg('修改失败');
                setTimeout(function(){
                    window.location.reload();//刷新当前页面.
                },1000);
            }
        });
    }
})
// 是否启用语音提示功能别
document.getElementById("mySwitch-three").addEventListener("toggle",function(event){
    if(event.detail.isActive){
        //console.log("你启动了---是否启用语音提示功能别");
        mui.ajax("/index/user/memberset",{
            type: 'post',
            data:{isvoiceprompt:1},
            dataType:'json',//服务器返回json格式数据
            type:'post',//HTTP请求类型
            success: function (data) {
                //console.log(data);
            },
            error: function (data) {
                layer.msg('修改失败');
                setTimeout(function(){
                    window.location.reload();//刷新当前页面.
                },1000);
            }
        });
    }else{
        //console.log("你关闭了---是否启用语音提示功能别");
        mui.ajax("/index/user/memberset",{
            type: 'post',
            data:{isvoiceprompt:0},
            dataType:'json',//服务器返回json格式数据
            type:'post',//HTTP请求类型
            success: function (data) {
                //console.log(data);
            },
            error: function (data) {
                layer.msg('修改失败');
                setTimeout(function(){
                    window.location.reload();//刷新当前页面.
                },1000);
            }
        });
    }
})