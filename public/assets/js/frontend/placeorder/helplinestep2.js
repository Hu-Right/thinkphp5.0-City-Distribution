
mui.plusReady(function() {
    plus.storage.setItem('status', '8');

    var content = plus.storage.getItem('content'),
        start_address = plus.storage.getItem('start_address'),
        start_lon = plus.storage.getItem('start_lon'),
        start_lat = plus.storage.getItem('start_lat'),
        end_address = plus.storage.getItem('end_address'),
        end_lon = plus.storage.getItem('end_lon'),
        end_lat = plus.storage.getItem('end_lat'),
        name = plus.storage.getItem('name'),
        phone = plus.storage.getItem('phone'),
        status = plus.storage.getItem('status');
    // console.log('ads：'+ads);
    // console.log('content：'+content);
    // console.log('start_address：'+start_address);
    // console.log('start_lon：'+start_lon);
    // console.log('start_lat：'+start_lat);
    // console.log('end_address：'+end_address);
    // console.log('end_lon：'+end_lon);
    // console.log('end_lat：'+end_lat);
    // console.log('name：'+name);
    // console.log('phone：'+phone);
    // console.log('status：'+status);

    //回调计算距离--没有距离暂时注释
    /*var callBackFun = function(data){
        // console.log('计算里程');
        document.getElementById("mileage").value = parseFloat(data.distance);
        var user_min = data.d*24*60+data.h*60+data.m;
        document.getElementById("user_min").value = parseFloat(user_min);
        //console.log(JSON.stringify(data))
        // console.log(data.d);
        // console.log(data.h);
        // console.log(data.m);
        // console.log(data.distance);
        // console.log(user_min);
        //计算价格，更改页面的值
        price()
    }*/

    if(end_address != null){
        document.getElementById('select-ads').innerText = end_address;
    }

    //计算距离--没有距离暂时注释
    /*var myP1 = new BMap.Point(start_lon, start_lat); //起点
    var myP2 = new BMap.Point(end_lon,end_lat); //终点
    map1(myP1,myP2,callBackFun);*/

    price()//计算价格，更改页面的值

    //参数
    var data = {
        content:content,//订单内容
        start_address:start_address,//起始地址
        start_lon:start_lon,//起始经度
        start_lat:start_lat,//起始纬度
        end_address:end_address,//结束地址
        end_lon:end_lon,//结束经度
        end_lat:end_lat,//结束纬度
        name:name,//联系人
        phone:phone//电话
    }

    //判断结束地址是否为空
    document.getElementById('pay-btn').addEventListener('tap', function() {
        // console.log(1);
        var address = document.getElementById('select-ads').innerText;
        // 判断结束地址是否为空
        if(address.indexOf("选择地址") != -1 ){
            mui.alert('请选择收货地址', '提示');
            return false;
        }
        if(status == 8){
            place(data)
        }

        if(status == 14){
            place(data)//传输下单参数
        }
    });

    // 点击选择地址跳转页面
    document.getElementById('select-ads').addEventListener('tap', function() {
        window.location.href =  top.location.origin+'/index/address/selectaddress';
    });

    // 处理返回键
    mui.back = function(){
        //alert(pageUrl);
        var btnArray = ['返回首页', '继续发单'];
        var message = '<h6>返回首页后信息将被清除</h6>';
        mui.confirm(message, '是否取消发单？', btnArray, function(e) {
            if (e.index != 1) {
                popToTarget('home')
            }
        },'div');
    }

    /**
     * 从当前页面pop到目标页面
     * @param {String} targetId 目标页面ID
     */
    function popToTarget(targetId){
        //获取目标页面
        var target = plus.webview.getWebviewById(targetId);
        if (!target) {
            console.log("目标页面不存在！");
            return;
        }
        //获取当前页面
        var current = plus.webview.currentWebview();
        if (current === target) {
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
        console.log("目标页面不是当前页面的祖先页面！");
    }

});

// 选择时间
var select_time = document.getElementById('select-time');
select_time.addEventListener('tap', function() {
    var _html = event.target;

    // 选择时间
    var time_now = new Date();
    var now_h = time_now.getHours();
    // var now_i = time_now.getMinutes();
    //console.log(now_h)

    var data_h = [{
        value: '0',
        text: '立即排队'
    }];
    // 获取时列表
    for (var m = now_h + 1; m < 24; m++) {
        var _m = {
            value: m,
            text: m
        };
        data_h = data_h.concat(_m)
    }
    // console.log(data_h)

    var dtpicker = new mui.DtPicker({
        "type": "time",
        "customData": {
            "h": data_h,
            "i": [{
                value: " ",
                text: " "
            },
                {
                    value: "00",
                    text: "00"
                },
                {
                    value: "10",
                    text: "10"
                },
                {
                    value: "20",
                    text: "20"
                },
                {
                    value: "30",
                    text: "30"
                },
                {
                    value: "40",
                    text: "40"
                },
                {
                    value: "50",
                    text: "50"
                }
            ]
        }
    })
    dtpicker.show(function(e) {
        var time = '';
        if (e.h.text == '立即排队') {
            time = e.h.text;
        } else if (e.i.text == ' ') {
            time = e.y.text + '-' + e.m.text + '-' + e.d.text + ' ' + e.h.text + ':' + '00' + ':' + '00';
        } else {
            time = e.y.text + '-' + e.m.text + '-' + e.d.text + ' ' + e.h.text + ':' + e.i.text + ':' + '00';
        }
        _html.innerHTML = time;
        document.getElementById("service_date").value = time;
        document.getElementById("hour").value = e.h.text;
        price()
        // console.log(e)
        // console.log(time)
    })
});

// 选择时长---帮我排
var time_len = document.getElementById('time-len');
time_len.addEventListener('tap', function() {
    var _html = event.target;
    var data_d = [],
        data_h = [];
    for(var i=0; i<8; i++){
        var _d = {value: i*24*2,text: '0'+i}
        data_d = data_d.concat(_d);
    }
    for(var i=0; i<24; i++){
        var _h = ';'
        if(i<10){
            _h = {value: i*2,text: '0'+i}
        }else{
            _h = {value: i*2,text: i}
        }

        data_h = data_h.concat(_h);
    }
    var dtpicker = new mui.DtPicker({
        "type": "day",
        "customData": {
            "d": data_d,
            "h": data_h,
            "i": [
                { value: 1, text: "00" },
                { value: 1, text: "30" }
            ]
        }
    })
    dtpicker.show(function(e) {
        var time = e.d.text + '天' + e.h.text +'小时' + e.i.text +'分钟';
        if(e.d.text == '00' && e.h.text == '00' && e.i.text == '00'){
            time = '30分钟';
        }
        _html.innerHTML = time;
        document.getElementById('line_time').value = e.d.value+e.h.value+e.i.value;
        price()
        //console.log(e);
    })
});

//下单传ajax
function place(data){
    // 立即下单---选择支付方式
    var pay_pop = document.getElementById('pay-pop');
    pay_pop.style.display = 'block';

// 取消支付(关闭下单弹窗)
    document.getElementById('cancel-pay').addEventListener('tap', function() {
        pay_pop.style.display = 'none';
    });

// 确定支付
    document.getElementById('sure-pay').addEventListener('tap', function() {
        pay_pop.style.display = 'none';

        var fee = document.getElementById("fee").value;//小费
        var line_time = document.getElementById('line_time').value;
        var linetime_text = document.getElementById('time-len').getElementsByTagName('a')[0].innerHTML;
        var service_date = document.getElementById("service_date").value;
        var payment=document.getElementsByName("payment");
        var paymentvalue='';   //  selectvalue为radio中选中的值
        for(var i=0;i<payment.length;i++){
            if(payment[i].checked==true) {
                paymentvalue = payment[i].value;
                break;
            }
        }
        var money = document.getElementById("price").innerText;//订单金额
        var remake = document.getElementById("remake").value;
        // mui.alert('订单内容'+data.content);
        // mui.alert('起始地址'+data.start_address);
        // mui.alert('起始经度'+data.start_lon);
        // mui.alert('起始纬度'+data.start_lat);
        // mui.alert('结束地址'+data.end_address);
        // mui.alert('结束经度'+data.end_lon);
        // mui.alert('结束纬度'+data.end_lat);
        // mui.alert('联系人'+data.name);
        // mui.alert('联系电话'+data.phone);
        // mui.alert('排队时长数值'+line_time);
        // mui.alert('预约时间'+service_date);
        // mui.alert('排队时长文本'+linetime_text);
        // mui.alert('支付方式'+paymentvalue);
        var orderdata = {
            details:data.content,
            line_time:line_time,
            linetime_text:linetime_text,
            service_date:service_date,
            start_address:data.start_address,
            start_lon:data.start_lon,
            start_lat:data.start_lat,
            end_address:data.end_address,
            end_lon:data.end_lon,
            end_lat:data.end_lat,
            linkname:data.name,
            mobile:data.phone,
            fee:fee,//小费
            payment:paymentvalue,
            remake:remake,
            total:money//订单金额
        }

        //不同支付方式走不通流程
        if(paymentvalue.indexOf("支付宝") != -1 ){
            // console.log('支付宝');
            pay('alipay',money,'帮我买',orderdata);
        }
        if(paymentvalue.indexOf("微信") != -1 ){
            console.log('微信');
            // pay('wxpay',money,'帮我买');
            mui.alert('请使用其他支付方式','即将开通');
        }
        if(paymentvalue.indexOf("余额") != -1 ){
            // console.log(JSON.stringify(orderdata));
            placeAnOrder(orderdata);
        }
    });
}

//下单
function placeAnOrder(orderdata){
    mui.ajax('/index/placeorder/helpline',{
        type: 'post',
        data:orderdata,
        dataType:'json',//服务器返回json格式数据
        success: function (data) {
            // console.log(JSON.stringify(data));
            //alert(111)
            layer.msg(data.msg);
            setTimeout(function(){
                mui.openWindow({
                    url:top.location.origin+data.url
                })
            },1000);

        },
        error: function (xhr,type,errorThrown) {
            // console.log(xhr.readyState);
            setTimeout(function(){
                window.location.reload();//刷新当前页面.
            },1000);
        }
    });
}

//价格计算
function price(){

    var weather = parseFloat(document.getElementById("weather").value);
    var subscription_price = parseFloat(document.getElementById("subscription_price").value);
    var fee = parseFloat(document.getElementById("fee").value);
    var service_date = document.getElementById("service_date").value;
    var is_weather = document.getElementById("is_weather").value;
    var hour = document.getElementById("hour").value;
    var front_night = parseFloat(document.getElementById("front_night").value);
    var back_night = parseFloat(document.getElementById("back_night").value);
    var line_time = parseFloat(document.getElementById("line_time").value);
    var lineup_start_price = parseFloat(document.getElementById("lineup_start_price").value);
    var lineup_one_ten_price = parseFloat(document.getElementById("lineup_one_ten_price").value);
    var lineup_delayed_price = parseFloat(document.getElementById("lineup_delayed_price").value);

    var money = lineup_start_price;
    //时间价格
    if(line_time==1){
        money = lineup_start_price;
    }else if(1<line_time&&line_time<=20){
        money = lineup_one_ten_price*(line_time-1)+lineup_start_price;
    }else if(20<line_time){
        money = (line_time-20)*lineup_delayed_price+(lineup_one_ten_price*19)+lineup_start_price;
    }
    //预约加价
    if(service_date.indexOf('立即') == -1){
        //console.log(1)
        money += subscription_price;
    }
    //时段加价
    if(22<hour&&hour<24){
        money += front_night;
    }else if(0<hour&&hour<6){
        money += back_night;
    }
    //天气加价
    if(is_weather == 1){
        money += weather;
    }
    //小费
    if(!isNaN(fee)){
        money += fee;
    }
    //console.log(service_date);
    document.getElementById("price").innerHTML = (money.toFixed(2));

}

//跳转到费用明细
document.getElementById('cost').addEventListener('tap', function() {
    var type = 4,
        line_time = parseFloat(document.getElementById('line_time').value),
        service_date = document.getElementById('service_date').value,
        fee = parseFloat(document.getElementById('fee').value),
        hour = parseFloat(document.getElementById('hour').value);

    if(isNaN(fee)){
        fee = 0;
    }
    //console.log(type);
    //console.log(mileage);
    //console.log(prepayment);

    mui.openWindow({
        url: top.location.origin+'/index/placeorder/expenseDetail',
        id: 'expenseDetail',
        extras: {
            type: type,
            service_date: service_date,
            line_time: line_time,
            fee: fee,
            hour: hour
        },
        createNew:true
    })
});
