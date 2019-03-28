mui.init();
mui.plusReady(function() {
    plus.storage.clear();//清除所有数据

    // 选择标签事件
    var _input_tag = document.getElementById('input_tag');
    var tags = document.getElementById('tags');
    var tags_span = tags.getElementsByTagName('span');
    for (var i = 0; i < tags_span.length; i++) {
        tags_span[i].addEventListener('tap', function() {
            var _input_val = _input_tag.value;
            // console.log(this.innerHTML)
            var _val = this.innerHTML.replace('+', ' ');
            _input_tag.value = _input_val + _val + ' '; // 此处空格不可删
        })
    }


// 购买地址切换
    var ads_buy = document.getElementById('select_ads');
    var _lis = ads_buy.getElementsByClassName('li');
    var select_ads_btm = document.getElementById('select_ads_btm');
    var _divs = select_ads_btm.getElementsByClassName('div');
    doSelectAds(0);
    doSelectAds(1);
    function doSelectAds(i) {
        _lis[i].addEventListener('tap', function(e) {
            if (i == 0) {
                _lis[0].classList.add('li-on');
                _lis[1].classList.remove('li-on');
                _divs[0].classList.add('on');
                _divs[1].classList.remove('on');
            } else {
                _lis[0].classList.remove('li-on');
                _lis[1].classList.add('li-on');
                _divs[0].classList.remove('on');
                _divs[1].classList.add('on');
            }
        });
    }




    // 点击选择地址

    document.getElementById('select-ads').addEventListener('tap',function(){

        var cp_index = document.getElementById('scroll').getElementsByClassName('mui-active')[0].innerText;// 产品类型
        // console.log(cp_index)	// 产品类型
        var puy_con = document.getElementById('input_tag').value;	// 帮我购买内容
        // console.log(puy_con);
        if(puy_con != ''){
            plus.storage.setItem('content', cp_index+puy_con);
            plus.storage.setItem('status', '1');
            mui.openWindow({
                url: top.location.origin+'/index/address/selectaddress',
                id: 'select-ads-index',
                createNew:true
            });
        }else{
            mui.alert('请先填写购买的商品名称和数量', '帮我买');
        }

    });


// 下一步
    var pay_ads_index = 0;	// 购买地址方式index	 0:指定购买地址
    document.getElementById('next-btn').addEventListener('tap',function(){
        var cp_index = document.getElementById('scroll').getElementsByClassName('mui-active')[0].innerText;// 产品类型
        // console.log(cp_index)	// 产品类型

        // 获取帮我购买内容
        var puy_con = document.getElementById('input_tag').value;	// 帮我购买内容
        // console.log(puy_con);

        // 判断是指定地址还是就近购买
        var _lis = document.getElementById('select_ads').getElementsByClassName('li');
        for(var i=0; i<_lis.length; i++){
            if(_lis[i].classList.value == 'li li-on' || _lis[i].classList == 'li li-on'){
                pay_ads_index = i
            }
            //console.log(_lis[i].classList.value)
        }
        // console.log(pay_ads_index)	// 购买地址方式index
        if(pay_ads_index == 0){
            if(puy_con == ''){
                mui.alert('请先填写购买的商品名称和数量', '帮我买');
            }else{
                mui.alert('请选择购买地址', '帮我买');
            }
        } else{
            if(puy_con == ''){
                mui.alert('请先填写购买的商品名称和数量', '帮我买');
            }else{
                plus.storage.setItem('content', cp_index+puy_con);
                plus.storage.setItem('status', '2');
                mui.openWindow({
                    url: top.location.origin+'/index/placeorder/helpbuystep2',
                    id: 'helpBuy-index-next',
                    createNew:true
                });
            }
        }
    });
});
