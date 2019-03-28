mui.init();
var islogin = $(" input[ name='islogin' ] ").val();
// console.log(islogin);



if(islogin == 1){
    var automatic = setInterval(push,2000);
}


function push(){

    ajaxPush()

}


// push()

//刷新页面


//ajax刷新
function ajaxPush(){
    mui.ajax("/index/user/oneLogin",{
        type: 'post',
        dataType:'json',//服务器返回json格式数据
        type:'post',//HTTP请求类型
        success: function (data) {
            // console.log(data);
            if(data.code == 1){
                clearInterval(automatic);//成功后清除定时服务
                mui.alert("您的账号已在别处登录，\r\n如非本人操作请修改密码!",null,'好',function (e) {
                    mui.openWindow({url:top.location.origin+'/index/user/login',id:'login',createNew:true});
                });
                // mui.openWindow({url:top.location.origin+'/index/user/login',id:'login',createNew:true});
                return false;
            }
        },
        error: function (xhr,type,errorThrown) {
            //console.log(xhr.readyState);

        }
    });
}