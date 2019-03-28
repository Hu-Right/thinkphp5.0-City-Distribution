mui.init();
document.getElementsByClassName('map1')[0].style.height = window.innerHeight - 148 + 'px';
document.getElementsByClassName('map2')[0].style.height = 300 + 'px';
var tx = document.getElementsByClassName('touxiang')[0];
tx.addEventListener('tap', function(e) {
	(window.event || e).cancelBubble = true;
	mui('.mui-off-canvas-wrap').offCanvas().toggle();

});

var xx = document.getElementsByClassName('xx')[0];
xx.addEventListener('tap', function() {
	mui.openWindow({
		url: 'personal.html',
		id: 'personal'
	});
});
document.getElementsByClassName('order-start')[0].addEventListener('tap', function() {
    this.className = 'order-start act';
    document.getElementsByClassName('order-end')[0].className = 'order-end';
    mui.ajax('/vvptd/order/is_open',{
        type:'post',
        data:{
            type:1
        },
        success:function(data){
           if(data.code==1){
               mui.alert('开启成功');
           }else{
               mui.alert(data.msg);
           }
        }
    })

});
document.getElementsByClassName('order-end')[0].addEventListener('tap', function() {
    this.className = 'order-end act';
    document.getElementsByClassName('order-start')[0].className = 'order-start';
    mui.ajax('/vvptd/order/is_open',{
        type:'post',
        data:{
            type:'0'
        },
        success:function(data){
            if(data.code==1){
                mui.alert('关闭成功');

            }else{
                mui.alert(data.msg);

            }
        }
    })

});
document.getElementsByClassName('cancel-order')[0].addEventListener('tap', function() {
    document.getElementsByClassName('order-qiang')[0].style.display = 'none';
});
document.getElementsByClassName('get-btn-order')[0].addEventListener('tap', function() {
    document.getElementsByClassName('mapPage')[0].style.display = 'none';
    document.getElementsByClassName('order-main')[0].style.display = 'block';
    document.getElementsByClassName('order-qiang')[0].style.display = 'none';
});
document.getElementsByClassName('home-btn')[0].addEventListener('tap', function() {
    document.getElementsByClassName('mapPage')[0].style.display = 'none';
    document.getElementsByClassName('order-main')[0].style.display = 'none';
});
document.getElementsByClassName('get-order-btn')[0].addEventListener('tap', function() {
    document.getElementsByClassName('mapPage')[0].style.display = 'block';
});
mui('.mui-scroll-wrapper').scroll({
	deceleration: 0.0005 //flick 减速系数，系数越大，滚动速度越慢，滚动距离越小，默认值0.0006
});
var is_status = 0;
function play(arr){
    mui.ajax('/vvptd/order/audioStart',{
        type:'post',
        data:{
            order_id:arr.id
        },
        success:function(data){
            //bdtts_div_id
            var daxiao = baseUrl+"/assets/vvptd/ding.mp3";
            var daxiao = new Audio(daxiao);
            daxiao .play(); //播放
            daxiao.loop = false;
            daxiao.addEventListener('ended', function () {

                var ttsDiv = document.getElementById('bdtts_div_id');
                // 这样就可实现播放内容的替换了
                var au1 = '<audio id="tts_autio_id" autoplay="autoplay">';
                var sss = '<source id="tts_source_id" src="'+data+'" type="audio/mpeg">';
                var eee = '<embed id="tts_embed_id" height="0" width="0" src="">';
                var au2 = '</audio>';
                ttsDiv.innerHTML = au1 + sss + eee + au2;
                ttsAudio = document.getElementById('tts_autio_id');
                ttsAudio.play();
                ttsAudio.addEventListener('ended', function () {
                        is_status = 0;
                        $('.order-qiang').hide();
                        mui.ajax('/vvptd/order/thisOrderStatus',{
                            type:'post',
                            data:{
                                type:0,
                                order_id:arr.id
                            },
                            success:function(data){
                                autoOrder();
                            },error:function(){
                                autoOrder();
                            }
                        })
                        
                }, false);
                
            }, false);
        }
    })
    

}
autoOrder();
//自动接单
function autoOrder(){
    if(typeof(EventSource)!=="undefined")
    {
            
            var source=new EventSource("/vvptd/order/noticeOrderRunMen");

            source.onmessage=function(event)
            {
                
                if(is_status == 0){
                    var arr = eval('('+event.data+')');
                    console.log(arr.msg);
                    
                    if(arr.msg == 'success'){
                        source.close();
                        is_status=1;
                        //关闭自动刷新地图事件
                        clearInterval();
                        mui.ajax('/vvptd/order/order_detail',{
                            type:'post',
                            data:{
                                order_id:arr.order_id
                            },
                            success:function(data){
                                var arr = data.data;
                                var html = '';
                                html+='<div class="mui-table-view order-qiang-msg">';
                                html+='<div class="mui-table-view-cell order-number">';
                                html+='<div class="order-number-msg">';
                                html+='<div class="mui-pull-left">';
                                html+='<img src="'+arr.runmen.avatar+'" alt="">';
                                html+='</div>';
                                html+='<div class="mui-pull-left">';
                                html+='<p class="order-number-name">'+arr.runmen.nickname+'</p>';
                                html+='<p id="phoneNumber">'+arr.runmen.mobile+'</p>';
                                html+='</div>';
                                if(arr.type == 1) {
                                    html+='<div class="mui-pull-right tangle buy-tangle">';
                                    html+='<span>买</span>';
                                    html+='</div>';
                                }else if(arr.type == 2){
                                    html+='<div class="mui-pull-right tangle song-tangle">';
                                    html+='<span>送</span>';
                                    html+='</div>';
                                }else if(arr.type == 3){
                                    html+='<div class="mui-pull-right tangle ban-tangle">';
                                    html+='<span>帮</span>';
                                    html+='</div>';
                                }else if(arr.type == 5){
                                    html+='<div class="mui-pull-right tangle song-tangle">';
                                    html+='<span>取</span>';
                                    html+='</div>';
                                }else{
                                    html+='<div class="mui-pull-right tangle pai-tangle">';
                                    html+='<span>排</span>';
                                    html+='</div>';
                                }
                                html+='</div>';
                                html+='</div>';
                                html+='<div class="mui-table-view-cell product-type">';
                                html+='<p>产品类型：<span> '+arr.type_info.service_name+'</span></p>';
                                html+='</div>';
                                html+='<div class="mui-table-view-cell product-type">';
                                html+='<div class="customer-request mui-row">';
                                html+='<p class="mui-col-xs-4 mui-col-sm-4">客户要求：</p>';
                                html+='<p class="mui-col-xs-8 mui-col-sm-8">'+arr.content.details+'</p>';
                                html+='</div>';
                                html+='</div>';
                                html+='<div class="mui-table-view-cell buy-address">';
                                html+='<p> <span class="order-type buy">买</span> 购买地址:</p>';
                                html+='<p>'+arr.start_address+'</p>';
                                html+='</div>';
                                html+='<div class="mui-table-view-cell buy-address">';
                                html+='<p> <span class="order-type receive">收</span> 收获地址:</p>';
                                html+='<p>'+arr.end_address+'</p>';
                                html+='</div>';
                                html+='<div class="mui-table-view-cell buy-msg">';
                                html+='<p>购买时间：<span> ';
                                if(arr.content.service_time ==''||arr.content.service_time==undefined){
                                    html+='立即';
                                }else{
                                    html+=arr.content.service_time;
                                }
                                html+='</span></p>';
                                html+='</div>';
                                if(arr.type == 1 || arr.type == 2 ){
                                    html+='<div class="mui-table-view-cell buy-msg">';
                                    html+='<p>商品预付：<span> ';
                                    if(arr.content.prepayment ==''||arr.content.prepayment==undefined){
                                        html+='0';
                                    }else{
                                        html+=arr.content.prepayment;
                                    }

                                    html+'元</span></p>';
                                    html+='</div>';
                                    html+='<div class="mui-table-view-cell buy-msg">';
                                    html+='<p>距离：<span> ';
                                    if(arr.start_end_distance ==''){
                                        html+='0';
                                    }else{
                                        html+=arr.start_end_distance;
                                    }
                                    html+='（公里）</span></p>';
                                    html+='</div>';
                                }

                                html+='<div class="mui-table-view-cell buy-msg buy-msg-last">';
                                html+='<div class="order-cost">';
                                html+='<p>订单金额：<span>'+arr.money+'元</span></p>';
                                html+='<p>(含商品预付费用)</p>';
                                html+='</div>';
                                html+='</div>';
                                html+='<div class="msg-btm get-btn">';
                                html+='<button type="button" class="mui-btn cancel-order">取消</button>';
                                html+='<button type="button" class="mui-btn get-btn-order qing-btn">抢单</button>';
                                html+='</div>';
                                html+='</div>';
                                html+='</div>';
                                $('.order-qiang').html(html);
                                $('.order-qiang').show();
                                play(arr);
                                document.getElementsByClassName('cancel-order')[0].addEventListener('tap', function() {
                                    mui.ajax('/vvptd/order/thisOrderStatus',{
                                        type:'post',
                                        data:{
                                            type:0,
                                            order_id:arr.id
                                        },
                                        success:function(data){
                                            autoOrder();
                                            document.getElementsByClassName('order-qiang')[0].style.display = 'none';
                                        }
                                    })
                                });
                                document.getElementsByClassName('qing-btn')[0].addEventListener('tap', function() {
                                    mui.ajax('/vvptd/order/manual',{
                                        type:'post',
                                        data:{
                                            order_id:arr.id
                                        },
                                        success:function(data){
                                            if(data.code == 1){
                                                document.getElementsByClassName('order-qiang')[0].style.display = 'none';
                                                mui.alert('抢单成功');
                                                orderSuccess(arr);
                                            }else{
                                                mui.alert(data.msg);
                                                document.getElementsByClassName('order-qiang')[0].style.display = 'none';
                                            }

                                        }
                                    })

                                });

                            }
                        })
                    }
                }
            }
        
    }
    else
    {
        alert('no');
        return false;
    }
}
//我已就位
document.getElementById('woyijiuwei').addEventListener('tap', function() {
    var order_id = $('#order_id_s').val();
    layer.open({
        type: 2,
        shadeClose:false
    });
    var is_admin = $('#is_admin').val();
    if(is_admin == 1){
        var mobile = $('#phoneNumber').html();
        $.ajax({
            type: "POST", //用POST方式传输
            dataType: "text", //数据格式:JSON
            url: '/api/sms/send', //目标地址
            data: {mobile:mobile,event:'vvptd-send'}, //post携带数据
            success: function (data){
                var datas = JSON.parse(data);
                layer.closeAll();
                if(datas.code == 1){
                    mui.alert('就位成功，请开始配送！');
                    $('#woyijiuwei').attr('disabled', true);
                    $('#paizhao').removeAttr('disabled');
                    $('#paizhao').css('background','chartreuse');

                    ///chartreuse
                }else{
                    layer.open({
                        content: datas.msg
                        , skin: 'msg'
                        , time: 2 //2秒后自动关闭
                    });
                }

            } //请求成功时执行的函数
        });
    }else {
        mui.ajax('/vvptd/order/addOrderCode', {
            type: 'post',
            data: {
                order_id: order_id
            }, success: function (data) {
                if (data.code == 1) {
                    layer.closeAll();
                    mui.alert('就位成功，请开始配送！');
                    $('#woyijiuwei').attr('disabled', true);
                    $('#paizhao').removeAttr('disabled');
                    $('#paizhao').css('background','chartreuse');
                } else {
                    mui.alert('就位失败');
                }
            }
        })
    }
});
//确认验证码
document.getElementById('code_true').addEventListener('tap', function() {
    var order_id = $('#order_id_s').val();
    var code = $('#codes').val();
    if(code == ''){
        mui.alert('验证码不能为空！');
        return false;
    }
    var is_admin = $('#is_admin').val();
    mui.ajax('/vvptd/order/checkOrderCode',{
        type:'post',
        data:{
            order_id:order_id,
            code:code,
            is_admin:is_admin
        },success:function(data){
            if(data.code ==1){
                mui.alert('验证成功',function(){
                    $('.order-main').hide();
                    $('.mapPage').show();
                    $('#codes').val('');
                    timeoutOrder();
                    plus.geolocation.getCurrentPosition(translatePoint, function(e) {
                        mui.toast("异常:" + e.message);
                    });
                });
            }else{
                mui.alert('验证码错误');
            }
        }
    })
});
/*跳转*/
//页面跳转

