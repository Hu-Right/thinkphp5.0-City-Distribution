mui.init();


// 接单计时
if(document.getElementById("sec")&&document.getElementById('min')){
    var m = document.getElementById('min').innerText;
    var s = document.getElementById('sec').innerText;
    var timer;
    //console.log(m)
    //console.log(s)
    function CountDown() {
        if (s < 59)
        {
            s++;
            if(s<10){
                document.getElementById('sec').innerText = '0'+s;
            }else{
                document.getElementById('sec').innerText = s;
            }
        }else{
            s = 0;
            document.getElementById('sec').innerText = '0'+s;
            m++;
            if(m<10){
                document.getElementById('min').innerText = '0'+m;
            }else{
                document.getElementById('min').innerText = m;
            }
            //document.getElementById('min').innerText = m;
            if(m>=15){
                //if(m>=1){
                clearInterval(timer);
                document.getElementById('time').innerText = '等待超时';
                var btnArray = ['取消订单', '继续等待'];
                mui.confirm('', '暂无跑男接单', btnArray, function(e) {
                    if (e.index == 1) {
                        // 继续等待

                    } else {
                        // 取消订单
                        var id = document.getElementById('id').value;
                        cancel(id);
                    }
                })
            }
        }
    }
    timer = setInterval("CountDown()", 1000);
}

