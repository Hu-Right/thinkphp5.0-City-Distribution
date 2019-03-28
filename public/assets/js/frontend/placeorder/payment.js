var wxChannel = null; // 微信支付
var aliChannel = null; // 支付宝支付
var channel = null;
mui.init({});

mui.plusReady(function() {
    // 获取支付通道
    plus.payment.getChannels(function(channels){
        aliChannel=channels[0];
        wxChannel=channels[1];
    },function(e){
        alert("获取支付通道失败："+e.message);
    });
})

// var ALIPAYSERVER='http://demo.dcloud.net.cn/helloh5/payment/alipay.php?total=0.01';
var ALIPAYSERVER=top.location.origin+'/index/payment/alipay?total=';
var WXPAYSERVER='http://demo.dcloud.net.cn/helloh5/payment/wxpay.php?total=';
// 2. 发起支付请求
function pay(id,money,type,orderdata){
    // 从服务器请求支付订单
    var PAYSERVER='';
    if(id=='alipay'){
        PAYSERVER=ALIPAYSERVER+money+'&tip='+type;
        PAYSERVER=ALIPAYSERVER+'0.01&tip='+type;
        channel = aliChannel;
    }else if(id=='wxpay'){
        PAYSERVER=WXPAYSERVER+money+'&tip='+type;
        PAYSERVER=ALIPAYSERVER+'0.01&tip='+type;
        channel = wxChannel;
    }else{
        plus.nativeUI.alert("不支持此支付通道！",null,"捐赠");
        return;
    }
    var xhr=new XMLHttpRequest();
    xhr.onreadystatechange=function(){
        switch(xhr.readyState){
            case 4:
                if(xhr.status==200){
                    plus.payment.request(channel,xhr.responseText,function(result){
                        // console.log(JSON.stringify(result));
                        var obj_rawdata = JSON.parse( result.rawdata );
                        // console.log(obj_rawdata);
                        var obj_result = JSON.parse(obj_rawdata.result);
                        // console.log(obj_result);
                        var total_amount = obj_result.alipay_trade_app_pay_response.total_amount;
                        // console.log(result.rawdata.result.alipay_trade_app_pay_response.total_amount);
                        plus.nativeUI.alert("支付成功！",function(){
                            //支付订单金额--更改订单状态，并根据结果跳转
                            var res = payMoney(total_amount);
                            // console.log(res);
                            // console.log(JSON.stringify(res));
                            Object.assign(orderdata, res.data);//将对象合并
                            // console.log(orderdata);
                            // console.log(JSON.stringify(orderdata));
                            placeAnOrder(orderdata);
                        });
                    },function(error){
                        // console.log(JSON.stringify(error));
                        var msg = getCaption(error.message);

                        plus.nativeUI.alert(msg);
                    });
                }else{
                    alert("获取订单信息失败！");
                }
                break;
            default:
                break;
        }
    }
    xhr.open('GET',PAYSERVER);
    xhr.send();
}


//截取出支付宝错误提示（{"message":"[Payment支付宝:6001]用户中途取消,http://ask.dcloud.net.cn/article/286","code":-100}），用户中途取消
function getCaption(obj){

    var str = obj.match(/](\S*),/)[1];

    // console.log(str);
    return str;
}


//支付订单金额（先充值，充值完以后扣余额）
function payMoney(money) {
    var result;
    mui.ajax('/index/payment/recharge',{
        data:{
            money:money
        },
        dataType:'json',//服务器返回json格式数据
        type:'post',//HTTP请求类型
        async : false,
        timeout:10000,//超时时间设置为10秒；
        success:function(data){
            // console.log(JSON.stringify(data));
            result = data;
        },
        error:function(xhr,type,errorThrown){
            //异常处理；
            console.log(type);
        }
    });
    return result;
}