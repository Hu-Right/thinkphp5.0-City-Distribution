mui.init()


//返回主页
document.getElementById("back").addEventListener('tap',function(){
    mui.openWindow({
        url:top.location.origin+'/index/user/index',
        createNew:true
    })
})

var li = document.getElementsByTagName('li')

//修改头像，触发事件
li[0].addEventListener('tap',function(){
    //点击触发上传文件按钮
    //console.log(111);
    document.getElementById("file").click();
})
//修改头像，次函数
function fileSelected(_this) {
    //文件选择后触发次函数,上传图片
    var formdata = new FormData();
    formdata.append('file',_this[0].files[0]);
    mui.ajax("/api/common/upload",{
        type: 'post',
        data:formdata,
        processData:false,
        contentType:false,
        success: function (data) {
            //console.log(111)
            //console.log(data);
            var imgUrl = data.data.url;
            if(data.code == 1){
                //layer.msg(data.msg);
                //console.log(data.data.url);
                mui.ajax("/api/user/profile",{
                    type: 'post',
                    data:{avatar:imgUrl},
                    dataType:'json',//服务器返回json格式数据
                    type:'post',//HTTP请求类型
                    success: function (data) {
                        //console.log(imgUrl);
                        if(data.code == 1){
                            layer.msg('修改头像成功');
                            document.getElementById('avatar').src = imgUrl;
                        }else{
                            layer.msg('修改头像失败');
                            setTimeout(function(){
                                window.location.reload();//刷新当前页面.
                            },1000);
                        }
                    },
                    error: function (data) {
                        layer.msg('修改失败');
                        setTimeout(function(){
                            window.location.reload();//刷新当前页面.
                        },1000);
                    }
                });
            }else{
                layer.msg(data.msg);
                setTimeout(function(){
                    window.location.reload();//刷新当前页面.
                },1000);
            }
        },
        error: function (data) {
            layer.msg('修改失败');
            setTimeout(function(){
                window.location.reload();//刷新当前页面.
            },1000);
        }
    });
}

//修改昵称
li[1].addEventListener('tap',function(){
    mui.openWindow({url:'changenkn',id:'changenkn',createNew:true});
})
//查看等级
li[2].addEventListener('tap',function(){
    mui.openWindow('showlevel','',{})
})
//修改性别
li[3].addEventListener('tap',function(){
    mui('#sex').popover('toggle');
})
document.getElementById("man").addEventListener('tap',function(){
    mui('#sex').popover('toggle');
    mui.ajax('/index/user/changesex',{
        data:{gender:1},
        dataType:'json',//服务器返回json格式数据
        type:'post',//HTTP请求类型
        timeout:10000,//超时时间设置为10秒；
        //headers:{'Content-Type':'application/json'},
        success:function(data){
            //console.log(data);
            if(data.code == 1){
                layer.msg(data.msg);
                $('.gender').text('男');
            }else{
                layer.msg(data.msg);
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
document.getElementById("woman").addEventListener('tap',function(){
    mui('#sex').popover('toggle');
    mui.ajax('/index/user/changesex',{
        data:{gender:2},
        dataType:'json',//服务器返回json格式数据
        type:'post',//HTTP请求类型
        timeout:10000,//超时时间设置为10秒；
        //headers:{'Content-Type':'application/json'},
        success:function(data){
            //console.log(data);
            if(data.code == 1){
                layer.msg(data.msg);
                $('.gender').text('女');
            }else{
                layer.msg(data.msg);
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
//修改手机号
li[4].addEventListener('tap',function(){
    mui.openWindow({url:'changemobile',id:'changemobile',createNew:true});
})
//修改密码
li[5].addEventListener('tap',function(){
    mui.openWindow({url:'changepwd',id:'changepwd',createNew:true});
})
//退出账号
document.getElementById("logout").addEventListener('tap',function(){
    //console.log(1);
    mui.ajax('/index/user/logout',{
        data:{},
        dataType:'json',//服务器返回json格式数据
        type:'post',//HTTP请求类型
        success:function(data){
            //console.log(data);
            if(data.code == 1){
                layer.msg(data.msg);
                //1秒后刷新页面
                setTimeout(function(){
                    //跳转
                    mui.openWindow({url:data.url,id:'login',createNew:true})
                },1000);
            }else{
                layer.msg(data.msg);
                //1秒后刷新页面
                setTimeout(function(){
                    window.location.reload();//刷新当前页面.
                },1000);
            }
        },
        error:function(data){
            layer.msg('退出失败');
            //1秒后刷新页面
            setTimeout(function(){
                window.location.reload();//刷新当前页面.
            },1000);
        }
    });
})