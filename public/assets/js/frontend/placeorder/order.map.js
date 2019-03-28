// 只显示起终点
function map1(icon1, icon2, myP1, myP2, callBackFun) {
    // 地图
    var map = new BMap.Map("allmap");
    map.centerAndZoom(new BMap.Point(116.404, 39.915), 15);

    // var myP1 = new BMap.Point(116.380967, 34.913285); //起点
    // var myP2 = new BMap.Point(113.424374, 34.914668); //终点
    var myIcon1 = new BMap.Icon("/assets/img/icon-"+icon1+".png", new BMap.Size(25, 64), { //起点图片
        imageOffset: new BMap.Size(0, 0) //图片的偏移量。为了是图片底部中心对准坐标点。
    });

    var myIcon2 = new BMap.Icon("/assets/img/icon-"+icon2+".png", new BMap.Size(25, 64), { //终点图片
        imageOffset: new BMap.Size(0, 0) //图片的偏移量。为了是图片底部中心对准坐标点。
    });


    var myIcon = new BMap.Icon("/assets/img/icon-pn.png", new BMap.Size(40, 76), { //跑男图片
        imageOffset: new BMap.Size(0, 0) //图片的偏移量。为了是图片底部中心对准坐标点。
    });


    var riding = new BMap.RidingRoute(map, {
        renderOptions: {
            map: map,
            autoViewport: true
        }
    });
    // 自定义起终点图标
    riding.setMarkersSetCallback(function(r) {
        r[0].marker.setIcon(myIcon1);
        r[1].marker.setIcon(myIcon2);
    })
    riding.search(myP1, myP2);


    // 获取两点间的距离和时间
    var res = getRange1(map, myP1, myP2,callBackFun);
    //console.log(res);
    return res;
}

// 获取两点间的距离和时间
function getRange1(map, myP1, myP2 , callBackFun) {
    var time = '';
    var kilometre = '';
    var distance = '';
    var d= '',
        h = '',
        m = '';
    var searchComplete = function(results) {
        //console.log(results)
        if (transit.getStatus() != BMAP_STATUS_SUCCESS) {
            return;
        }
        var plan = results.getPlan(0);
        time = plan.getDuration(true);//获取时间
        kilometre = plan.getDistance(true);//获取距离
        if(kilometre.indexOf('米') != -1){
            distance = kilometre.split('米')[0]/1000;
        }else if(kilometre.indexOf('公里') != -1){
            distance = kilometre.split('公里')[0];
        }

        if(time.indexOf('天') != -1){
            d = time.split('天')[0];
            var dd = time.split('天')[1];
            if(dd.indexOf('小时') != -1){
                h = dd.split('小时')[0]
            }
        }else if(time.indexOf('小时') != -1){
            h = time.split('小时')[0];
            sessionStorage.setItem('h', h);
            m = time.split('小时')[1].split('分钟')[0];
            sessionStorage.setItem('m', m);
        }else if(time.indexOf('分钟') != -1){
            m = time.split('分钟')[0];
        }else{
            mui.alert('距离过远', '提示');
        }
        callBackFunction({'d':d, 'h':h, 'm':m, 'distance':distance});

    }
    var transit = new BMap.RidingRoute(map, {
        renderOptions: {
            map: map
        },
        onSearchComplete: searchComplete,
        onPolylinesSet: function() {

        },
        onMarkersSet: function(routes) {
            map.removeOverlay(routes[0].marker); //删除起点
            map.removeOverlay(routes[1].marker); //删除终点
        }
    });
    transit.search(myP1, myP2);

}


// 带有跑男位置
function map2(icon1, icon2, myP1, myP2) {
    var map = new BMap.Map("allmap");
    map.centerAndZoom(new BMap.Point(116.404, 39.915), 15);

    var myIcon1 = new BMap.Icon("/assets/img/icon-"+icon1+".png", new BMap.Size(25, 64), { //起点图片
        imageOffset: new BMap.Size(0, 0) //图片的偏移量。为了是图片底部中心对准坐标点。
    });

    var myIcon2 = new BMap.Icon("/assets/img/icon-"+icon2+".png", new BMap.Size(25, 64), { //终点图片
        imageOffset: new BMap.Size(0, 0) //图片的偏移量。为了是图片底部中心对准坐标点。
    });


    var myIcon = new BMap.Icon("/assets/img/icon-pn.png", new BMap.Size(40, 76), { //跑男图片
        imageOffset: new BMap.Size(0, 0) //图片的偏移量。为了是图片底部中心对准坐标点。
    });


    var riding = new BMap.RidingRoute(map, {
        renderOptions: {
            map: map,
            autoViewport: true
        }
    });
    // 自定义起终点图标
    riding.setMarkersSetCallback(function(r) {
        r[0].marker.setIcon(myIcon1);
        r[1].marker.setIcon(myIcon2);
    })
    riding.search(myP1, myP2);

    window.run = function(output) {
        console.log(output)
        var time = output.split('分钟')[0];


        var riding2 = new BMap.RidingRoute(map); //骑行实例
        riding2.search(myP1, myP2);
        riding2.setSearchCompleteCallback(function() {
            var pts = riding2.getResults().getPlan(0).getRoute(0).getPath(); //通过骑行实例，获得一系列点的数组
            // console.log(pts)
            var paths = pts.length; //获得有几个点
            time = time*60*1000/paths;
            console.log(time)
            var carMk = new BMap.Marker(pts[0], {
                icon: myIcon
            });
            map.addOverlay(carMk);
            i = 0;

            function resetMkPoint(i) {
                carMk.setPosition(pts[i]);
                if (i < paths) {
                    setTimeout(function() {
                        i++;
                        resetMkPoint(i);
                    }, time);
                }
            }
            setTimeout(function() {
                resetMkPoint(0);
            }, 100)

        });
    }


    // 获取两点间的距离和时间
    getRange(map, myP1, myP2);

}


// 带跑男获取两点间的距离和时间
function getRange(icon1, icon2, map, myP1, myP2) {
    var output = ''
    var searchComplete = function(results) {
        if (transit.getStatus() != BMAP_STATUS_SUCCESS) {
            return;
        }
        var plan = results.getPlan(0);
        output += plan.getDuration(true) + "\n"; //获取时间
        output += "总路程为：";
        output += plan.getDistance(true) + "\n"; //获取距离
    }
    var transit = new BMap.RidingRoute(map, {
        renderOptions: {
            map: map
        },
        onSearchComplete: searchComplete,
        onPolylinesSet: function() {
                alert(output)
        },
        onMarkersSet: function(routes) {
            map.removeOverlay(routes[0].marker); //删除起点
            map.removeOverlay(routes[1].marker); //删除终点
        }
    });
    transit.search(myP1, myP2);
    setTimeout(function() {
        run(output);
    }, 1500);
}


// 只有收货地址
//var mpoint = new BMap.Point(118.380967, 39.913285);
//var icon = 'shou';
//map3(icon, mpoint);
function map3(icon, pt) {
    // 地图
    var map = new BMap.Map("allmap");
    map.centerAndZoom(pt, 15);

    var myIcon = new BMap.Icon("/assets/img/icon-"+icon+".png", new BMap.Size(25, 64), { //排队图标
        imageOffset: new BMap.Size(0, 0) //图片的偏移量。为了是图片底部中心对准坐标点。
    });

    var marker2 = new BMap.Marker(pt,{icon:myIcon});  // 创建标注
    map.addOverlay(marker2);              // 将标注添加到地图中
}