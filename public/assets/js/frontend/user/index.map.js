mui.plusReady(function() {
    // 获取首页城市切换页面传过来的参数
    window.addEventListener('doit', function(e) {
        //获取参数值
        var imagePath = e.detail.imagePath;
        let currentLon = e.detail.currentLon;
        let currentLat = e.detail.currentLat;
        let locaCity = e.detail.locaCity;
        if (imagePath) {
            //console.log(imagePath)
            //alert(imagePath)
            document.getElementById("vv-city-info").innerText = imagePath
            // 定位城市
            map.centerAndZoom(imagePath, 16)

        } else {
            // 切换城市
            document.getElementById("vv-city-info").innerText = locaCity
            //console.log(currentLon)
            //console.log(currentLat)
            let gpsPoint = new BMap.Point(currentLon, currentLat);
            BMap.Convertor.translate(gpsPoint, 0, function(point) {
                map.centerAndZoom(point, 16);
                map.clearOverlays()
            }); //坐标转换
        }


    });

    // 进入页面  获取设备定位信息
    plus.geolocation.getCurrentPosition(translatePoint, noDingwei, {
        provider: 'baidu',
        coordsType: 'bd09ll',
        geocode: true
    });


    // 刷新定位
    document.getElementById("new-dingwei").addEventListener('tap', function() {
        // console.log(123)
        plus.geolocation.getCurrentPosition(translatePoint2, noDingwei, {
            provider: 'baidu',
            coordsType: 'bd09ll',
            geocode: true
        });
    })

// 点击城市选择城市
    document.getElementById("vv-city").addEventListener('tap', function() {
        plus.geolocation.getCurrentPosition(function () {
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
        }, noDingwei, {
            provider: 'baidu',
            coordsType: 'bd09ll',
            geocode: true
        });
        // console.log(locaCity)

    })


});
var isDingwei ;
// 没有开启定位回调封装
function noDingwei(e) {
    switch (e.code) {
        case 10:
            mui.alert('请开启应用的定位权限', '温馨提示', '确定', function() {}, 'div');
            isDingwei = 10
            break;
        case 13:
            mui.alert('请开启应用的定位权限', '温馨提示', '确定', function() {}, 'div');
            isDingwei = 10
            break;
        case 9:
            //mui.alert('请开启手机定位服务');
            mui.alert('请开启手机定位服务', '温馨提示', '确定', function() {}, 'div');
            isDingwei = 9
            break;
        case 2:
            if (e.message.indexOf("[geolocation:13]") > -1) {
                //如果网络开启，定位失败，提示检查定位权限
                mui.alert('请开启应用的定位权限', '温馨提示', '确定', function() {}, 'div');
            }
            if (e.message.indexOf("[geolocation:14]") > -1) {
                //如果应用的权限开了，提示网络异常
                mui.alert('请检查网络是否正常', '温馨提示', '确定', function() {}, 'div');
            }
            isDingwei = 10
            break;
        case e.PERMISSION_DENIED:
            mui.alert('请求定位被拒绝', '温馨提示', '确定', function() {}, 'div');
            break;
            isDingwei = 10
        case e.POSITION_UNAVAILABLE:
            mui.alert("位置信息不可用", '温馨提示', '确定', function() {}, 'div');
            break;
        case e.TIMEOUT:
            mui.alert("获取位置信息超时", '温馨提示', '确定', function() {}, 'div');
            break;
        case e.UNKNOWN_ERROR:
            mui.alert("未知错误", '温馨提示', '确定', function() {}, 'div');
            break;
    }
}

// 获取设备的相关参数封装
var locaCity = '';
let map = new BMap.Map("allmap"); //创建地图

