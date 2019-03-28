mui.init({
    swipeBack: false,
    keyEventBind: {
        backbutton: false //关闭back按键监听
    },
    pullRefresh: {
        container: '#pullrefresh',
        up: {
            contentrefresh: '正在加载...',
            callback: pullupRefresh
        }
    }
});
var page = 2;//页面上是第一页从第二页开始加载
/**
 * 上拉加载具体业务实现
 */
function pullupRefresh() {
    setTimeout(function() {
        mui.ajax("/index/order/pullRefresh",{
            type: 'post',
            data:{page:page++,type:1},
            dataType:'json',//服务器返回json格式数据
            type:'post',//HTTP请求类型
            success: function (data) {
                //console.log(data);
                if(data.length == 0){
                    var result = true;
                    mui('#pullrefresh').pullRefresh().endPullupToRefresh(result);
                }else{
                    var result = false;
                    mui('#pullrefresh').pullRefresh().endPullupToRefresh(result); //参数为true代表没有更多数据了。
                    var table = document.body.querySelector('.mui-table-view');
                    var cells = document.body.querySelectorAll('.mui-table-view-cell');
                    for(var i = cells.length, k = 0, len = i + 10 ; i < len; i++,k++) {
                        //console.log(data[k]);
                        if(typeof(data[k]) != 'undefined'){
                            //状态不同显示内容不一样
                            switch(data[k].status)
                            {
                                case 0:
                                    var status_name = '待接单',
                                        html = '<button type="button" class="mui-btn mui-btn-grey cancel">取消订单</button>';
                                    if(data[k].start_address != ''){
                                        var start_address =
                                            '<div class="li o-h">' +
                                            '<i class="iconfont icon-dizhi-fa f-l"></i>' +
                                            '<p class="p">'+data[k].start_address+'</p>' +
                                            '</div>';
                                    }else{
                                        var start_address = '';
                                    }
                                    break;
                                case 1:
                                    if(data[k].order_code == null){
                                        var status_name = '待就位',
                                            html = '';
                                    }else{
                                        var status_name = '已接单',
                                            html = '';
                                    }
                                    if(data[k].start_address != ''){
                                        var start_address =
                                            '<div class="li o-h">' +
                                            '<i class="iconfont icon-dizhi-fa f-l"></i>' +
                                            '<p class="p">'+data[k].start_address+'</p>' +
                                            '</div>';
                                    }else{
                                        var start_address = '';
                                    }
                                    break;
                                /*case 2:
                                    var status_name = '已接单',
                                        html = '<button type="button" class="mui-btn mui-btn-blue">确认完成</button>';
                                    if(data[k].start_address != ''){
                                        var start_address =
                                            '<div class="li o-h">' +
                                            '<i class="iconfont icon-dizhi-fa f-l"></i>' +
                                            '<p class="p">'+data[k].start_address+'</p>' +
                                            '</div>';
                                    }else{
                                        var start_address = '';
                                    }
                                    break;*/
                                case 3:
                                    var status_name = '已取消',
                                        html = '<button type="button" class="mui-btn mui-btn-grey delete"">删除</button>';
                                    if(data[k].start_address != ''){
                                        var start_address =
                                            '<div class="li o-h">' +
                                            '<i class="iconfont icon-dizhi-fa f-l"></i>' +
                                            '<p class="p">'+data[k].start_address+'</p>' +
                                            '</div>';
                                    }else{
                                        var start_address = '';
                                    }
                                    break;
                                case 5:
                                    var status_name = '待评价',
                                        html = '<button type="button" class="mui-btn mui-btn-blue">去评价</button><button type="button" class="mui-btn mui-btn-grey delete">删除</button>';
                                    if(data[k].start_address != ''){
                                        var start_address =
                                            '<div class="li o-h">' +
                                            '<i class="iconfont icon-dizhi-fa f-l"></i>' +
                                            '<p class="p">'+data[k].start_address+'</p>' +
                                            '</div>';
                                    }else{
                                        var start_address = '';
                                    }
                                    break;
                                case 6:
                                    var status_name = '已完成',
                                        html = '<button type="button" class="mui-btn mui-btn-grey delete">删除</button>';
                                    if(data[k].start_address != ''){
                                        var start_address =
                                            '<div class="li o-h">' +
                                            '<i class="iconfont icon-dizhi-fa f-l"></i>' +
                                            '<p class="p">'+data[k].start_address+'</p>' +
                                            '</div>';
                                    }else{
                                        var start_address = '';
                                    }
                                    break;
                                case 9:
                                    var status_name = '待支付',
                                        html = '<button type="button" class="mui-btn mui-btn-blue">去支付</button><button type="button" class="mui-btn mui-btn-grey cancel">取消订单</button>';
                                    if(data[k].start_address != ''){
                                        var start_address =
                                            '<div class="li o-h">' +
                                            '<i class="iconfont icon-dizhi-fa f-l"></i>' +
                                            '<p class="p">'+data[k].start_address+'</p>' +
                                            '</div>';
                                    }else{
                                        var start_address = '';
                                    }
                                    break;
                                default:
                                    var status_name = '',
                                        html = '';
                                    var start_address = '';
                            }
                            switch(data[k].type)
                            {
                                case 1:
                                    var end_icon = 'shou';
                                    break;
                                case 2:
                                    var end_icon = 'shou';
                                    break;
                                case 3:
                                    var end_icon = 'ban';
                                    break;
                                case 4:
                                    var end_icon = 'pai';
                                    break;
                                default:
                            }

                            var li = document.createElement('li');
                            li.className = 'mui-table-view-cell';
                            li.innerHTML =
                                '<a class="jump" data-id="'+data[k].id+'">' +
                                '<div class="top">' +
                                '<span class="type">'+data[k].service_name+'</span>' +
                                '<span class="time"><span class="sp-m-r">'+data[k].create_date+'</span>' +
                                '<span class="process" id="process'+data[k].status+'">'+status_name+'</span>' +
                                '</div>' +
                                '<div class="center">' +
                                start_address +
                                '<div class="li o-h">' +
                                '<i class="iconfont icon-dizhi-'+end_icon+' f-l"></i>' +
                                '<p class="p">'+data[k].end_address+'</p>' +
                                '</div>' +
                                '<div class="li o-h">' +
                                '<i class="iconfont icon-dianhua f-l"></i>' +
                                '<p class="p f-l">联系人：'+data[k].content.name+'<span class="phone">'+data[k].content.mobile+'</span></p>' +
                                '</div>' +
                                '</div>' +
                                '</a>' +
                                '<div class="btm">' +
                                '<input type="hidden" class="id" value="'+data[k].id+'">'
                                +html+'' +
                                '<p class="f-r p2">订单金额 <span class="black">￥</span><span class="black money">'+data[k].money+'</span></p>' +
                                '</div>';
                            table.appendChild(li);
                        }
                    }
                }

            },
            error: function (data) {
                //layer.msg('修改失败');
                //console.log('error');
                setTimeout(function(){
                    window.location.reload();//刷新当前页面.
                },1000);
            }
        });
    }, 1000);
}

