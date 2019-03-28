mui.init();

(function($) {
    $('#scroll').scroll({
        indicators: true //是否显示滚动条
    });



    // 底部地址点击替换
    var _ads = document.getElementById('ads');
    var _lis = document.querySelectorAll(".tab-body .mui-table-view-cell");
    for (var i = 0; i < _lis.length; i++) {
        _lis[i].addEventListener('tap', function() {
            // console.log(this.innerHTML)
            _ads.innerHTML = this.innerHTML
        });
    }

    // 跳转到搜索地址页
    document.getElementById('go-search').addEventListener('tap', function() {
        mui.openWindow({
            url: top.location.origin+'/index/address/searchhead'
        });
    })
})(mui);

// 地图
mui.plusReady(function() {
    // 获取设备定位信息
    plus.geolocation.getCurrentPosition(translatePoint, function(e) {
        mui.toast("异常:" + e.message);
    });

    // 点击定位按钮定位
    document.getElementById("new-dingwei").addEventListener('tap', function() {
        plus.geolocation.getCurrentPosition(translatePoint2, function(e) {
            mui.toast("异常:" + e.message);
        });
    })
});
var locaCity = '';

// 获取当前位置
function translatePoint(position) {
    var currentLon = position.coords.longitude;
    var currentLat = position.coords.latitude;
    locaCity = position.address.city;
    document.getElementById("vv-city-info").innerText = locaCity;
    var gpsPoint = new BMap.Point(currentLon, currentLat);
    BMap.Convertor.translate(gpsPoint, 0, initMap); //坐标转换
}


function initMap(point) {
    var map = new BMap.Map("map"); //创建地图
    // alert(JSON.stringify(point))
    map.centerAndZoom(point, 17);

    // 获取当前位置附近的点
    var options = {
        onSearchComplete: function(results) {
            if (local.getStatus() == BMAP_STATUS_SUCCESS) {
                // 判断状态是否正确
                var html = '';
                for (var i = 0; i < results.getCurrentNumPois(); i++) {
                    html += '<li class="mui-table-view-cell" data-lng="'+results.getPoi(i).point.lng+'" data-lat="'+results.getPoi(i).point.lat+'">' +
                        '<p class="ads1">' + results.getPoi(i).title + '</p>' +
                        '<p class="ads2">' + results.getPoi(i).address + '</p>' +
                        '</li>';
                }
                document.getElementById("fj-list").innerHTML = html;

                // 点击附近的点把值赋给上面地址
                mui('#fj-list').on('tap','.mui-table-view-cell', function(){
                    //document.getElementById("ads").innerHTML = this.innerHTML;
                    var mlng = this.getAttribute('data-lng'),	// 经度
                        mlat = this.getAttribute('data-lat');	// 纬度
                    var mads = document.getElementById('ads');
                    // alert('经度：'+ mlng);
                    // alert('纬度：'+ mlat);
                    mads.setAttribute('data-lng',mlng);
                    mads.setAttribute('data-lat',mlat);
                });

            }
        }
    }
    var local =  new BMap.LocalSearch(map, options);
    local.searchNearby('大厦 , 广场',point,1000);



    // 拖拽地图后获取中心点位置
    map.addEventListener("dragend", function showInfo() {
        var cp = map.getCenter();	// 当前选择的位置经纬度
        // alert(JSON.stringify(cp))

        var mpoint = new BMap.Point(cp.lng, cp.lat);

        // 定点转化成地址写入收货地址栏
        var geoc = new BMap.Geocoder();
        geoc.getLocation(cp, function(rs){
            var addComp = rs.addressComponents;
            // console.log(JSON.stringify(rs));
            //alert(2)
            var mads_html = '<p class="ads1">'+rs.surroundingPois[0].title+'</p><p class="ads2">'+rs.address+'</p>';
            document.getElementById("ads").innerHTML = mads_html;


            var mads = document.getElementById('ads');
            mads.setAttribute('data-lng',rs.point.lng);		// 经度
            mads.setAttribute('data-lat',rs.point.lat);		// 纬度

        });



        // 获取拖拽后定点地址附近的点
        var options = {
            onSearchComplete: function(results) {
                if (local.getStatus() == BMAP_STATUS_SUCCESS) {
                    // 判断状态是否正确
                    var html = '';
                    for (var i = 0; i < results.getCurrentNumPois(); i++) {
                        html += '<li class="mui-table-view-cell" data-lng="'+results.getPoi(i).point.lng+'" data-lat="'+results.getPoi(i).point.lat+'">' +
                            '<p class="ads1">' + results.getPoi(i).title + '</p>' +
                            '<p class="ads2">' + results.getPoi(i).address + '</p>' +
                            '</li>';
                    }
                    document.getElementById("fj-list").innerHTML = html;
                    mui('#fj-list').on('tap','.mui-table-view-cell', function(){
                        document.getElementById("ads").innerHTML = this.innerHTML;
                    });
                }
            }
        }
        var local =  new BMap.LocalSearch(map, options);
        local.searchNearby('大厦 , 广场',mpoint,600);
    });

}

