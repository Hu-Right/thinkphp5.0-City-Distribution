var locaCity = '';
var mPoint = '';


//启用双击监听
mui.init({
    gestureConfig: {
        doubletap: true
    },
    subpages: [{
        url: top.location.origin+'/index/address/searchbody',
        id: 'search-ads_sub.html',
        styles: {
            top: '44px',
            bottom: '0px',
        }
    }]
});


// 获取当前位置
mui.plusReady(function() {
    plus.geolocation.getCurrentPosition(MapPoint, function(e) {
        mui.toast("error:" + e.message);
    });
});

// 获取当前位置并替换头部城市
function MapPoint(position) {
    var Lon = position.coords.longitude; //获取经度
    var Lat = position.coords.latitude; //获取纬度
    locaCity = position.address.city;
    document.getElementById("vv-city-info").innerText = locaCity;
}



var contentWebview = null;
document.querySelector('header').addEventListener('doubletap', function() {
    if (contentWebview == null) {
        contentWebview = plus.webview.currentWebview().children()[0];
    }
    contentWebview.evalJS("mui('#pullrefresh').pullRefresh().scrollTo(0,0,100)");
});