document.getElementById('message').addEventListener('tap', function() {
	mui.openWindow({
		url: baseUrl+'/vvptd/member/news'
	})
});
document.getElementById('balance').addEventListener('tap', function() {
    mui.openWindow({
        url: baseUrl+'/vvptd/member/myAccount'
    })
});
document.getElementById('order').addEventListener('tap', function() {
	mui.openWindow({
		url:baseUrl+'/vvptd/order/index'
	})
});
document.getElementById('vvteam').onclick =  function() {
	mui.openWindow({
		url:baseUrl+'/vvptd/team/index'
	})
};
document.getElementById('setting').addEventListener('tap', function() {
	mui.openWindow({
		url: baseUrl+'/vvptd/setting/index',
		id: 'setting'
	})
});
document.getElementById('task').addEventListener('tap', function() {
	mui.openWindow({
		url: baseUrl+'/vvptd/member/getTaskList'
	})
});
document.getElementById('grzx').onclick =  function() {
    mui.openWindow({
        url: baseUrl+'/vvptd/member/personal'
    })
};
document.getElementById('remark').addEventListener('tap', function() {
    mui.openWindow({
        url: baseUrl+'/vvptd/member/pingjia'
    })
});
document.getElementById('vvteam').addEventListener('tap', function() {
    mui.openWindow({
        url: baseUrl+'/vvptd/team/index'
    })
});
document.getElementById('share').addEventListener('tap', function() {
    mui.openWindow({
        url: baseUrl+'/vvptd/member/shareMember'
    })
});
document.getElementById('help').addEventListener('tap', function() {
    mui.openWindow({
        url: baseUrl+'/vvptd/help/help_list'
    })
});
document.getElementById('relitu').addEventListener('tap', function() {
    mui.openWindow({
        url: baseUrl+'/vvptd/member/hotMap'
    })
});
document.getElementById('course').addEventListener('tap', function() {
    mui.openWindow({
        url: baseUrl+'/vvptd/member/vvptHelp'
    })
});
/*跳转*/
//拨打电话
document.getElementById("telephone").addEventListener('tap', function() {
    var btnArray = ['拨打', '取消'];
    var phone = document.getElementById('phoneNumber').innerText;
    mui.confirm('是否拨打' + phone + '?', '提示', btnArray, function(e) {
        if (e.index == 0) {
            plus.device.dial(phone, false);
        }
    });
});
/*修改头像上传*/
$('.files').on('change',function(){
    var formdata = new FormData();
    formdata.append('file',this.files[0]);
    mui.ajax('/vvptd/member/upload',{
        type:'post',
        data:formdata,
        processData:false,
        contentType:false,
        success: function (data) {
            if(data.code == 1){
                mui.ajax('/vvptd/member/changeHeadimg', {
                    type: 'post',
                    data:{
                        img:data.data.url
                    },
                    success: function (data) {
                        if (data.code == 1) {

                            $('.imageup').attr('src',data.data);
                            $('.touxiang').attr('src',data.data);
                        } else {
                            layer.open({
                                content: data.msg
                                , skin: 'msg'
                                , time: 2 //2秒后自动关闭

                            });
                        }
                    }
                })
            }else{
                layer.open({
                    content:data.msg
                    , skin: 'msg'
                    , time: 2 //2秒后自动关闭

                });
            }
        },
        error: function (data) {
            layer.open({
                content:'网络繁忙'
                , skin: 'msg'
                , time: 2 //2秒后自动关闭

            });
        }
    })
})
/*修改头像上传*/
function plusReady() {
	// 弹出系统选择按钮框
	mui("body").on("tap", ".imageup", function() {
		page.imgUp();
	})

}
var page = null;
page = {
	imgUp: function() {
		var m = this;
		plus.nativeUI.actionSheet({
			cancel: "取消",
			buttons: [{
					title: "拍照"
				},
				{
					title: "从相册中选择"
				}
			]
		}, function(e) { //1 是拍照  2 从相册中选择
			switch(e.index) {
				case 1:
					appendByCamera();
					break;
				case 2:
					appendByGallery();
					break;
			}
		});
	}
	//摄像头
}

