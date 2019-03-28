mui.init({
    pullRefresh: {
        container: '#pullrefresh',
        /*down: {
            callback: pulldownRefresh
        },*/
        up: {
            contentrefresh: '正在加载...',
            callback: pullupRefresh
        }
    }
});
/**
 * 下拉刷新具体业务实现
 */
/*function pulldownRefresh() {
    setTimeout(function() {
        var table = document.body.querySelector('.mui-table-view');
        var cells = document.body.querySelectorAll('.mui-table-view-cell');
        // 循环新加载的数据
        for (var i = cells.length, len = i + 3; i < len; i++) {
            var li = document.createElement('li');
            li.className = 'mui-table-view-cell';
            li.innerHTML = '<a href="javascript:;" class="mui-navigate-right">' +
                '<div class="mui-media-body">' +
                '订单三' +
                '<p class="mui-ellipsis">' +
                '<span>2018.10.10</span>' +
                '<span style="margin-left:5px;">12:30</span>'+
                '</p>'+
                '</div>'+
                '</a>';
            //下拉刷新，新纪录插到最前面；
            table.insertBefore(li, table.firstChild);
        }
        mui('#pullrefresh').pullRefresh().endPulldownToRefresh(); //refresh completed
    }, 1500);
}*/

/**
 * 上拉加载具体业务实现
 */
var count = 0;
var page = 2;//页面上是第一页从第二页开始加载
function pullupRefresh() {
    setTimeout(function() {
        mui.ajax("/index/message/pullRefresh",{
            type: 'post',
            data:{page:page++},
            dataType:'json',//服务器返回json格式数据
            type:'post',//HTTP请求类型
            success: function (data) {
                if(data.length != 0){
                    //console.log(data);
                    //console.log('OK');
                    count = 0;
                    var table = document.body.querySelector('.mui-table-view');
                    var cells = document.body.querySelectorAll('.mui-table-view-cell');
                    for (var i = cells.length, k = 0, len = i + 10; i < len; i++,k++) {
                        //console.log(data[k]);
                        if(typeof(data[k]) != 'undefined'){
                            var li = document.createElement('li');
                            li.className = 'mui-table-view-cell';
                            li.innerHTML =
                                '<a href="javascript:;" class="mui-navigate-right">' +
                                    '<div class="mui-media-body">' +
                                        data[k].title +
                                        '<p class="mui-ellipsis">' +
                                        '<span>'+ data[k].create_date +'</span>' +
                                        '<span style="margin-left:5px;">'+ data[k].create_time +'</span>'+
                                        '</p>'+
                                    '</div>'+
                                '</a>';
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
        mui('#pullrefresh').pullRefresh().endPullupToRefresh((++count > 1)); //参数为true代表没有更多数据了。
        /*var table = document.body.querySelector('.mui-table-view');
        var cells = document.body.querySelectorAll('.mui-table-view-cell');
        for (var i = cells.length, len = i + 5; i < len; i++) {
            var li = document.createElement('li');
            li.className = 'mui-table-view-cell';
            li.innerHTML = '<a href="javascript:;" class="mui-navigate-right">' +
                '<div class="mui-media-body">' +
                '订单三' +
                '<p class="mui-ellipsis">' +
                '<span>2018.10.10</span>' +
                '<span style="margin-left:5px;">12:30</span>'+
                '</p>'+
                '</div>'+
                '</a>';
            table.appendChild(li);
        }*/
    }, 1500);
}

//跳转到详情
mui(document.body).on('tap','#jump',function(){
    var id = this.getAttribute('data-id');
    // console.log(this.getAttribute('data-id'));
    mui.openWindow({url:top.location.origin+'/index/message/msgShow?id='+id,id:'msgShow'})
})