mui.plusReady(function() {
    plus.storage.clear();//清除所有数据

    // 地图
    var order_type = document.getElementById('order_type').value;//订单类型
    var start_lon = document.getElementById('start_lon').value;
    var start_lat = document.getElementById('start_lat').value;
    var end_lon = document.getElementById('end_lon').value;
    var end_lat = document.getElementById('end_lat').value;

    // console.log(start_lon);
    // console.log(start_lat);
    // console.log(end_lon);
    // console.log(end_lat);
    if(end_lon == null || end_lon == '' || end_lat == null || end_lat == '')
    {
        // console.log(1);
        if(order_type==1){
            var myP = new BMap.Point(start_lon,start_lat); //终点
            map3('shou',myP);
        }
        else if(order_type==3)
        {
            var myP = new BMap.Point(start_lon,start_lat); //终点
            map3('ban',myP);
        }
        else if(order_type==4)
        {
            var myP = new BMap.Point(start_lon,start_lat); //终点
            map3('pai',myP);
        }
    }
    else
    {
        // console.log(order_type);
        if(order_type==1)
        {
            // var myP1 = new BMap.Point(113.712092,34.769777); //起点
            // var myP2 = new BMap.Point(113.715928,34.76868); //终点
            var myP1 = new BMap.Point(start_lon,start_lat); //起点
            var myP2 = new BMap.Point(end_lon,end_lat); //终点
            map1('mai','shou',myP1,myP2,callBackFunction);

            //回调计算距离
            var callBackFunction = function(data){
                console.log(data);
            }
        }
        else if (order_type == 2)
        {
            var myP1 = new BMap.Point(start_lon,start_lat); //起点
            var myP2 = new BMap.Point(end_lon,end_lat); //终点
            map1('fa','shou',myP1,myP2,callBackFunction);

            //回调计算距离
            var callBackFunction = function(data){
                //console.log(data);
            }
        }
        else if (order_type == 5)
        {

            var myP1 = new BMap.Point(start_lon,start_lat); //起点
            var myP2 = new BMap.Point(end_lon,end_lat); //终点
            map1('qu','shou',myP1,myP2,callBackFunction);

            //回调计算距离
            var callBackFunction = function(data){
                //console.log(data);
            }
        }
    }

    //取消订单
    if(document.getElementById("cancel")){
        document.getElementById("cancel").addEventListener('tap',function(){
            var id = document.getElementById('id').value;
            cancel(id);
        })
    }

    //去评价
    if(document.getElementById("evaluate")){
        document.getElementById("evaluate").addEventListener('tap',function(){
            var id = document.getElementById('id').value;
            mui.openWindow({url:top.location.origin+'/index/order/evaluate?id='+id,id:'evaluate'})
        })
    }

    //再来一单
    if(document.getElementById("again")){
        document.getElementById("again").addEventListener('tap',function(){
            var order_type = document.getElementById('order_type').value;
            var order_id = document.getElementById('id').value;
            // console.log(order_type);

            if(order_type == 1){
                // console.log(order_id);
                var orderInfo = JSON.parse(getOrderInfo(order_id));
                // console.log(orderInfo);
                // console.log(JSON.stringify(orderInfo));
                // console.log(JSON.stringify(orderInfo.content));
                // console.log(orderInfo.name);
                // console.log(orderInfo.mobile);

                plus.storage.setItem('ads', orderInfo.end_address);//传往second.html的值
                plus.storage.setItem('content', orderInfo.content.details);
                plus.storage.setItem('start_address', orderInfo.start_address);//起始地址
                plus.storage.setItem('start_lon', orderInfo.start_lon);//起始经度
                plus.storage.setItem('start_lat', orderInfo.start_lat);//起始纬度
                plus.storage.setItem('end_address', orderInfo.end_address);//结束地址
                plus.storage.setItem('end_lon', orderInfo.end_lon);//结束经度
                plus.storage.setItem('end_lat', orderInfo.end_lat);//结束纬度
                plus.storage.setItem('name', orderInfo.content.name);//联系人
                plus.storage.setItem('phone', orderInfo.content.mobile);//联系电话
                plus.storage.setItem('mileage', orderInfo.start_end_distance);//位置距离
                plus.storage.setItem('status', 11);//帮我买-再来一单

                mui.openWindow({
                    url:top.location.origin+'/index/placeorder/helpbuystep2'
                })
            }
            else if(order_type == 2){
                // console.log(order_id);
                var orderInfo = JSON.parse(getOrderInfo(order_id));
                // console.log(orderInfo);
                // console.log(JSON.stringify(orderInfo));
                // console.log(JSON.stringify(orderInfo.content));
                // console.log(orderInfo.name);
                // console.log(orderInfo.mobile);

                plus.storage.setItem('ads', orderInfo.end_address);//传往second.html的值
                plus.storage.setItem('content', orderInfo.content.details);
                plus.storage.setItem('start_address', orderInfo.start_address);//起始地址
                plus.storage.setItem('start_lon', orderInfo.start_lon);//起始经度
                plus.storage.setItem('start_lat', orderInfo.start_lat);//起始纬度
                plus.storage.setItem('s_name', orderInfo.name);//起始联系人
                plus.storage.setItem('s_phone', orderInfo.mobile);//起始联系电话
                plus.storage.setItem('end_address', orderInfo.end_address);//结束地址
                plus.storage.setItem('end_lon', orderInfo.end_lon);//结束经度
                plus.storage.setItem('end_lat', orderInfo.end_lat);//结束纬度
                plus.storage.setItem('e_name', orderInfo.content.name);//结束联系人
                plus.storage.setItem('e_phone', orderInfo.content.mobile);//结束联系电话
                plus.storage.setItem('mileage', orderInfo.start_end_distance);//位置距离
                plus.storage.setItem('status', 12);//帮我送-再来一单

                mui.openWindow({
                    url:top.location.origin+'/index/placeorder/helpdeliverstep2'
                })
            }
            else if(order_type == 3){
                // console.log(order_id);
                var orderInfo = JSON.parse(getOrderInfo(order_id));
                // console.log(orderInfo);
                // console.log(JSON.stringify(orderInfo));
                // console.log(JSON.stringify(orderInfo.content));
                // console.log(orderInfo.name);
                // console.log(orderInfo.mobile);

                plus.storage.setItem('ads', orderInfo.end_address);//传往second.html的值
                plus.storage.setItem('content', orderInfo.content.details);
                plus.storage.setItem('start_address', orderInfo.start_address);//起始地址
                plus.storage.setItem('start_lon', orderInfo.start_lon);//起始经度
                plus.storage.setItem('start_lat', orderInfo.start_lat);//起始纬度
                plus.storage.setItem('end_address', orderInfo.end_address);//结束地址
                plus.storage.setItem('end_lon', orderInfo.end_lon);//结束经度
                plus.storage.setItem('end_lat', orderInfo.end_lat);//结束纬度
                plus.storage.setItem('name', orderInfo.content.name);//联系人
                plus.storage.setItem('phone', orderInfo.content.mobile);//联系电话
                plus.storage.setItem('son_id', orderInfo.content.son_id);//帮我办子分类id
                plus.storage.setItem('status', 13);//帮我办-再来一单

                mui.openWindow({
                    url:top.location.origin+'/index/placeorder/helpdostep2'
                })
            }
            else if(order_type == 4){

                // console.log(order_id);
                var orderInfo = JSON.parse(getOrderInfo(order_id));
                // console.log(orderInfo);
                // console.log(JSON.stringify(orderInfo));
                // console.log(JSON.stringify(orderInfo.content));
                // console.log(orderInfo.name);
                // console.log(orderInfo.mobile);

                plus.storage.setItem('ads', orderInfo.end_address);//传往second.html的值
                plus.storage.setItem('content', orderInfo.content.details);
                plus.storage.setItem('start_address', orderInfo.start_address);//起始地址
                plus.storage.setItem('start_lon', orderInfo.start_lon);//起始经度
                plus.storage.setItem('start_lat', orderInfo.start_lat);//起始纬度
                plus.storage.setItem('end_address', orderInfo.end_address);//结束地址
                plus.storage.setItem('end_lon', orderInfo.end_lon);//结束经度
                plus.storage.setItem('end_lat', orderInfo.end_lat);//结束纬度
                plus.storage.setItem('name', orderInfo.content.name);//联系人
                plus.storage.setItem('phone', orderInfo.content.mobile);//联系电话
                plus.storage.setItem('status', 14);//帮我排-再来一单

                mui.openWindow({
                    url:top.location.origin+'/index/placeorder/helplinestep2'
                })

            }
            else if(order_type == 5){
                // console.log(order_id);
                var orderInfo = JSON.parse(getOrderInfo(order_id));
                // console.log(orderInfo);
                // console.log(JSON.stringify(orderInfo));
                // console.log(JSON.stringify(orderInfo.content));
                // console.log(orderInfo.name);
                // console.log(orderInfo.mobile);

                plus.storage.setItem('ads', orderInfo.end_address);//传往second.html的值
                plus.storage.setItem('content', orderInfo.content.details);
                plus.storage.setItem('start_address', orderInfo.start_address);//起始地址
                plus.storage.setItem('start_lon', orderInfo.start_lon);//起始经度
                plus.storage.setItem('start_lat', orderInfo.start_lat);//起始纬度
                plus.storage.setItem('s_name', orderInfo.name);//起始联系人
                plus.storage.setItem('s_phone', orderInfo.mobile);//起始联系电话
                plus.storage.setItem('end_address', orderInfo.end_address);//结束地址
                plus.storage.setItem('end_lon', orderInfo.end_lon);//结束经度
                plus.storage.setItem('end_lat', orderInfo.end_lat);//结束纬度
                plus.storage.setItem('e_name', orderInfo.content.name);//结束联系人
                plus.storage.setItem('e_phone', orderInfo.content.mobile);//结束联系电话
                plus.storage.setItem('mileage', orderInfo.start_end_distance);//位置距离
                plus.storage.setItem('status', 15);//帮我买-再来一单

                mui.openWindow({
                    url:top.location.origin+'/index/placeorder/helptakestep2'
                })
            }

            // mui.openWindow({url:top.location.origin+'/index/user/index'})
        })
    }


    //取消订单
    function cancel(id){
        // console.log(id);
        // return false;
        mui.ajax("/index/placeorder/cancel",{
            type: 'post',
            data:{id:id},
            dataType:'json',//服务器返回json格式数据
            type:'post',//HTTP请求类型
            success: function (data) {
                // console.log(data);
                mui.toast(data.msg);
                //当前页面有ajax轮询，状态一边就会刷新下边的跳转可以屏蔽
                /*setTimeout(function(){
                    window.location.href = data.url;//跳转
                },1000);*/
            },
            error: function (xhr,type,errorThrown) {
                //console.log(xhr.readyState);
                mui.toast('取消失败');
                setTimeout(function(){
                    window.location.reload();//刷新当前页面.
                },1000);
            }
        });
    }

    //再来一单获取订单参数
    function getOrderInfo(id){
        //console.log(id);
        var orderInfo;
        mui.ajax("/index/placeorder/getOrderInfo",{
            type: 'post',
            data:{id:id},
            dataType:'json',//服务器返回json格式数据
            async:false,
            type:'post',//HTTP请求类型
            success: function (data) {
                // console.log(data);
                // console.log(JSON.stringify(data));
                orderInfo = JSON.stringify(data.data);
            },
            error: function (xhr,type,errorThrown) {
                // console.log(xhr.readyState);
                setTimeout(function(){
                    window.location.reload();//刷新当前页面.
                },1000);
            }
        });
        return orderInfo;
    }
    // 拨打电话
    if(document.getElementById('phone-one')){
        var phoneOne = document.getElementById('phone-one').innerText;
        document.getElementById("tag-one").addEventListener('tap', function() {
            plus.device.dial(phoneOne, false);
        });
    }
    if(document.getElementById('phone-two')){
        var phoneTwo =  document.getElementById('phone-two').innerText;
        document.getElementById("tag-two").addEventListener('tap', function() {
            plus.device.dial(phoneTwo, false);
        });
    }
    if(document.getElementById('phone-three')){
        var phoneThree =  document.getElementById('phone-three').value;
        document.getElementById("tag-three").addEventListener('tap', function() {
            plus.device.dial(phoneThree, false);
        });
    }

    //返回主页
    document.getElementById("selfBack").addEventListener('tap', function() {
        // alert(1);
        popToTarget('home')
    });
    /**
     * 从当前页面pop到目标页面
     * @param {String} targetId 目标页面ID
     */
    function popToTarget(targetId){
        //获取目标页面
        var target = plus.webview.getWebviewById(targetId);
        // alert(target);
        if (!target) {
            // alert("目标页面不存在！");
            console.log("目标页面不存在！");
            return;
        }
        //获取当前页面
        var current = plus.webview.currentWebview();
        if (current === target) {
            // alert("目标页面是当前页面！");
            console.log("目标页面是当前页面！");
            return;
        }
        //将要关闭的页面
        var pages = new Array(current);
        //父级页面
        var opener = current.opener();
        while (opener){
            if (opener === target) {//找到了目标页面
                //关闭目标页面的所有子级页面pages
                pages.map(function(page){
                    page.close();
                });
                return;
            }
            pages.push(opener);
            opener = opener.opener();
        }
        //没有找到目标页面
        // alert("目标页面不是当前页面的祖先页面！");
        console.log("目标页面不是当前页面的祖先页面！");
    }
});