// 拍照添加文件
function appendByCamera() {
	plus.camera.getCamera().captureImage(function(e) {
		console.log(e);
		plus.io.resolveLocalFileSystemURL(e, function(entry) {
			var path = entry.toLocalURL();
			document.getElementById("headimg").src = path;
			//就是这里www.bcty365.com
		}, function(e) {
			mui.toast("读取拍照文件错误：" + e.message);
		});

	});
}
// 从相册添加文件
function appendByGallery() {
	plus.gallery.pick(function(path) {
		document.getElementById("headimg").src = path;

	});
}

//          //服务端接口路径
//          var server = "http://www.test.cn/aaa.php";
//          //获取图片元素
//          var files = document.getElementById('headimg');
//          // 上传文件
//          function upload(){
//              console.log(files.src);
//              var wt=plus.nativeUI.showWaiting();
//              var task=plus.uploader.createUpload(server,
//                  {method:"POST"},
//                  function(t,status){ //上传完成
//                      if(status==200){
//                          alert("上传成功："+t.responseText);
//                          wt.close(); //关闭等待提示按钮
//                      }else{
//                          alert("上传失败："+status);
//                          wt.close();//关闭等待提示按钮
//                      }
//                  }
//              );
//              //添加其他参数
//              task.addData("name","test");
//              task.addFile(files.src,{key:"dddd"});
//              task.start();
//          }
if(window.plus) {
	plusReady();
} else {
	document.addEventListener("plusready", plusReady, false);
}
//clearInterval();
var interval;
function timeoutOrder(){
    interval = setInterval(function(){
        checkOrder();
        plus.geolocation.getCurrentPosition(translatePoint, function(e) {
            mui.toast("异常:" + e.message);
        });
    },15000);
}

