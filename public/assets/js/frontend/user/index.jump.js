var close = document.querySelector(".mui-icon-arrowleft")
// 关闭侧边栏
close.addEventListener('tap',function(){
mui('.mui-off-canvas-wrap').offCanvas().close();
})


document.getElementById('msg').addEventListener('tap',function(){
    var islogin = $(" input[ name='islogin' ] ").val();
    // console.log(islogin);
    //alert(top.location.origin);
    if(islogin==0){
        //alert(top.location.origin);
        mui.openWindow({url:top.location.origin+'/index/user/login',id:'login',createNew:true})
        //window.location.href = 'login';
    }else if(islogin==1){
        //alert(top.location.origin);
        //console.log(top.location.origin);
        mui.openWindow({url:top.location.origin+'/index/message/msgListHead',id:'msgListHead',createNew:true})
        //window.location.href = 'profile';
    }else{
        setTimeout(function(){
            window.location.reload();//刷新当前页面.
        },1000);
    }
})

// 点击头像登录 // 未登录跳转页面
var login = document.getElementById("img")
login.addEventListener('tap',function(){
    var islogin = $(" input[ name='islogin' ] ").val();
    //alert(top.location.origin);
    if(islogin==0){
        //alert(top.location.origin);
        mui.openWindow({url:top.location.origin+'/index/user/login',id:'login',createNew:true})
        //window.location.href = 'login';
    }else if(islogin==1){
        //alert(top.location.origin);
        //console.log(top.location.origin);
        mui.openWindow({url:top.location.origin+'/index/user/profile',id:'profile',createNew:true})
        //window.location.href = 'profile';
    }else{
        setTimeout(function(){
            window.location.reload();//刷新当前页面.
        },1000);
    }
})

// 活动
document.getElementById('activity').addEventListener('tap',function () {
    layer.msg('敬请期待');
})

// 功能模块
var gongneng = document.getElementById("gongneng")

var gongnengLi = gongneng.getElementsByTagName('li')
// 订单中心
gongnengLi[0].addEventListener('tap',function(){
    var islogin = $(" input[ name='islogin' ] ").val();

    //console.log(islogin);

    if(islogin==0){
        mui.openWindow({url:top.location.origin+'/index/user/login',id:'login',createNew:true})
    }else if(islogin==1){
        mui.openWindow({url:top.location.origin+'/index/order/index',id:'/index/order/index',createNew:true})
    }else{
        setTimeout(function(){
            window.location.reload();//刷新当前页面.
        },1000);
    }
})
// 邀请奖励
gongnengLi[1].addEventListener('tap',function(){
    var islogin = $(" input[ name='islogin' ] ").val();
    if(islogin==0){
        mui.openWindow({url:top.location.origin+'/index/user/login',id:'login',createNew:true})
    }else if(islogin==1){
        mui.openWindow({url:top.location.origin+'/index/user/invitingawards',id:'invitingawards',createNew:true})
    }else{
        setTimeout(function(){
            window.location.reload();//刷新当前页面.
        },1000);
    }
})
// 我的地址
gongnengLi[2].addEventListener('tap',function(){
    var islogin = $(" input[ name='islogin' ] ").val();
    if(islogin==0){
        mui.openWindow({url:top.location.origin+'/index/user/login',id:'login',createNew:true})
    }else if(islogin==1){
        mui.openWindow({url:top.location.origin+'/index/address/index',id:'address',createNew:true})
    }else{
        setTimeout(function(){
            window.location.reload();//刷新当前页面.
        },1000);
    }
})
// 用户设置
gongnengLi[3].addEventListener('tap',function(){
    var islogin = $(" input[ name='islogin' ] ").val();
    if(islogin==0){
        mui.openWindow({url:top.location.origin+'/index/user/login',id:'login',createNew:true})
    }else if(islogin==1){
        mui.openWindow({url:top.location.origin+'/index/user/memberset',id:'memberset',createNew:true})
    }else{
        setTimeout(function(){
            window.location.reload();//刷新当前页面.
        },1000);
    }
})
// 使用帮助
gongnengLi[4].addEventListener('tap',function(){
    var islogin = $(" input[ name='islogin' ] ").val();
    if(islogin==0){
        mui.openWindow({url:top.location.origin+'/index/user/login',id:'login',createNew:true})
    }else if(islogin==1){
        mui.openWindow({url:top.location.origin+'/index/user/userhelp',id:'userhelp',createNew:true})
    }else{
        setTimeout(function(){
            window.location.reload();//刷新当前页面.
        },1000);
    }
})

// 余额
document.getElementById("balance").addEventListener('tap',function(){
    var islogin = $(" input[ name='islogin' ] ").val();

    console.log(islogin);

    if(islogin==0){
        mui.openWindow({url:top.location.origin+'/index/user/login',id:'login',createNew:true})
    }else if(islogin==1){
        mui.openWindow({url:top.location.origin+'/index/bill/index',id:'/index/bill/index',createNew:true})
    }else{
        setTimeout(function(){
            window.location.reload();//刷新当前页面.
        },1000);
    }
})

// 在线充值
document.getElementById("recharge").addEventListener('tap',function(){
    var islogin = $(" input[ name='islogin' ] ").val();
    if(islogin==0){
        mui.openWindow({url:top.location.origin+'/index/user/login',id:'login',createNew:true})
    }else if(islogin==1){
        mui.openWindow({url:top.location.origin+'/index/recharge/index',id:'recharge',createNew:true})
    }else{
        setTimeout(function(){
            window.location.reload();//刷新当前页面.
        },1000);
    }
})

mui.plusReady(function() {
    console.log('id:'+plus.webview.currentWebview().id);

})
