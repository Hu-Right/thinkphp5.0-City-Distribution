mui.init()

document.getElementById("submit").addEventListener('tap',function(){
    var content = $('#content').val();
        img_url =[];
    $("input[name='img']").each(function(){
        img_url.push($(this).val());
    })
    //console.log(img_url);
    mui.ajax("/index/user/advise",{
        type: 'post',
        data:{content:content,img:img_url},
        dataType:'json',//服务器返回json格式数据
        type:'post',//HTTP请求类型
        success: function (data) {
            // console.log(data);
            if(data.code == 1){
                layer.msg(data.msg);
                setTimeout(function(){
                    window.location = data.url;//刷新当前页面.
                },1000);
            }else{
                layer.msg(data.msg);
            }
        },
        error: function (data) {
            layer.msg('反馈失败');
            /*setTimeout(function(){
                window.location.reload();//刷新当前页面.
            },1000);*/
        }
    });
})