mui.plusReady(function() {
	// 获取设备定位信息
    document.getElementById("dingwei").addEventListener('tap', function() {
        checkOrder();
        autoOrder();
        timeoutOrder();
        plus.geolocation.getCurrentPosition(translatePoint, function(e) {
            mui.toast("异常:" + e.message);
        });
    })
    //plus.geolocation.getCurrentPosition(translatePoint2, function(e) {
    //    mui.toast("异常:" + e.message);
    //});
    document.getElementById("new-dingwei").addEventListener('tap', function() {
        checkOrder();
        autoOrder();
        plus.geolocation.getCurrentPosition(translatePoint, function(e) {
            mui.toast("异常:" + e.message);
        });
    })

});
var locaCity = '';

function translatePoint(position) {
	var currentLon = position.coords.longitude;
	var currentLat = position.coords.latitude;

	//alert(position.address.province+"-"+position.address.city+"-"+position.address.district+"-"+position.address.street)
	var gpsPoint = new BMap.Point(currentLon, currentLat);
	BMap.Convertor.translate(gpsPoint, 0, initMap); //坐标转换

}
function initMap(point) {

    var urls = $('.touxiang').attr('src');
	map = new BMap.Map("allmap"); //创建地图
	map.centerAndZoom(point,16);
    //创建自定义覆盖物
    var myCompOverlay = new ComplexCustomOverlay(point,urls,0);
    map.addOverlay(myCompOverlay); //将标注添加到地图中
    getAddress(point.lng,point.lat);
    getPoint(point.lng,point.lat);
    updateRunW(point.lng,point.lat);
	// 拖拽地图后获取中心点位置
	map.addEventListener("dragend", function showInfo() {
        map.clearOverlays();
        var cp = map.getCenter();
        var myCompOverlay = new ComplexCustomOverlay(cp,urls,0);
        map.addOverlay(myCompOverlay); //将标注添加到地图中
        getAddress(cp.lng,cp.lat);
        getPoint(cp.lng,cp.lat);
	});
}
//更新跑男位置
function updateRunW(lng,lat){
    mui.ajax('/vvptd/order/updateRunmenPlace', {
        type: 'post',
        data:{
            lng:lng,
            lat:lat
        },
        success: function (data) {
        }
    })
}
// 添加标注
function addMarker(point, index,url) {
    var myIcon = new BMap.Icon(url,
        new BMap.Size(20, 33), {
            offset: new BMap.Size(10, 25),
            imageOffset: new BMap.Size(0, 0 - index * 25)

        });
    var marker = new BMap.Marker(point, {
        icon: myIcon
    });
    map.addOverlay(marker);
    marker.addEventListener('click', function() {
        document.getElementsByClassName('order-qiang')[0].style.display = 'block';
    });
    return marker;
}

