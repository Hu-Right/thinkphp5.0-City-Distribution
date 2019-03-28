mui.init()

// 拖动排序
var foo = document.getElementById('foo');
var sortable = Sortable.create(foo,{
    group: 'foo',
    animation: 100,
    handle:'.mui-icon-bars',
    // 拖拽开始
    onStart: function (/**Event*/evt) {

    },
    //  排序发生变化后的回调函数
    onUpdate: function (/**Event*/evt) {
        evt.oldIndex;  // 被拖拽元素拖拽前索引
        evt.newIndex;  // 拖拽后的新索引
        evt.item;  // 被拖拽的元素
        evt.from; // 拖拽后的页面html布局

        //console.log(evt.from.innerText);
        //console.log(evt);


        mui.ajax("/index/user/paymentsort",{
            type: 'post',
            data:{paymentsort:evt.from.innerText},
            dataType:'json',//服务器返回json格式数据
            type:'post',//HTTP请求类型
            success: function (data) {
                //console.log(data);
            },
            error: function (data) {
                layer.msg('修改失败');
                setTimeout(function(){
                    window.location.reload();//刷新当前页面.
                },1000);
            }
        });
    },
    // 拖拽结束
    onEnd: function (/**Event*/evt) {

    },
});