// 选项卡切换---主体
var index = 0;
var times2 = null;
mui('#sliderSegmentedControl').on('tap', '.mui-control-item', function() {
    index = this.getAttribute("data-id");
    // console.log(index)
    //获得slider插件对象
    var gallery = mui('.mui-slider');
    gallery.slider().gotoItem(index); //跳转到第index张图片，index从0开始；
});


// 帮我买
document.getElementById('tab-body1').addEventListener('tap',function(){
    var islogin = $(" input[ name='islogin' ] ").val();
    if(islogin==0){
        mui.openWindow({url:top.location.origin+'/index/user/login',id:'login',createNew:true})
    }else if(islogin==1){
        //console.log(this.getAttribute("data-id"));
        var id = this.getAttribute("data-id");
        mui.openWindow({
            url: top.location.origin+'/index/placeorder/helpbuystep1?id='+id,
            id: 'helpBuy-index',
            createNew:true
        });
    }else{
        setTimeout(function(){
            window.location.reload();//刷新当前页面.
        },1000);
    }
});
//帮我取
document.getElementById('tab-body2').addEventListener('tap',function(){
    var islogin = $(" input[ name='islogin' ] ").val();
    if(islogin==0){
        mui.openWindow({url:top.location.origin+'/index/user/login',id:'login',createNew:true})
    }else if(islogin==1){
        //console.log(this.getAttribute("data-id"));
        mui.openWindow({
            url: top.location.origin+'/index/placeorder/helptakestep1',
            id: 'helpTake-index',
            createNew:true
        });
    }else{
        setTimeout(function(){
            window.location.reload();//刷新当前页面.
        },1000);
    }

});

//帮我送
document.getElementById('tab-body3').addEventListener('tap',function(){
    var islogin = $(" input[ name='islogin' ] ").val();
    if(islogin==0){
        mui.openWindow({url:top.location.origin+'/index/user/login',id:'login',createNew:true})
    }else if(islogin==1){
        //console.log(this.getAttribute("data-id"));

        mui.openWindow({
            url: top.location.origin+'/index/placeorder/helpdeliverstep1',
            id: 'helpSend-index',
            createNew:true
        });
    }else{
        setTimeout(function(){
            window.location.reload();//刷新当前页面.
        },1000);
    }

});

//帮我办
document.getElementById('tab-body4').addEventListener('tap',function(){
    var islogin = $(" input[ name='islogin' ] ").val();
    if(islogin==0){
        mui.openWindow({url:top.location.origin+'/index/user/login',id:'login',createNew:true})
    }else if(islogin==1){
        //console.log(this.getAttribute("data-id"));
        var id = this.getAttribute("data-id");
        mui.openWindow({
            url: top.location.origin+'/index/placeorder/helpdostep1?id='+id,
            id: 'helpDo-index',
            createNew:true
        });
    }else{
        setTimeout(function(){
            window.location.reload();//刷新当前页面.
        },1000);
    }

});

//帮我排
document.getElementById('tab-body5').addEventListener('tap',function(){
    var islogin = $(" input[ name='islogin' ] ").val();
    if(islogin==0){
        mui.openWindow({url:top.location.origin+'/index/user/login',id:'login',createNew:true})
    }else if(islogin==1){
        //console.log(this.getAttribute("data-id"));
        var id = this.getAttribute("data-id");
        mui.openWindow({
            url: top.location.origin+'/index/placeorder/helplinestep1?id='+id,
            id: 'helpQueue-index',
            createNew:true
        });
    }else{
        setTimeout(function(){
            window.location.reload();//刷新当前页面.
        },1000);
    }
});