//获取当前经纬度的详细地址
function getAddress(lon,lat){
    var myGeo = new BMap.Geocoder();
    // 根据坐标得到地址描述
    myGeo.getLocation(new BMap.Point(lon, lat), function(result){
        if (result){
            $('#now_address').html(result.address);
        }
    });
}
//获取方圆5000米的 订单跟跑男的坐标
function getPoint(lon,lat){
    mui.ajax('/vvptd/order/getOrderAndRunmen',{
        type:'post',
        data:{
            lon:lon,
            lat:lat
        },
        async:false,
        success: function (data) {
            var arr = data.data.runmen;
            var order = data.data.order;
            if(arr.length>0){
                var url = '';
                var rid = $('#rid').val();
                for(var i =0;i<arr.length;i++){
                    var pt = new BMap.Point(arr[i].lon,arr[i].lat);
                    if(arr[i].id != rid){
                        var myCompOverlay = new ComplexCustomOverlay(pt,baseUrl+"/assets/vvptd/member/img/touxiang.png",0);
                        map.addOverlay(myCompOverlay); //将标注添加到地图中
                    }

                    //var marker = new BMap.Marker(pt);  // 创建标注
                    //map.addOverlay(marker);
                }
            }
            if(order.length>0){
                //自定义图标地址
                var url = '';
                for(var i =0;i<order.length;i++){
                    if(order[i].type == 4){
                        var pt = new BMap.Point(order[i].end_lon,order[i].end_lat);
                    }else{
                        var pt = new BMap.Point(order[i].start_lon,order[i].start_lat);
                    }

                    var myCompOverlay = new ComplexCustomOverlay(pt,order[i].type,1,order[i].id);
                    map.addOverlay(myCompOverlay); //将标注添加到地图中

                }
            }
        }
    })
}
// 新的重新定位
//function translatePoint2(position) {
//	var currentLon = position.coords.longitude;
//	var currentLat = position.coords.latitude;
//	var gpsPoint = new BMap.Point(currentLon, currentLat);
//	BMap.Convertor.translate(gpsPoint, 0, initMap2); //坐标转换
//}

