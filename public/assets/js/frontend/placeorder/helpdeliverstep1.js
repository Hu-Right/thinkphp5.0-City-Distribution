mui.init();
mui.plusReady(function() {
    plus.storage.clear();//清除所有数据

    // 点击选择地址跳转页面
    var send_con = ''; // 帮我送内容

    // 选择发货地址
    document.getElementById('select-ads').addEventListener('tap', function() {
        getMsg();
    });

    // 下一步
    document.getElementById('next-btn').addEventListener('tap', function() {
        send_con = document.getElementById('textarea').value; // 帮我购买内容
        // console.log(send_con);
        if (send_con != '') {
            mui.alert('请选择发货地址', '帮我送');
        } else {
            mui.alert('请先填写以上信息', '帮我送');
        }
    });


    function getMsg() {

        // 此处为正确代码
        send_con = document.getElementById('textarea').value; // 帮我购买内容
        // console.log(send_con);
        if (send_con != '') {
            plus.storage.setItem('content', send_con);
            plus.storage.setItem('status', '3');
            mui.openWindow({
                url: top.location.origin+'/index/address/selectaddress',
                id: 'select-ads-index',
                createNew:true
            });
        } else {
            mui.alert('请先填写以上信息', '帮我送');
        }
    }

});