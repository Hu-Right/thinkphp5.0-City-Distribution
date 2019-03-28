mui.init();

setInterval(push,2000);

// push()

//刷新页面
function push(){

    var status = parseInt($("#status").val());
    var id = parseInt($("#id").val());
    if(status == 1){
        var code = $(".con").html().trim();
    }

    // console.log(code);
    // return false;
    ajaxPush(id, status, code)


}

//订单详情页ajax刷新
function ajaxPush(id, status, code){
    mui.ajax("/index/order/ajaxPushDetail",{
        type: 'post',
        data:{id:id,status:status,code:code},
        dataType:'json',//服务器返回json格式数据
        type:'post',//HTTP请求类型
        success: function (data) {
            // console.log(data);
            if(data.code == 1){
                window.location.reload();//刷新当前页面.
            }
        },
        error: function (xhr,type,errorThrown) {
            //console.log(xhr.readyState);

        }
    });
}