function initMap2(start,end) {
    mp = new BMap.Map("allmap1"); //创建地图
    mp.centerAndZoom(start, 16);
    var myP1 = new BMap.Point(start.lng,start.lat); //起点
    var myP2 = new BMap.Point(end.lng,end.lat); //终点
// 	var myIcon = new BMap.Icon("http://lbsyun.baidu.com/jsdemo/img/Mario.png", new BMap.Size(32, 70), { //小车图片
// 		//offset: new BMap.Size(0, -5),    //相当于CSS精灵
// 		imageOffset: new BMap.Size(0, 0) //图片的偏移量。为了是图片底部中心对准坐标点。
// 	});
    var driving2 = new BMap.DrivingRoute(mp, {
        renderOptions: {
            map: mp,
            autoViewport: true
        }
    }); //驾车实例
    driving2.search(myP1, myP2); //显示一条公交线路

    window.run = function() {
        var driving = new BMap.DrivingRoute(mp); //驾车实例
        driving.search(myP1, myP2);
        driving.setSearchCompleteCallback(function() {
            var pts = driving.getResults().getPlan(0).getRoute(0).getPath(); //通过驾车实例，获得一系列点的数组
            var paths = pts.length; //获得有几个点





        });
    }

    setTimeout(function() {
        run();
    }, 1500);


}
//检测是否已经有单子
function checkOrder(){
$('.shiwu').hide();
    mui.ajax('/vvptd/order/checkOrder',{
        type:'post',
        data:{
        },
        success:function(data){
            var arr = data.data;
            if(arr !=null || arr!= '' ){
                if(arr.status == 1){
                    clearInterval(interval);
                    //说明有已结的单子
                    orderSuccess(arr);
                }else if(arr.status == 0 && arr.rid!=0){
                    $('#is_admin').val(arr.content.is_admin);
                    //未接的单子
                    var html = '';
                    html+='<div class="mui-table-view order-qiang-msg">';
                    html+='<div class="mui-table-view-cell order-number">';
                    html+='<div class="order-number-msg">';
                    html+='<div class="mui-pull-left">';
                    if(arr.content.is_admin ==1){
                        html += '<img src="'+baseUrl+'/vvptd/member/img/touxiang.png" alt="">';
                        html += '</div>';
                        html += '<div class="mui-pull-left">';
                        html += '<p class="order-number-name">' + arr.content.name + '</p>';
                        html += '<p id="phoneNumber">' + arr.content.mobile + '</p>';
                        html += '</div>';
                    }else {
                        html += '<img src="' + arr.runmen.avatar + '" alt="">';
                        html += '</div>';
                        html += '<div class="mui-pull-left">';
                        html += '<p class="order-number-name">' + arr.runmen.nickname + '</p>';
                        html += '<p id="phoneNumber">' + arr.runmen.mobile + '</p>';
                        html += '</div>';
                    }
                    if(arr.type == 1) {
                        html+='<div class="mui-pull-right tangle buy-tangle">';
                        html+='<span>买</span>';
                        html+='</div>';
                    }else if(arr.type == 2){
                        html+='<div class="mui-pull-right tangle song-tangle">';
                        html+='<span>送</span>';
                        html+='</div>';
                    }else if(arr.type == 3){
                        html+='<div class="mui-pull-right tangle ban-tangle">';
                        html+='<span>帮</span>';
                        html+='</div>';
                    }else if(arr.type == 5){
                            html+='<div class="mui-pull-right tangle song-tangle">';
                            html+='<span>取</span>';
                            html+='</div>';
                    }else{
                        html+='<div class="mui-pull-right tangle pai-tangle">';
                        html+='<span>排</span>';
                        html+='</div>';
                    }
                    html+='</div>';
                    html+='</div>';
                    html+='<div class="mui-table-view-cell product-type">';
                    html+='<p>产品类型：<span> '+arr.type_info.service_name+'</span></p>';
                    html+='</div>';
                    html+='<div class="mui-table-view-cell product-type">';
                    html+='<div class="customer-request mui-row">';
                    html+='<p class="mui-col-xs-4 mui-col-sm-4">客户要求：</p>';
                    html+='<p class="mui-col-xs-8 mui-col-sm-8">'+arr.content.details+'</p>';
                    html+='</div>';
                    html+='</div>';
                    html+='<div class="mui-table-view-cell buy-address">';
                    html+='<p> <span class="order-type buy">买</span> 购买地址:</p>';
                    html+='<p>'+arr.start_address+'</p>';
                    html+='</div>';
                    html+='<div class="mui-table-view-cell buy-address">';
                    html+='<p> <span class="order-type receive">收</span> 收获地址:</p>';
                    html+='<p>'+arr.end_address+'</p>';
                    html+='</div>';
                    html+='<div class="mui-table-view-cell buy-msg">';
                    html+='<p>购买时间：<span> ';
                    if(arr.content.service_time ==''||arr.content.service_time==undefined){
                        html+='立即';
                    }else{
                        html+=arr.content.service_time;
                    }
                    html+='</span></p>';
                    html+='</div>';
                    if(arr.type == 1 || arr.type == 2 ){
                        html+='<div class="mui-table-view-cell buy-msg">';
                        html+='<p>商品预付：<span> ';
                        if(arr.content.prepayment ==''||arr.content.prepayment==undefined){
                            html+='0';
                        }else{
                            html+=arr.content.prepayment;
                        }

                        html+'元</span></p>';
                        html+='</div>';
                        html+='<div class="mui-table-view-cell buy-msg">';
                        html+='<p>距离：<span> ';
                        if(arr.start_end_distance ==''){
                            html+='0';
                        }else{
                            html+=arr.start_end_distance;
                        }
                        html+='（公里）</span></p>';
                        html+='</div>';
                    }

                    html+='<div class="mui-table-view-cell buy-msg buy-msg-last">';
                    html+='<div class="order-cost">';
                    html+='<p>订单金额：<span>'+arr.money+'元</span></p>';
                    html+='<p>(含商品预付费用)</p>';
                    html+='</div>';
                    html+='</div>';
                    html+='<div class="msg-btm get-btn">';
                    html+='<button type="button" class="mui-btn cancel-order">取消</button>';
                    html+='<button type="button" class="mui-btn get-btn-order qing-btn">抢单</button>';
                    html+='</div>';
                    html+='</div>';
                    html+='</div>';

                    $('.order-qiang').html(html);
                    $('.order-qiang').show();
                    document.getElementsByClassName('cancel-order')[0].addEventListener('tap', function() {
                        layer.open({
                            type: 2,
                            shadeClose:false
                        });
                        mui.ajax('/vvptd/order/thisOrderStatus',{
                            type:'post',
                            data:{
                                type:0,
                                order_id:arr.id
                            },
                            success:function(data){
                                layer.closeAll();
                                document.getElementsByClassName('order-qiang')[0].style.display = 'none';
                            }
                        })
                    });
                    document.getElementsByClassName('qing-btn')[0].addEventListener('tap', function() {
                        layer.open({
                            type: 2,
                            shadeClose:false
                        });
                        mui.ajax('/vvptd/order/manual',{
                            type:'post',
                            data:{
                                order_id:arr.id
                            },
                            success:function(data){
                                layer.closeAll();
                                document.getElementsByClassName('order-qiang')[0].style.display = 'none';
                                if(data.code == 1){
                                    mui.alert(data.msg);
                                    orderSuccess(arr);
                                }else{
                                    mui.alert(data.msg);
                                }
                            }
                        })

                    });
                }
            }
        }
    })
}
//接单成功后展示页面
function orderSuccess(arr){
    clearInterval();
    var start = new BMap.Point(arr.start_lon,arr.start_lat);
    var end = new BMap.Point(arr.end_lon,arr.end_lat);
    initMap2(start,end);
    $('#is_admin').val(arr.content.is_admin);
    $('#a-mai').html(arr.type_info.service_name);
    $('#a-ordernum').html(arr.order_num);
    $('#a-time').html(arr.create_time);
    $('#a-img').attr('src',arr.runmen.avatar);
    $('#a-name').html(arr.runmen.nickname);
    $('#phoneNumber').html(arr.runmen.mobile);
    $('#a-type').html(arr.type_info.service_name);
    $('#a-detail').html(arr.content.details);
    $('#a-address').html(arr.start_address);
    $('#a-endaddress').html(arr.end_address);
    $('#order_id_s').val(arr.id);
    if(arr.content.service_time ==''||arr.content.service_time==undefined){
        $('#a-buytime').html('立即');
    }else{
        $('#a-buytime').html(arr.content.service_time);
    }
    if(arr.content.prepayment ==''||arr.content.prepayment==undefined){
        $('#a-money').html(0);
    }else{
        $('#a-money').html(arr.content.prepayment+'元');
    }
    if(arr.start_end_distance ==''){
        $('#a-juli').html(0);
    }else{
        $('#a-juli').html(arr.start_end_distance+'(公里)');
    }
    $('#a-orderprice').html(arr.money);
    $('.order-main').show();
    $('.mapPage').hide();
}
//1、定义构造函数并继承Overlay
//定义自定义覆盖物的构造函数
function ComplexCustomOverlay(point, url, type, orderId) {
    this._point = point;
    this._url = url;
    this._type = type;
    if(orderId != undefined){
        this._id = orderId
    }
}
// 继承API的BMap.Overlay
ComplexCustomOverlay.prototype = new BMap.Overlay();
//2、初始化自定义覆盖物
// 实现初始化方法
ComplexCustomOverlay.prototype.initialize = function(mp) {
    // 保存map对象实例
    this._map = mp;
    // 创建div元素，作为自定义覆盖物的容器
    if(this._type == 1) {
        var div = this._div = document.createElement("div");
        div.style.position = "absolute";
        div.style.zIndex = BMap.Overlay.getZIndex(this._point.lat); //聚合功能?
        // 可以根据参数设置元素外观
        div.style.height = "20px";
        div.style.width = "33px";
        var arrow = this._arrow = document.createElement("img");
        arrow.style.position = 'absolute';
        arrow.style.width = "20px";
        arrow.style.height = "33px";
        arrow.dataid = this._id;
        switch (this._url) {
            case 1:
                arrow.src = baseUrl+"/assets/vvptd/member/img/mai.png";
                break;
            case 2:
                arrow.src = baseUrl+"/assets/vvptd/member/img/song.png";
                break;
            case 3:
                arrow.src = baseUrl+"/assets/vvptd/member/img/ban.png";
                break;
            case 4:
                arrow.src = baseUrl+"/assets/vvptd/member/img/pai.png";
                break;
            case 5:
                arrow.src = baseUrl+"/assets/vvptd/member/img/qu.png";
                break;
        }
        div.appendChild(arrow);
        div.addEventListener('touchstart', function() {
            clearInterval();
           var order_id = arrow.dataid;
            mui.ajax('/vvptd/order/order_detail',{
                type:'post',
                data:{
                    order_id:order_id
                },
                success:function(data){
                    var arr = data.data;
                    var html = '';
                    html+='<div class="mui-table-view order-qiang-msg">';
                    html+='<div class="mui-table-view-cell order-number">';
                    html+='<div class="order-number-msg">';
                    html+='<div class="mui-pull-left">';
                    html+='<img src="'+arr.runmen.avatar+'" alt="">';
                    html+='</div>';
                    html+='<div class="mui-pull-left">';
                    html+='<p class="order-number-name">'+arr.runmen.nickname+'</p>';
                    html+='<p id="phoneNumber">'+arr.runmen.mobile+'</p>';
                    html+='</div>';
                     if(arr.type == 1) {
                         html+='<div class="mui-pull-right tangle buy-tangle">';
                         html+='<span>买</span>';
                         html+='</div>';
                     }else if(arr.type == 2){
                         html+='<div class="mui-pull-right tangle song-tangle">';
                         html+='<span>送</span>';
                         html+='</div>';
                     }else if(arr.type == 3){
                         html+='<div class="mui-pull-right tangle ban-tangle">';
                         html+='<span>帮</span>';
                         html+='</div>';
                     }else if(arr.type == 5){
                         html+='<div class="mui-pull-right tangle song-tangle">';
                         html+='<span>取</span>';
                         html+='</div>';
                     }else{
                         html+='<div class="mui-pull-right tangle pai-tangle">';
                         html+='<span>排</span>';
                         html+='</div>';
                     }
                    html+='</div>';
                    html+='</div>';
                    html+='<div class="mui-table-view-cell product-type">';
                    html+='<p>产品类型：<span> '+arr.type_info.service_name+'</span></p>';
                    html+='</div>';
                    html+='<div class="mui-table-view-cell product-type">';
                    html+='<div class="customer-request mui-row">';
                    html+='<p class="mui-col-xs-4 mui-col-sm-4">客户要求：</p>';
                    html+='<p class="mui-col-xs-8 mui-col-sm-8">'+arr.content.details+'</p>';
                    html+='</div>';
                    html+='</div>';
                    html+='<div class="mui-table-view-cell buy-address">';
                    html+='<p> <span class="order-type buy">买</span> 购买地址:</p>';
                    html+='<p>'+arr.start_address+'</p>';
                    html+='</div>';
                    html+='<div class="mui-table-view-cell buy-address">';
                    html+='<p> <span class="order-type receive">收</span> 收获地址:</p>';
                    html+='<p>'+arr.end_address+'</p>';
                    html+='</div>';
                    html+='<div class="mui-table-view-cell buy-msg">';
                    html+='<p>购买时间：<span> ';
                    if(arr.content.service_time ==''||arr.content.service_time==undefined){
                        html+='立即';
                    }else{
                        html+=arr.content.service_time;
                    }
                    html+='</span></p>';
                    html+='</div>';
                    if(arr.type == 1 || arr.type == 2 ){
                        html+='<div class="mui-table-view-cell buy-msg">';
                        html+='<p>商品预付：<span> ';
                        if(arr.content.prepayment ==''||arr.content.prepayment==undefined){
                            html+='0';
                        }else{
                            html+=arr.content.prepayment;
                        }

                        html+'元</span></p>';
                        html+='</div>';
                        html+='<div class="mui-table-view-cell buy-msg">';
                        html+='<p>距离：<span> ';
                        if(arr.start_end_distance ==''){
                            html+='0';
                        }else{
                            html+=arr.start_end_distance;
                        }
                        html+='（公里）</span></p>';
                        html+='</div>';
                    }

                    html+='<div class="mui-table-view-cell buy-msg buy-msg-last">';
                    html+='<div class="order-cost">';
                    html+='<p>订单金额：<span>'+arr.money+'元</span></p>';
                    html+='<p>(含商品预付费用)</p>';
                    html+='</div>';
                    html+='</div>';
                    html+='<div class="msg-btm get-btn">';
                    html+='<button type="button" class="mui-btn cancel-order">取消</button>';
                    html+='<button type="button" class="mui-btn get-btn-order qing-btn">抢单</button>';
                    html+='</div>';
                    html+='</div>';
                    html+='</div>';

                    $('.order-qiang').html(html);
                    $('.order-qiang').show();
                    document.getElementsByClassName('cancel-order')[0].addEventListener('tap', function() {
                        document.getElementsByClassName('order-qiang')[0].style.display = 'none';
                    });
                    document.getElementsByClassName('qing-btn')[0].addEventListener('tap', function() {
                        layer.open({
                            type: 2,
                            shadeClose:false
                        });
                        mui.ajax('/vvptd/order/manual',{
                            type:'post',
                            data:{
                                order_id:arr.id
                            },
                            success:function(data){
                                layer.closeAll();
                                if(data.code == 1){
                                    orderSuccess(arr);
                                    document.getElementsByClassName('order-qiang')[0].style.display = 'none';
                                    mui.alert(data.msg);
                                }else{
                                    mui.alert(data.msg);
                                }
                            }
                        })

                    });
                }
            })
        });
        map.getPanes().labelPane.appendChild(div); //getPanes(),返回值:MapPane,返回地图覆盖物容器列表  labelPane呢???
        // 需要将div元素作为方法的返回值，当调用该覆盖物的show、
        // hide方法，或者对覆盖物进行移除时，API都将操作此元素。
        return div;
    }else {
        var div = this._div = document.createElement("div");
        div.style.position = "absolute";
        div.style.zIndex = BMap.Overlay.getZIndex(this._point.lat); //聚合功能?
        // 可以根据参数设置元素外观
        div.style.height = "40px";
        div.style.width = "44px";
        var img = this._img = document.createElement("img");
        img.src = baseUrl + "/assets/vvptd/member/img/waikuang.png";
        img.style.width = "40px";
        img.style.height = "44px";
        var arrow = this._arrow = document.createElement("img");
        arrow.src = this._url;
        arrow.style.position = 'absolute';
        arrow.style.width = "38px";
        arrow.style.height = "39px";
        arrow.style.top = "1px";
        arrow.style.left = "1px";
        arrow.style.borderRadius = '50%';
        div.appendChild(img);
        div.appendChild(arrow);
        //给标注添加点击事件
        // 		div.addEventListener('touchstart', function() {
        // 			alert('点击了图标');
        // 		});
        // 将div添加到覆盖物容器中
        map.getPanes().labelPane.appendChild(div); //getPanes(),返回值:MapPane,返回地图覆盖物容器列表  labelPane呢???
        // 需要将div元素作为方法的返回值，当调用该覆盖物的show、
        // hide方法，或者对覆盖物进行移除时，API都将操作此元素。
        return div;
    }
};

//3、绘制覆盖物
// 实现绘制方法
ComplexCustomOverlay.prototype.draw = function() {
    var map = this._map;
    var pixel = map.pointToOverlayPixel(this._point);
    this._div.style.left = pixel.x + "px";
    this._div.style.top  = pixel.y - 40 + "px";
};



//4、自定义覆盖物添加事件方法
ComplexCustomOverlay.prototype.addEventListener = function(event, fun) {
    this._div['on' + event] = fun;
}