//跳转到订单详情
mui(document.body).on('tap','.jump',function(){
    var id = this.getAttribute('data-id');
    //console.log(this.getAttribute('data-id'));
    mui.openWindow({url:top.location.origin+'/index/placeorder/orderdetails?id='+id,id:'orderdetails',createNew:true})
})

//评价订单
mui(document.body).on('tap','.evaluate',function(){
    //console.log(this.parentNode.getElementsByTagName('input')[0].value);
    //删除id
    var id = this.parentNode.getElementsByTagName('input')[0].value;
    mui.openWindow({url:top.location.origin+'/index/order/evaluate?id='+id,id:'evaluate',createNew:true})
})

/*//检测是否待支付
mui.ajax("/index/order/checkPay",{
    type: 'post',
    data:{},
    dataType:'json',//服务器返回json格式数据
    type:'post',//HTTP请求类型
    success: function (data) {
        //console.log(data);
        //只刷新一次页面
        if(location.href.indexOf("#reloaded")==-1){
            /!*setTimeout(function(){
                location.href=location.href+"#reloaded";
                location.reload();//刷新当前页面.
            },1000);*!/
            location.href=location.href+"#reloaded";
            location.reload();//刷新当前页面.
        }

    },
    error: function (xhr,type,errorThrown) {
        // console.log(xhr.status);
        /!*setTimeout(function(){
            window.location.reload();//刷新当前页面.
        },1000);*!/
    }
});*/

//删除订单
mui(document.body).on('tap','.delete',function(){
    //console.log(this.parentNode.getElementsByTagName('input')[0].value);
    //删除id
    var id = this.parentNode.getElementsByTagName('input')[0].value;

    mui.ajax("/index/order/delete",{
        type: 'post',
        data:{id:id},
        dataType:'json',//服务器返回json格式数据
        type:'post',//HTTP请求类型
        success: function (data) {
            //console.log(data);
            layer.msg('删除成功');
            setTimeout(function(){
                window.location.reload();//刷新当前页面.
            },1000);
        },
        error: function (data) {
            //console.log('error');
            setTimeout(function(){
                window.location.reload();//刷新当前页面.
            },1000);
        }
    });
})

//取消订单
mui(document.body).on('tap','.cancel',function(){
    //console.log(this.parentNode.getElementsByTagName('input')[0].value);
    //删除id
    var id = this.parentNode.getElementsByTagName('input')[0].value;

    mui.ajax("/index/order/cancel",{
        type: 'post',
        data:{id:id},
        dataType:'json',//服务器返回json格式数据
        type:'post',//HTTP请求类型
        success: function (data) {
            //console.log(data);
            layer.msg('取消成功');
            setTimeout(function(){
                window.location.reload();//刷新当前页面.
            },1000);
        },
        error: function (data) {
            //console.log('error');
            setTimeout(function(){
                window.location.reload();//刷新当前页面.
            },1000);
        }
    });
})