// 点击定位按钮进行定位
function translatePoint2(position) {
    var currentLon = position.coords.longitude;
    var currentLat = position.coords.latitude;
    locaCity = position.address.city;
    // alert(position.address.city)
    document.getElementById("vv-city-info").innerText = locaCity;
    var gpsPoint = new BMap.Point(currentLon, currentLat);
    BMap.Convertor.translate(gpsPoint, 0, initMap2); //坐标转换
}

function initMap2(point) {
    //alert(5)
    map.centerAndZoom(point, 16);

}

// 点击城市选择城市
document.getElementById("vv-city").addEventListener('tap', function() {
    //console.log(locaCity)
    mui.openWindow({
        url: top.location.origin+'/index/city/index',
        id: 'city',
        extras: { //新窗口的额外扩展参数,可用来处理页面间传值
            locaCity: locaCity
        },
        show: {
            aniShow: 'slide-in-bottom'
        }
    })
})

// 如果是从搜索页传过来的地址
window.addEventListener('show', function(e) {
    //获取参数值
    var search_ads = e.detail.html,//接收A页面传入的id参数值
        search_lng = e.detail.mlng,
        search_lat = e.detail.mlat;
    // console.log(search_ads,search_lng,search_lat+'lalal');
    var map = new BMap.Map("map"); //创建地图
    map.centerAndZoom(search_lng,search_lat);
    var mads = document.getElementById('ads');
    mads.innerHTML = search_ads;

    mads.setAttribute('data-lng',search_lng);
    mads.setAttribute('data-lat',search_lat);
});

// 点击头部确定按钮事件
document.getElementById('sure-btn').addEventListener('tap',function(){
    var mads = document.getElementById('ads');
    var mads_lng = mads.getAttribute('data-lng'),	// 地址经度
        mads_lat = mads.getAttribute('data-lat');	// 地址纬度

    var mads_tit = mads.getElementsByClassName('ads1')[0].innerText,
        mads_address = mads.getElementsByClassName('ads2')[0].innerText
        //console.log(mads_lng,mads_lat)
        //console.log(mads_tit,mads_address)

        //alert('经度：'+mads_lng);
        //alert('纬度：'+mads_lat);
        //alert('地址头：'+mads_tit);
        //alert('地址：'+mads_address);

    var ads_detail = document.getElementById('ads-detail').value,
        name = document.getElementById('name').value,
        phone = document.getElementById('phone').value;
    var type = document.getElementById('addresstype').value,
        id = document.getElementById('id').value;
        //console.log(ads_detail,name,phone)
        //alert('详细门牌：'+ads_detail)
        //alert('名字：'+name)
        //alert('电话：'+phone)
        //alert('类型：'+type)
    // 判断经纬度是否为空
    if(mads_lng == 'null' || mads_lat == 'null' || mads_lng == '' || mads_lat == ''){

        mui.alert('地址定位错误', '提示');
        return false;
    }

    if(document.getElementById('phone').value != null && document.getElementById('phone').value != ''){
        var mobile = /^(13[0-9]|14[579]|15[0-3,5-9]|16[6]|17[0135678]|18[0-9]|19[89])\d{8}$/;
        if(!mobile.test(document.getElementById('phone').value)){
            mui.alert('手机号格式不正确', '提示');
            return false;
        }
    }

    //数据
    var data = {
        type:type,
        id:id,
        address_lon:mads_lng,
        address_lat:mads_lat,
        address_head:mads_tit,
        address:mads_address+' '+ads_detail,
        linkman:name,
        mobile:phone
    }
    //AJAX
    save(data);

});

//保存地址
function save(data){
    mui.ajax('/index/address/save',{
        type: 'post',
        data:data,
        dataType:'json',//服务器返回json格式数据
        success: function (data) {
            //console.log(data);
            //alert(111)
            setTimeout(function(){
                //location.href = data.url;//刷新当前页面.
                mui.openWindow({
                    url:top.location.origin+'/index/address/index',
                    createNew:true//是否重复创建同样id的webview，默认为false:不重复创建，直接显示
                })
            },1000);
        },
        error: function (xhr,type,errorThrown) {
            // console.log(xhr.readyState);
            //alert(222);
            setTimeout(function(){
                window.location.reload();//刷新当前页面.
            },1000);
        }
    });
}
