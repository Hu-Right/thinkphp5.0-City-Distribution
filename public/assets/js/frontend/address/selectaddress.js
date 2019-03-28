mui.init();
var map = new BMap.Map("map"); //创建地图

// 地图
var locaCity = '';
var status;

mui.plusReady(function() {

    // var ads = plus.storage.getItem('ads');
    var content = plus.storage.getItem('content'),
        start_address = plus.storage.getItem('start_address'),
        start_lon = plus.storage.getItem('start_lon'),
        start_lat = plus.storage.getItem('start_lat'),
        s_name = plus.storage.getItem('s_name'),
        s_phone = plus.storage.getItem('s_phone'),
        end_address = plus.storage.getItem('end_address'),
        end_lon = plus.storage.getItem('end_lon'),
        end_lat = plus.storage.getItem('end_lat'),
        e_name = plus.storage.getItem('e_name'),
        e_phone = plus.storage.getItem('e_phone'),
        name = plus.storage.getItem('name'),
        phone = plus.storage.getItem('phone');
        status = plus.storage.getItem('status');
    // console.log('ads：'+ads);
    // console.log('content：'+content);
    // console.log('start_address：'+start_address);
    // console.log('start_lon：'+start_lon);
    // console.log('start_lat：'+start_lat);
    // console.log('end_address：'+end_address);
    // console.log('end_lon：'+end_lon);
    // console.log('end_lat：'+end_lat);
    // console.log('name：'+name);
    // console.log('phone：'+phone);
    // console.log('status：'+status);

 plus.geolocation.getCurrentPosition(translatePoint, function(e) {
            mui.alert('获取位置信息失败')
        }, {
            provider: 'baidu',
            coordsType: 'bd09ll',
            geocode: true
        });

    if(status == 1 || status == 3 || status == 5 || status == 7 || status == 9){
        // console.log('13579');
        if(start_address != null){
            //更改地址
            var mads = document.getElementById('ads');
            mads.innerHTML = start_address;
            mads.setAttribute('data-lng', start_lon);
            mads.setAttribute('data-lat', start_lat);
        }
    }else if(status == 2 || status == 4 || status == 6 || status == 8 || status == 10){
        // console.log('24680');
        if(end_address != null){
            //更改地址
            var mads = document.getElementById('ads');
            mads.innerHTML = end_address;
            mads.setAttribute('data-lng', end_lon);
            mads.setAttribute('data-lat', end_lat);
        }else{
            document.getElementById('ads').innerHTML = '<p class="left">选择地址：</p>' +
                '<div class="right" id="blue">点击获取地址</div>';
        }
    }else{
        document.getElementById('ads').innerHTML = '<p class="left">选择地址：</p>' +
            '<div class="right" id="blue">点击获取地址</div>';
    }

    document.getElementById('contact_way').style.display="block";
    document.getElementById('contacts').style.display="block";
    // 点击头部确定按钮事件
    document.getElementById('sure-btn').addEventListener('tap', function() {
        var mads = document.getElementById('ads');
        var mads_lng = mads.getAttribute('data-lng'), // 地址经度
            mads_lat = mads.getAttribute('data-lat'); // 地址纬度
        // alert(mads_lng);
        // alert(mads_lat);
        // console.log(mads.innerHTML);

        if(mads.innerHTML.indexOf('点击获取地址') != -1){
            mui.alert('请选择地点', '提示');
            return false;
        }

        if(document.getElementById('phone').value != null && document.getElementById('phone').value != ''){
            var mobile = /^(13[0-9]|14[579]|15[0-3,5-9]|16[6]|17[0135678]|18[0-9]|19[89])\d{8}$/;
            if(!mobile.test(document.getElementById('phone').value)){
                mui.alert('手机号格式不正确', '提示');
                return false;
            }
        }

        var mads_tit = mads.getElementsByClassName('ads1')[0].innerText,
            mads_address = mads.getElementsByClassName('ads2')[0].innerText;


        var ads_detail = document.getElementById('ads-detail').value,
            name = document.getElementById('name').value,
            phone = document.getElementById('phone').value;
        // console.log(ads_detail, name, phone)
        // alert('经度：'+mads_lng);
        // alert('纬度：'+mads_lat);
        // 判断经纬度是否为空
        if(mads_lng == 'null' || mads_lat == 'null' || mads_lng == '' || mads_lat == ''){
            mui.alert('地址定位错误', '提示');
            return false;
        }

        var view = null;
        /*----------------------帮我买-----------------------------*/
        if (status == 1)
        {  //起始
            plus.storage.setItem('start_address', mads_address+mads_tit+ads_detail);
            mui.openWindow({
                url: top.location.origin+'/index/placeorder/helpbuystep2',
                id: 'helpBuy-index-next',
                createNew:true
            })
        }
        else if (status == 2)//结束
        {
            //收货人信息
            if(name == '' ||phone == ''){
                mui.alert('请填写联系人信息', '提示');
                return false;
            }
            plus.storage.setItem('end_address', mads_address+mads_tit+ads_detail);
            plus.storage.setItem('name', name);
            plus.storage.setItem('phone', phone);
            window.location.href =  top.location.origin+'/index/placeorder/helpbuystep2';
        }

        /*----------------------帮我取-----------------------------*/
        if (status == 9)//起始
        {
            //发货人信息
            if(name == '' ||phone == ''){
                mui.alert('请填写联系人信息', '提示');
                return false;
            }
            plus.storage.setItem('start_address', mads_address+mads_tit+ads_detail);
            plus.storage.setItem('s_name', name);
            plus.storage.setItem('s_phone', phone);
            mui.openWindow({
                url: top.location.origin+'/index/placeorder/helptakestep2',
                id: 'helpSend-index-next',
                createNew:true
            })
        }
        else if (status == 10)//结束
        {
            //收货人信息
            if(name == '' ||phone == ''){
                mui.alert('请填写联系人信息', '提示');
                return false;
            }
            plus.storage.setItem('end_address', mads_address+mads_tit+ads_detail);
            plus.storage.setItem('e_name', name);
            plus.storage.setItem('e_phone', phone);
            window.location.href =  top.location.origin+'/index/placeorder/helptakestep2';
        }

        /*----------------------帮我送-----------------------------*/
        if (status == 3)//起始
        {
            //发货人信息
            if(name == '' ||phone == ''){
                mui.alert('请填写联系人信息', '提示');
                return false;
            }
            plus.storage.setItem('start_address', mads_address+mads_tit+ads_detail);
            plus.storage.setItem('s_name', name);
            plus.storage.setItem('s_phone', phone);
            mui.openWindow({
                url: top.location.origin+'/index/placeorder/helpdeliverstep2',
                id: 'helpSend-index-next',
                createNew:true
            })
        }
        else if (status == 4)//结束
        {
            //收货人信息
            if(name == '' ||phone == ''){
                mui.alert('请填写联系人信息', '提示');
                return false;
            }
            plus.storage.setItem('end_address', mads_address+mads_tit+ads_detail);
            plus.storage.setItem('e_name', name);
            plus.storage.setItem('e_phone', phone);
            window.location.href =  top.location.origin+'/index/placeorder/helpdeliverstep2';

        }
        /*----------------------帮我办-----------------------------*/
        if (status == 6)
        {
            //联系人信息
            if(name == '' ||phone == ''){
                mui.alert('请填写联系人信息', '提示');
                return false;
            }
            plus.storage.setItem('end_address', mads_address+mads_tit+ads_detail);
            plus.storage.setItem('name', name);
            plus.storage.setItem('phone', phone);
            window.location.href =  top.location.origin+'/index/placeorder/helpdostep2';
        }
        /*----------------------帮我排-----------------------------*/
        if (status == 8)
        {
            //联系人信息
            if(name == '' ||phone == ''){
                mui.alert('请填写联系人信息', '提示');
                return false;
            }
            plus.storage.setItem('end_address', mads_address+mads_tit+ads_detail);
            plus.storage.setItem('name', name);
            plus.storage.setItem('phone', phone);
            window.location.href =  top.location.origin+'/index/placeorder/helplinestep2';
        }
    });

    if(status == 1 || status ==3 || status ==5 || status ==7 || status ==9){
		// console.log('手机定位13579')
        var map_lon = start_lon;
        var map_lat = start_lat;
		// console.log(map_lon)
		// console.log(map_lat)
        map.clearOverlays();
        var point1 = new BMap.Point(map_lon,map_lat);
        map.centerAndZoom(point1,16);
        var myIcon = new BMap.Icon("/assets/img/dingwei.png", new BMap.Size(30, 30), {
            anchor: new BMap.Size(25, 50), // 指定定位位置
            imageOffset: new BMap.Size(0, 0), // 设置图片偏移
            // size:new BMap.Size(30, 30)
            imageSize: new BMap.Size(30, 30)
        });
        var marker = new BMap.Marker(point1, {
            icon: myIcon
        });
        // map.addOverlay(marker);
    }else if(status == 2 || status ==4 || status ==6 || status ==8 || status ==10){
		// console.log('手机定位24680')
        var map_lon = end_lon;
        var map_lat = end_lat;
        map.clearOverlays();
        var point1 = new BMap.Point(map_lon,map_lat);
        map.centerAndZoom(point1,16);
        var myIcon = new BMap.Icon("/assets/img/dingwei.png", new BMap.Size(30, 30), {
            anchor: new BMap.Size(25, 50), // 指定定位位置
            imageOffset: new BMap.Size(0, 0), // 设置图片偏移
            // size:new BMap.Size(30, 30)
            imageSize: new BMap.Size(30, 30)
        });
        var marker = new BMap.Marker(point1, {
            icon: myIcon
        });

        // map.removeOverlay(preMarker);
        // map.addOverlay(marker);
    }

    //起始地址不显示联系人信息
    if(status == 1){
        document.getElementById('contact_way').style.display="none";
        document.getElementById('contacts').style.display="none";
    }
    //结束地址显示联系人信息
    if(status == 2 || status == 4 || status == 6 || status == 8 || status == 10){
        document.getElementById('contact_way').style.display="block";
        document.getElementById('contacts').style.display="block";
    }

    // 手机定位
    document.getElementById("new-dingwei").addEventListener('tap', function() {
        plus.geolocation.getCurrentPosition(translatePoint2, function(e) {
            mui.toast("异常:" + e.message);
        });
    });


    (function($) {
        $('#scroll').scroll({
            indicators: true //是否显示滚动条
        });
        $('#scroll2').scroll({
            indicators: true //是否显示滚动条
        });


        // 底部地址点击替换
        var _ads = document.getElementById('ads');
        var _lis = document.querySelectorAll(".tab-body .mui-table-view-cell");
        for (var i = 0; i < _lis.length; i++) {
            _lis[i].addEventListener('tap', function() {
                var mlng = this.getAttribute('data-lng'), // 经度
                    mlat = this.getAttribute('data-lat'); // 纬度
                console.log(this.innerHTML)
                // alert(mlng)
                // alert(mlat)
                _ads.innerHTML = this.innerHTML;
                _ads.setAttribute('data-lng', mlng);
                _ads.setAttribute('data-lat', mlat);
                //经纬度存入locaStorage
                if(status == 1 || status ==3 || status ==5 || status ==7 || status ==9){
                    plus.storage.setItem('start_lon', mlng);
                    plus.storage.setItem('start_lat', mlat);
                    // console.log('start_lon'+plus.storage.getItem('start_lon'));
                    // console.log('start_lat'+plus.storage.getItem('start_lat'));
                }else if(status == 2 || status ==4 || status ==6 || status ==8 || status ==10){
                    plus.storage.setItem('end_lon', mlng);
                    plus.storage.setItem('end_lat', mlat);
                    // console.log('end_lon'+plus.storage.getItem('end_lon'));
                    // console.log('end_lat'+plus.storage.getItem('end_lat'));
                }
            });
        }

        // 跳转到搜索地址页
        document.getElementById('go-search').addEventListener('tap', function() {
            // console.log(status);
            window.location.href =  top.location.origin+'/index/address/homesearchhead';
            /*mui.openWindow({
                url: top.location.origin+'/index/address/homesearchhead',
                id: 'search-ads'
            });*/
        })
    })(mui);

});

