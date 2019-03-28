mui.init();
mui.plusReady(function() {
    // 接收父级页面传的参数
    var self = plus.webview.currentWebview();
    // 当前定位的城市
    var locaCity = document.getElementById("loca-caty")


    if(self.locaCity){
        locaCity.innerText = self.locaCity
    }
    // 重新获取定位
    plus.geolocation.getCurrentPosition(translatePoint,function(e){
        mui.toast("异常:" + e.message);
    });

    function translatePoint(position){
        var currentLon = position.coords.longitude;
        var currentLat = position.coords.latitude;
        locaCity.innerText = position.address.city
        // 点击当前的定位城市 返回首页
        locaCity.addEventListener('tap',function(){
            var view = plus.webview.currentWebview().opener();
            mui.fire(view,'doit',{
                currentLon:currentLon,
                currentLat:currentLat,
                locaCity:locaCity.innerText
            });
            mui.back()
        })

    }

    // 点击热门城市
    var hotCity = document.getElementById("hot-city")
    hotCity.addEventListener("tap", function(e) {
        var tagClass = e.target.getAttribute("class");
        // console.log("tagClass==" + tagClass);
        if (tagClass && tagClass.indexOf("hot-city") != -1) {
            var selectCity = e.target.innerText;
            // alert("选择的城市=" + selectCity);
            var view = plus.webview.currentWebview().opener();
            mui.fire(view,'doit',{
                imagePath:selectCity
            });
            mui.back()
        }
    });

    var header = document.querySelector('header.mui-bar');
    var list = document.getElementById('list');
    list.style.height = (document.body.offsetHeight - header.offsetHeight) + 'px';
    window.groupList = new mui.GroupList(list);
    //点击列表选择城市
    var ul_city = document.getElementById('ul_city');
    ul_city.addEventListener("tap", function(e) {
        var tagClass = e.target.getAttribute("class");
        // console.log("tagClass==" + tagClass);
        if (tagClass && tagClass.indexOf("mui-table-view-cell") != -1) {
            var selectCity = e.target.innerText;
            // alert("选择的城市=" + selectCity);
            var view = plus.webview.currentWebview().opener();
            mui.fire(view,'doit',{
                imagePath:selectCity
            });
            mui.back()
        }
    });
});