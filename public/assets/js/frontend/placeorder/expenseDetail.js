//天气
var is_weather = document.getElementById("is_weather").value;
var weather = parseFloat(document.getElementById("weather").value);
//里程
var two_km = parseFloat(document.getElementById("two_km").value);
var three_km = parseFloat(document.getElementById("three_km").value);
var three_ten_km = parseFloat(document.getElementById("three_ten_km").value);
var ten_thirty_km = parseFloat(document.getElementById("ten_thirty_km").value);
var over_thirty_km = parseFloat(document.getElementById("over_thirty_km").value);
//重量
var lower_twentyfive_kg = parseFloat(document.getElementById("lower_twentyfive_kg").value);
var twentysix_thirty_kg = parseFloat(document.getElementById("twentysix_thirty_kg").value);
var thirtyone_forty_kg = parseFloat(document.getElementById("thirtyone_forty_kg").value);
var over_forty_kg = parseFloat(document.getElementById("over_forty_kg").value);
//排队时长
var lineup_start_price = parseFloat(document.getElementById("lineup_start_price").value);
var lineup_one_ten_price = parseFloat(document.getElementById("lineup_one_ten_price").value);
var lineup_delayed_price = parseFloat(document.getElementById("lineup_delayed_price").value);
//时段
var front_night = parseFloat(document.getElementById("front_night").value);
var back_night = parseFloat(document.getElementById("back_night").value);
//预约费
var subscription_price = parseFloat(document.getElementById("subscription_price").value);
//总金额
var total = 0;