// 获取当前位置
function translatePoint(position) {
    locaCity = position.address.city;
    // 判断经纬度是否为空
    if(locaCity == 'null' || locaCity == ''){
        mui.alert('定位错误', '提示');
        return false;
    }
    var start_lon = plus.storage.getItem('start_lon');
    var start_lat = plus.storage.getItem('start_lat');
	var end_lon = plus.storage.getItem('end_lon');
	var end_lat = plus.storage.getItem('end_lat');

	if(status == 1 || status ==3 || status ==5 || status ==7 || status ==9){
		if(start_lon && start_lat){
			var gpsPoint = new BMap.Point(start_lon, start_lat);
		}else{
			var currentLon = position.coords.longitude;
			var currentLat = position.coords.latitude;
			var gpsPoint = new BMap.Point(currentLon, currentLat);
		}
	}else if(status == 2 || status ==4 || status ==6 || status ==8 || status ==10){
		if(end_lon && end_lat){
			// console.log(end_lon)
			// console.log(end_lat)

			var gpsPoint = new BMap.Point(end_lon, end_lat);
		}else{
			var currentLon = position.coords.longitude;
			var currentLat = position.coords.latitude;
			var gpsPoint = new BMap.Point(currentLon, currentLat);
		}
	}else{

        if(start_lon && start_lat){
            var gpsPoint = new BMap.Point(start_lon, start_lat);
        }else if(end_lon && end_lat){
            var gpsPoint = new BMap.Point(end_lon, end_lat);
        }else{
            var currentLon = position.coords.longitude;
            var currentLat = position.coords.latitude;
            var gpsPoint = new BMap.Point(currentLon, currentLat);
        }

        // alert(status)

		mui.alert('定位失败')
	}
    
    document.getElementById("vv-city-info").innerText = locaCity;
	initMap(gpsPoint)
    // BMap.Convertor.translate(gpsPoint, 0, initMap); //坐标转换
}


