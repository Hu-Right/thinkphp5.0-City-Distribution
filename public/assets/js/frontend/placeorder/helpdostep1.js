mui.init();
mui.plusReady(function() {
    plus.storage.clear();//清除所有数据

    // 点击选择地址跳转页面
    var type = 0, // 服务类型
        send_con = ''; // 帮我送内容

    // 下一步
    document.getElementById('next-btn').addEventListener('tap', function() {
        var cp_index = document.getElementById('scroll').getElementsByClassName('mui-active')[0].innerText;// 产品类型
        var son_id = document.getElementById('scroll').getElementsByClassName('mui-active')[0].dataset.id;// 子分类id
        // console.log(cp_index) // 服务类型
        // console.log(son_id) // 服务类型

        // 此处为正确代码
        send_con = document.getElementById('textarea').value; // 帮我购买内容
        // console.log(send_con);

        if (send_con == '')
        {
            mui.alert('请先填写以上信息', '代排队');
        }else
        {
            plus.storage.setItem('content', cp_index+send_con);
            plus.storage.setItem('son_id', son_id);
            plus.storage.setItem('status', '5');

            mui.openWindow({
                url: top.location.origin+'/index/placeorder/helpdostep2',
                id: 'helpDo-index-next',
                createNew:true
            })
        }
    });
});