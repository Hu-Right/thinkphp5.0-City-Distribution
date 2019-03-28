mui.init();

//页面滚动
mui('.mui-scroll-wrapper').scroll({
    deceleration: 0.0005 //flick 减速系数，系数越大，滚动速度越慢，滚动距离越小，默认值0.0006
});

// 获取当前位置
mui.plusReady(function() {
    plus.geolocation.getCurrentPosition(MapPoint, function(e) {
        mui.alert('获取位置信息失败')
    },{
        provider: 'baidu',
        coordsType: 'bd09ll',
        geocode: true
    });
});

// 获取当前位置
function MapPoint(position) {
    var Lon = position.coords.longitude; //获取经度
    var Lat = position.coords.latitude; //获取纬度
    // alert(Lon);
    // alert(Lat);
    var gpsPoint = new BMap.Point(Lon, Lat);
    map.centerAndZoom(gpsPoint, 17);
    getFJList(gpsPoint)
    // BMap.Convertor.translate(gpsPoint, 0, getFJList); //坐标转换

}


// 获取默认附近地址列表
function getFJList(point) {
    // alert(JSON.stringify(point))
    var map = new BMap.Map("allmap"); //创建地图

    // 获取当前位置附近的点
    var options = {
        onSearchComplete: function(results) {
            //alert(JSON.stringify(results))
            if (local.getStatus() == BMAP_STATUS_SUCCESS) {
                // 判断状态是否正确
                var html = '';
                for (var i = 0; i < results.getCurrentNumPois(); i++) {
                    html += '<li class="mui-table-view-cell" data-lng='+ results.getPoi(i).point.lng + ' data-lat=' + results.getPoi(i).point.lat + ' >' +
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


// 获取搜索地址列表
var map = new BMap.Map("allmap"); //创建地图
document.getElementById("searchId").addEventListener('input',function(){
    // alert(this.value);
    var input_val = this.value;
    var options = {
        onSearchComplete: function(results) {
            if (local.getStatus() == BMAP_STATUS_SUCCESS) {
                // 判断状态是否正确
                var html = '';
                for (var i = 0; i < results.getCurrentNumPois(); i++) {
                    html += '<li class="mui-table-view-cell" data-lng='+ results.getPoi(i).point.lng + ' data-lat=' + results.getPoi(i).point.lat + '>' +
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

// 点击地址列表把数据携带回上一页
var view = null;
mui('#searchResultPanel').on('tap', '.mui-table-view-cell', function() {
    var mlng = this.getAttribute('data-lng'), // 经度
        mlat = this.getAttribute('data-lat'); // 纬度
    var html = this.innerHTML;
    //console.log(mlng, mlat,html+'_sub')
    //alert(mlng)
    //alert(mlat)
    //alert(html)

    // 跳转至前一页
    if (!view) {
        view = plus.webview.getWebviewById('select-ads-index.html');
    }
    mui.fire(view, 'show', {
        html: html,
        mlat: mlat,
        mlng: mlng
    });
    mui.back();
});


