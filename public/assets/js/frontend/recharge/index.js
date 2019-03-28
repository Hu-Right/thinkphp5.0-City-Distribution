mui.init()

var ul = document.getElementById("ul");
//console.log(ul)
var aList = ul.getElementsByTagName('a');

for (var i = 0; i < aList.length; i++) {
    aList[i].addEventListener('tap', function() {
        for (var j = 0; j < aList.length; j++) {
            //先清空所有的样式
            aList[j].className = 'recharge-border';
        }
        //给当前的设置样式
        this.className = 'border-active';
        //更改金额
        // console.log(this.getAttribute('data-money'));
        document.getElementById('money').value = this.getAttribute('data-money');
    });
}

// 支付方式
var list = document.querySelector('.mui-table-view.mui-table-view-radio');
list.addEventListener('selected', function(e) {
    // console.log("当前选中的为：" + e.detail.el.innerText);
    document.getElementById('payment').value = e.detail.el.innerText;
});

// 去支付
document.getElementById("goBtn").addEventListener('tap',function(){
    var payment = document.getElementById('payment').value;
    var money = document.getElementById('money').value;
    if(payment.indexOf("支付宝") != -1 ){
        console.log('支付宝');
        pay('alipay',money,'充值');
    }else if(payment.indexOf("微信") != -1 ){
        console.log('微信');
        // pay('wxpay',money,'充值');
        mui.alert('请使用其他支付方式','即将开通');
    }
})

// 充值协议
document.getElementById("czxy").addEventListener('tap',function(){
    // console.log(1);
    mui.openWindow(top.location.origin+'/index/recharge/agreement?title=充值协议&id=8');
})