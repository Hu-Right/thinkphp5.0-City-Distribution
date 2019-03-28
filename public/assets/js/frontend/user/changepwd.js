mui.init()
//修改密码
document.getElementById("edit-btn").addEventListener('tap',function(){
    var oldpassword = $(" input[ name='oldpassword' ] ").val(),
        newpassword = $(" input[ name='newpassword' ] ").val(),
        renewpassword = $(" input[ name='renewpassword' ] ").val()
    mui.ajax('/index/user/changepwd',{
        data:{oldpassword:oldpassword,newpassword:newpassword,renewpassword:renewpassword},
        dataType:'json',//服务器返回json格式数据
        type:'post',//HTTP请求类型
        timeout:10000,//超时时间设置为10秒；
        //headers:{'Content-Type':'application/json'},
        success:function(data){
            // console.log(data);
            if(data.code == 1){
                layer.msg(data.msg);
                //1秒后刷新页面
                setTimeout(function(){
                    mui.openWindow({url:data.url,id:'login',createNew:true});
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