// openwindow 传参
mui.plusReady(function() {
    // 接收父级页面传的参数
    var self = plus.webview.currentWebview();
    //alert(JSON.stringify(self));
    var type = self.type;	// 订单类型
    document.getElementById('type').value = type;
    //帮我买费用明细
    if(type == 1){
        var mileage = self.mileage,	// 里程
            service_date = self.service_date,//预约
            hour = self.hour,//时段
            prepayment = self.prepayment,//预付款
            fee = self.fee;//小费
        // console.log('type:'+type);
        // console.log('mileage:'+mileage);
        // console.log('service_date:'+service_date);
        // console.log('hour:'+hour);
        // console.log('prepayment:'+prepayment);
        // console.log('fee:'+fee);

        total = km(mileage);
        total = tq_yy_sd(total, service_date, hour);
        total = yf(total,prepayment);
        total = xf(total,fee);

        // console.log(total);

        document.getElementById("total").innerText = total.toFixed(2);

    }
    //帮我取/送费用明细
    if(type == 2 || type == 5){
        var mileage = self.mileage,	// 里程
            service_date = self.service_date,//预约
            fee = self.fee,//小费
            premium = self.premium,//保价
            hour = self.hour,//时段
            weight = self.weight;//重量
        // console.log('type:'+type);
        // console.log('mileage:'+mileage);
        // console.log('hour:'+hour);
        // console.log('prepayment:'+prepayment);
        // console.log('fee:'+fee);
        total = km(mileage);
        total = kg(total, weight);
        total = tq_yy_sd(total, service_date, hour);
        total = xf(total,fee);
        total = bj(total,premium);
        document.getElementById("total").innerText = total.toFixed(2);
        //console.log(total);
    }
    //帮我办费用明细
    if(type == 3){
        var service_price = parseFloat(self.service_price),	//服务价格
            service_date = self.service_date,//预约
            fee = self.fee,//小费
            hour = self.hour;//时段
        var table = document.body.querySelector('.mui-table-view');
        var li = document.createElement('li');
        li.className = 'mui-table-view-cell';
        li.innerHTML =
            '<p class="left">上门费用</p>' +
            '<p class="right">'+ (parseFloat(service_price)).toFixed(2) +'元</p>';
        table.appendChild(li);
        total = tq_yy_sd(service_price, service_date, hour);
        total = xf(total,fee);
        document.getElementById("total").innerText = (parseFloat(total)).toFixed(2);
    }
    //代排队费用明细
    if(type == 4){
        var line_time = self.line_time,	//排队时长
            service_date = self.service_date,//预约
            fee = self.fee,//小费
            hour = self.hour;//时段
        var table = document.body.querySelector('.mui-table-view');
        var li = document.createElement('li');
        li.className = 'mui-table-view-cell';
        li.innerHTML =
            '<p class="left">起步价（30分钟以内）</p>' +
            '<p class="right">'+ lineup_start_price.toFixed(2) +'元</p>';
        table.appendChild(li);
        total = line(line_time);
        total = tq_yy_sd(total, service_date, hour);
        total = xf(total,fee);
        document.getElementById("total").innerText = total.toFixed(2);
    }
});
//公里费用
function km(mileage){
    var money = 0;
    if(0<Math.ceil(mileage)&&Math.ceil(mileage)<=2){
        money = two_km;
    }else if(2<Math.ceil(mileage)&&Math.ceil(mileage)<=3){
        money = three_km;
    }else if(3<Math.ceil(mileage)&&Math.ceil(mileage)<=10){
        money = (Math.ceil(mileage)-3)*three_ten_km+three_km;
    }else if(10<Math.ceil(mileage)&&Math.ceil(mileage)<=30){
        money = (Math.ceil(mileage)-10)*ten_thirty_km+(10-3)*three_ten_km+three_km;
    }else if(30<Math.ceil(mileage)){
        money = (Math.ceil(mileage)-30)*over_thirty_km+(30-10)*ten_thirty_km+(10-3)*three_ten_km+three_km;
    }
    var table = document.body.querySelector('.mui-table-view');
    var li = document.createElement('li');
    li.className = 'mui-table-view-cell';
    li.innerHTML =
        '<p class="left">订单距离：'+ mileage +' 公里</p>' +
        '<p class="right">'+ money.toFixed(2) +'元</p>';
    table.appendChild(li);
    return money;
}
//公斤费用
function kg(money, weight){
    var price = money;
    var html = '';
    if(25<weight&&weight<=30){
        money += twentysix_thirty_kg;
        html = '25-30公斤（每单加）';
    }else if(30<weight&&weight<=40){
        money += thirtyone_forty_kg;
        html = '30-40公斤（每单加）';
    }else if(40<weight){
        money += thirtyone_forty_kg+(weight-40)*over_forty_kg;
        html = '40公斤以上（1公斤/元）';
    }else if(weight<=25){
        html = '25公斤以内（每单加）';
    }
    var table = document.body.querySelector('.mui-table-view');
    var li = document.createElement('li');
    li.className = 'mui-table-view-cell';
    li.innerHTML =
        '<p class="left">物品重量：'+ html +' </p>' +
        '<p class="right">'+ (money - price).toFixed(2) +'元</p>';
    table.appendChild(li);
    return money;
}
//排队时长费用
function line(line_time){
    var html = '';
    var money = 0;
    if(line_time == 1){
        money = lineup_start_price;
    }else if(1<line_time&&line_time<=20){
        money = (line_time-1)*lineup_one_ten_price;
    }else if(20<line_time){
        money = (line_time-20)*lineup_delayed_price+(lineup_one_ten_price*19);
    }
    if((line_time-1)%48 != 0){
        if(((line_time-1)%48)%2 != 0){
            html = Math.floor((line_time-1)/48) + '天'+ Math.floor(((line_time-1)%48)/2) +'小时30分钟';
        }else{
            html = Math.floor((line_time-1)/48) + '天'+ ((line_time-1)%48)/2 +'小时0分钟';
        }
    }else{
        html = ((line_time-1)/48) + '天0小时0分钟';
    }

    if(line_time != 1){

        var table = document.body.querySelector('.mui-table-view');
        var li = document.createElement('li');
        li.className = 'mui-table-view-cell';
        li.id = 'cost';
        li.innerHTML =
            '<p class="left">费用（'+ html +'）</p>' +
            '<p class="right">'+ money.toFixed(2) +'元</p>';
        table.appendChild(li);
        return money + lineup_start_price;
    }else{
        var li = document.createElement('li');
        li.id = 'cost';
        li.innerHTML ='';
        return money;
    }

}
//天气、预约、时段费用
function tq_yy_sd(money, service_date, hour){
    //天气
    if(is_weather == 1){
        money += weather;
        var table = document.body.querySelector('.mui-table-view');
        var li = document.createElement('li');
        li.className = 'mui-table-view-cell';
        li.innerHTML =
            '<p class="left">特殊天气（每单加）</p>' +
            '<p class="right">'+ weather.toFixed(2) +'元</p>';
        table.appendChild(li);
    }
    //预约
    if(service_date.indexOf('立即') == -1){
        //console.log(1)
        money += subscription_price;
        var table = document.body.querySelector('.mui-table-view');
        var li = document.createElement('li');
        li.className = 'mui-table-view-cell';
        li.innerHTML =
            '<p class="left">预约费用（每单加）</p>' +
            '<p class="right">'+ subscription_price.toFixed(2) +'元</p>';
        table.appendChild(li);
    }
    //时段
    if(22<hour&&hour<24){
        money += front_night;
        var table = document.body.querySelector('.mui-table-view');
        var li = document.createElement('li');
        li.className = 'mui-table-view-cell';
        li.innerHTML =
            '<p class="left">深夜时段（每单加）</p>' +
            '<p class="right">'+ front_night.toFixed(2) +'元</p>';
        table.appendChild(li);
    }else if(0<hour&&hour<6){
        money += back_night;
        var table = document.body.querySelector('.mui-table-view');
        var li = document.createElement('li');
        li.className = 'mui-table-view-cell';
        li.innerHTML =
            '<p class="left">凌晨时段（每单加）</p>' +
            '<p class="right">'+ back_night.toFixed(2) +'元</p>';
        table.appendChild(li);
    }
    return money;
}
//预付费用
function yf(money,prepayment){

    /*console.log('money:'+money);
    console.log('prepayment:'+prepayment);
    return money;*/

    money += parseFloat(prepayment);
    var table = document.body.querySelector('.mui-table-view');
    var li = document.createElement('li');
    li.className = 'mui-table-view-cell';
    li.innerHTML =
        '<p class="left">商品费用</p>' +
        '<p class="right">'+ parseFloat(prepayment).toFixed(2) +'元</p>';
    table.appendChild(li);
    return money;
}
//小费
function xf(money,fee){

    /*console.log('money:'+money);
    console.log('fee:'+fee);
    return money;*/

    money += parseFloat(fee);
    var table = document.body.querySelector('.mui-table-view');
    var li = document.createElement('li');
    li.className = 'mui-table-view-cell';
    li.innerHTML =
        '<p class="left">小费/服务费用</p>' +
        '<p class="right">'+ parseFloat(fee).toFixed(2) +'元</p>';
    table.appendChild(li);
    return money;
}
//保价
function bj(money,baojia){

    /*console.log('money:'+money);
    console.log('fee:'+fee);
    return money;*/

    money += parseFloat(baojia);
    var table = document.body.querySelector('.mui-table-view');
    var li = document.createElement('li');
    li.className = 'mui-table-view-cell';
    li.innerHTML =
        '<p class="left">保价</p>' +
        '<p class="right">'+ parseFloat(baojia).toFixed(2) +'元</p>';
    table.appendChild(li);
    return money;
}
