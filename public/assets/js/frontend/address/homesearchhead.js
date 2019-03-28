// 获取当前位置
mui.init({
    /*beforeback: function() {
        //获得父页面的webview
        var list = plus.webview.currentWebview().opener();
        //触发父页面的自定义事件(refresh),从而进行刷新
        mui.fire(list, 'refresh1');
        //返回true,继续页面关闭逻辑
        return true;
    }*/
});

mui.plusReady(function() {
// 获取当前位置
//         console.log('开始定位')
        plus.geolocation.getCurrentPosition(MapPoint, function(e) {
            mui.alert('获取位置信息失败')
        }, {
            provider: 'baidu',
            coordsType: 'bd09ll',
            geocode: true
        });


    var status = plus.storage.getItem('status');
    // console.log(status);

    //根据 status 不同值做不同操作
    if (status == 2 || status == 4 || status == 6 || status == 8 || status == 10){

        mui('#searchResultPanel').on('tap', '.mui-table-view-cell', function() {
            var mlng = this.getAttribute('data-lng'), // 经度
                mlat = this.getAttribute('data-lat'); // 纬度
            var html = this.innerHTML;

            // 判断经纬度是否为空
            if(mlng == null || mlat == null || mlng == '' || mlat == ''){
                mui.alert('', '地址定位错误');
                return false;
            }
            //保存信息
            // plus.storage.setItem('ads', html);
            plus.storage.setItem('end_address', html);
            plus.storage.setItem('end_lon', mlng);
            plus.storage.setItem('end_lat', mlat);
            window.location.href =  top.location.origin+'/index/address/selectaddress';
            // mui.back();//返回
        });
    }else if(status == 1 || status == 3 || status == 5 || status == 7 || status == 9){

        mui('#searchResultPanel').on('tap', '.mui-table-view-cell', function() {
            var mlng = this.getAttribute('data-lng'), // 经度
                mlat = this.getAttribute('data-lat'); // 纬度
            var html = this.innerHTML;

            // 判断经纬度是否为空
            if(mlng == null || mlat == null || mlng == '' || mlat == ''){
                mui.alert('', '地址定位错误');
                return false;
            }
            //保存信息
            // plus.storage.setItem('ads', html);
            plus.storage.setItem('start_address', html);
            plus.storage.setItem('start_lon', mlng);
            plus.storage.setItem('start_lat', mlat);
            window.location.href =  top.location.origin+'/index/address/selectaddress';
            // mui.back();//返回
        });
    }
});








var contentWebview = null;
document.querySelector('header').addEventListener('doubletap', function() {
    if (contentWebview == null) {
        contentWebview = plus.webview.currentWebview().children()[0];
    }
    contentWebview.evalJS("mui('#pullrefresh').pullRefresh().scrollTo(0,0,100)");
});


// 获取搜索地址列表
document.getElementById("searchId").addEventListener('input', function() {
    // console.log(this.value);
    var input_val = this.value;
    var options = {
        onSearchComplete: function(results) {
            if (local.getStatus() == BMAP_STATUS_SUCCESS) {
                // 判断状态是否正确
                var html = '';
                for (var i = 0; i < results.getCurrentNumPois(); i++) {
                    html += '<li class="mui-table-view-cell" data-lng=' + results.getPoi(i).point.lng + ' data-lat=' + results.getPoi(
                        i).point.lat + '>' +
                        '<p class="ads1">' + results.getPoi(i).title + '</p>' +
                        '<p class="ads2">' + results.getPoi(i).address + '</p>' +
                        '</li>';
                }
                document.getElementById("searchResultPanel").innerHTML = html;
            }
        }
    }
    var local = new BMap.LocalSearch(map, options);
    local.search(input_val);
});


var map = new BMap.Map("allmap"); //创建地图

mui('.mui-scroll-wrapper').scroll({
    deceleration: 0.0005 //flick 减速系数，系数越大，滚动速度越慢，滚动距离越小，默认值0.0006
});





// 获取当前位置
function MapPoint(position) {
    var Lon = position.coords.longitude; //获取经度
    var Lat = position.coords.latitude; //获取纬度
    locaCity = position.address.city;
    document.getElementById("vv-city-info").innerText = locaCity;
    var gpsPoint = new BMap.Point(Lon, Lat);
    map.centerAndZoom(gpsPoint, 17); 
    getFJList(gpsPoint)
    // BMap.Convertor.translate(gpsPoint, 0, getFJList); //坐标转换
}

// 获取默认附近地址列表
function getFJList(point) {


    // 获取当前位置附近的点
    var options = {
        onSearchComplete: function(results) {
            if (local.getStatus() == BMAP_STATUS_SUCCESS) {
                // 判断状态是否正确
                var html = '';
                for (var i = 0; i < results.getCurrentNumPois(); i++) {
                    html += '<li class="mui-table-view-cell" data-lng=' + results.getPoi(i).point.lng + ' data-lat=' + results.getPoi(
                        i).point.lat + '>' +
                        '<p class="ads1">' + results.getPoi(i).title + '</p>' +
                        '<p class="ads2">' + results.getPoi(i).address + '</p>' +
                        '</li>';
                }
                document.getElementById("searchResultPanel").innerHTML = html;
            }
        }
    }
    var local = new BMap.LocalSearch(map, options);

    local.searchNearby('大厦 , 广场', point, 1000);
}