function translatePoint(position) {
    //console.log(JSON.stringify(position));
    var currentLon = position.coords.longitude;
    var currentLat = position.coords.latitude;
    locaCity = position.address.city;
    var gpsPoint = new BMap.Point(currentLon, currentLat);
    map.centerAndZoom(gpsPoint, 16);

    // BMap.Convertor.translate(gpsPoint, 0, initMap); //坐标转换
    document.getElementById("vv-city-info").innerText = locaCity//更换 header 城市
    updateArea(position.address.province, position.address.city, position.address.district)//更新 user 位置信息
    var mlng = gpsPoint.lng;
    var mlat = gpsPoint.lat;
    getPaoNanPoints(map,mlng,mlat);

    // 拖拽地图后获取中心点位置
    map.addEventListener("dragend", function showInfo() {
        var cp = map.getCenter();
        //alert(cp.lng + "," + cp.lat);
        // 获取当前定点位置的跑男位置
        getPaoNanPoints(map,cp.lng,cp.lat);

    });
}
// 获取设备的相关参数封装 之 初始化地图
// function initMap(point) {
//
//     //alert(cp.lng + "," + cp.lat);
//     // 获取我当前位置的跑男位置
//
//
// }

// 获取定点范围内的跑男位置
function getPaoNanPoints(map,mlng,mlat){
    var points = [];

    //console.log(mlng+','+mlat);

    //发送送ajax到后台 拿到该范围内的所有的跑腿人员的位置
    mui.ajax('/index/user/runmen',{
        data:{lon:mlng,lat:mlat},
        dataType:'json',//服务器返回json格式数据
        type:'post',//HTTP请求类型
        timeout:10000,//超时时间设置为10秒；
        success:function(data){
            //var a = JSON.stringify(data);
            //console.log(a);
            //console.log(data.code);
            delMarker(map);
            if(data.code == 1){
                var points = data.data;
                for (var i = 0; i < points.length; i ++) {
                    var point = new BMap.Point(points[i].lon, points[i].lat);
                    addMarker(map,point);
                }
            }
        },
        error:function(xhr,type,errorThrown){
            //console.log(xhr.status);
            layer.msg('定位错误');
            /*setTimeout(function(){
                window.location.reload();//刷新当前页面.
            },1000);*/
        }
    });
}
// 编写自定义函数,创建标注
function addMarker(map,point){
    var myIcon = new BMap.Icon("/assets/img/icon-pn.png", new BMap.Size(30,30));
    var marker = new BMap.Marker(point,{icon:myIcon});  // 创建标注
    map.addOverlay(marker);
}
// 编写自定义函数,删除标注
function delMarker(map){
    //alert(1);
    map.clearOverlays();
}

// 刷新定位
function translatePoint2(position) {
    var currentLon = position.coords.longitude;
    var currentLat = position.coords.latitude;
    locaCity = position.address.city;
    // console.log(position.address.city)
    document.getElementById("vv-city-info").innerText = locaCity//更换 header 城市
    var gpsPoint = new BMap.Point(currentLon, currentLat);
    map.centerAndZoom(gpsPoint, 16);
    // BMap.Convertor.translate(gpsPoint, 0, initMap2); //坐标转换

    updateArea(position.address.province, position.address.city, position.address.district)//更新 user 位置信息
}
// function initMap2(point) {
//
// }

//更新用户位置信息
function updateArea(province, city, county){
    var islogin = $(" input[ name='islogin' ] ").val();//登录状态，未登录不发送 ajax
    if(islogin==1){
        //发送送ajax到后台 拿到该范围内的所有的跑腿人员的位置
        mui.ajax('/index/user/updateArea',{
            data:{province:province,city:city,county:county},
            dataType:'json',//服务器返回json格式数据
            type:'post',//HTTP请求类型
            timeout:10000,//超时时间设置为10秒；
            success:function(data){
                //var a = JSON.stringify(data);
                //console.log(a);
                //console.log(JSON.stringify(data));

            },
            error:function(xhr,type,errorThrown){
                //console.log(xhr.status);
                layer.msg('定位错误');
                setTimeout(function(){
                    window.location.reload();//刷新当前页面.
                },1000);
            }
        });
    }
}