function initMap(point) {
    // alert(JSON.stringify(point))
    map.centerAndZoom(point, 17);
    map.clearOverlays();
    var myIcon = new BMap.Icon("/assets/img/dingwei.png", new BMap.Size(30, 30), {
        anchor: new BMap.Size(25, 50), // 指定定位位置
        imageOffset: new BMap.Size(0, 0), // 设置图片偏移
        // size:new BMap.Size(30, 30)
        imageSize: new BMap.Size(30, 30)
    });
    var marker = new BMap.Marker(point, {
        icon: myIcon
    });

    // map.removeOverlay(preMarker);
    map.addOverlay(marker);
    // 获取当前位置附近的点
    var options = {
        onSearchComplete: function(results) {
            if (local.getStatus() == BMAP_STATUS_SUCCESS) {
                // console.log(JSON.stringify(results.getPoi(i)))
                // 判断状态是否正确
                var html = '';
                for (var i = 0; i < results.getCurrentNumPois(); i++) {
                    html += '<li class="mui-table-view-cell" data-lng=' + results.getPoi(i).point.lng + ' data-lat=' + results.getPoi(
                        i).point.lat + '>' +
                        '<p class="ads1">' + results.getPoi(i).title + '</p>' +
                        '<p class="ads2">' + results.getPoi(i).address + '</p>' +
                        '</li>';

                }
                document.getElementById("fj-list").innerHTML = html;

                // 点击附近的点把值赋给上面地址
                mui('#fj-list').on('tap', '.mui-table-view-cell', function() {
                    document.getElementById("ads").innerHTML = this.innerHTML;
                    var mlng = this.getAttribute('data-lng'), // 经度
                        mlat = this.getAttribute('data-lat'); // 纬度
                    var mads = document.getElementById('ads');
                    mads.setAttribute('data-lng', mlng);
                    mads.setAttribute('data-lat', mlat);
                    //经纬度存入locaStorage
                    if(status == 1 || status ==3 || status ==5 || status ==7 || status ==9){
                        plus.storage.setItem('start_lon', mlng);
                        plus.storage.setItem('start_lat', mlat);
                    }else if(status == 2 || status ==4 || status ==6 || status ==8 || status ==10){
                        plus.storage.setItem('end_lon', mlng);
                        plus.storage.setItem('end_lat', mlat);
                    }
					var mpoint = new BMap.Point(mlng, mlat);
					        map.centerAndZoom(mpoint, 17);
					        map.clearOverlays();
					        var myIcon = new BMap.Icon("/assets/img/dingwei.png", new BMap.Size(30, 30), {
					            anchor: new BMap.Size(25, 50), // 指定定位位置
					            imageOffset: new BMap.Size(0, 0), // 设置图片偏移
					            // size:new BMap.Size(30, 30)
					            imageSize: new BMap.Size(30, 30)
					        });
					        var marker = new BMap.Marker(mpoint, {
					            icon: myIcon
					        });

					        // map.removeOverlay(preMarker);
					        map.addOverlay(marker);
                });
            }
        }
    }
    var local = new BMap.LocalSearch(map, options);
    local.searchNearby('大厦 , 广场', point, 1000);



    // 拖拽地图后获取中心点位置
    map.addEventListener("dragend", function showInfo() {
        var cp = map.getCenter(); // 当前选择的位置经纬度
        // alert(JSON.stringify(cp))

        var mpoint = new BMap.Point(cp.lng, cp.lat);
        map.centerAndZoom(mpoint, 17);
        map.clearOverlays();
        var myIcon = new BMap.Icon("/assets/img/dingwei.png", new BMap.Size(30, 30), {
            anchor: new BMap.Size(25, 50), // 指定定位位置
            imageOffset: new BMap.Size(0, 0), // 设置图片偏移
            // size:new BMap.Size(30, 30)
            imageSize: new BMap.Size(30, 30)
        });
        var marker = new BMap.Marker(mpoint, {
            icon: myIcon
        });

        // map.removeOverlay(preMarker);
        map.addOverlay(marker);

        // 定点转化成地址写入收货地址栏
        var geoc = new BMap.Geocoder();
        geoc.getLocation(cp, function(rs) {
            var addComp = rs.addressComponents;
            // console.log(JSON.stringify(rs));

            var mads_html = '<p class="ads1">' + rs.surroundingPois[0].title + '</p><p class="ads2">' + rs.address +
                '</p>';
            document.getElementById("ads").innerHTML = mads_html;

            var mads = document.getElementById('ads');
            mads.setAttribute('data-lng', rs.point.lng); // 经度
            mads.setAttribute('data-lat', rs.point.lat); // 纬度

            //经纬度存入locaStorage
            if(status == 1 || status ==3 || status ==5 || status ==7 || status ==9){
                // alert(13579);
                // alert(rs.point.lng);
                // alert(rs.point.lat);
                var lng = rs.point.lng+'',//数字加空字符转为字符串，字符串才能被存入locaStorage
                    lat = rs.point.lat+'';
                // alert('lng'+lng);
                // alert('lat'+lat);
                plus.storage.setItem('start_lon', lng);
                plus.storage.setItem('start_lat', lat);
                // alert('start_lon'+plus.storage.getItem('start_lon'));
                // alert('start_lat'+plus.storage.getItem('start_lat'));

            }else if(status == 2 || status ==4 || status ==6 || status ==8 || status ==10){
                // alert(24680);
                // alert(rs.point.lng);
                // alert(rs.point.lat);
                var lng = rs.point.lng+'',
                    lat = rs.point.lat+'';
                plus.storage.setItem('end_lon', lng);
                plus.storage.setItem('end_lat', lat);
                // alert(plus.storage.getItem('end_lon'));
                // alert(plus.storage.getItem('end_lat'));
            }
        });



        // 获取拖拽后定点地址附近的点
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
                    document.getElementById("fj-list").innerHTML = html;
                }
            }
        }
        var local = new BMap.LocalSearch(map, options);
        local.searchNearby('大厦 , 广场', mpoint, 600);
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
    // BMap.Convertor.translate(gpsPoint, 0, initMap2); //坐标转换
	initMap2(gpsPoint)
}

function initMap2(point) {
    map.centerAndZoom(point, 16);

}

// 点击城市选择城市
document.getElementById("vv-city").addEventListener('tap', function() {
    // console.log(locaCity)
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