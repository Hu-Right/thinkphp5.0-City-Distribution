mui.init();

setInterval(push,2000);

// push()

//刷新页面
function push(){

    var statusInfo=[];
    var idInfo=[];

    $(".status").each(function(){
        statusInfo.push(parseInt($(this).val()));
    });
    $(".id").each(function(){
        idInfo.push(parseInt($(this).val()));
    });
    // console.log(statusInfo);
    // return false;
    ajaxPush(idInfo, statusInfo)

}

//订单列表页ajax刷新
function ajaxPush(idInfo, statusInfo){
    mui.ajax("/index/order/ajaxPushList",{
        type: 'post',
        data:{id:idInfo,status:statusInfo},
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


