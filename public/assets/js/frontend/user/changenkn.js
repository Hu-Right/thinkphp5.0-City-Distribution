mui.init()
document.getElementById("edit-btn").addEventListener('tap',function(){
    var nickname = $(" input[ name='nickname' ] ").val()
    mui.ajax('/index/user/changenkn',{
        data:{nickname:nickname},
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
                    // window.location = data.url;//跳转
                    mui.openWindow({url:data.url,id:'home',createNew:true});
                },1000);
            }else{
                layer.msg(data.msg);
                //1秒后刷新页面
                /*setTimeout(function(){
                    window.location.reload();//刷新当前页面.
                },1000);*/
            }
        },
        error:function(xhr,type,errorThrown){
            layer.msg('修改失败');
            //console.log(xhr.status);
            //1秒后刷新页面
            setTimeout(function(){
                window.location.reload();//刷新当前页面.
            },1000);
        }
    });
    // 成功后返回上一页
    // mui.back()

})