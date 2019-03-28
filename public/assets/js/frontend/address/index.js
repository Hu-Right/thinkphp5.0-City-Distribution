mui.init();

(function() {
    // 如果三个地址有一个有值,可管理
    var home_ads = document.getElementById('home_ads').innerHTML,
        company_ads = document.getElementById('company_ads').innerHTML,
        used_ads = document.getElementsByClassName('used_ads');
    //console.log(home_ads);
    //console.log(company_ads);
    //console.log(used_ads);


    if (home_ads != '设置地址' || company_ads != '设置地址' || used_ads.length != 0) {
        // 显示管理按钮
        var head_btn = document.getElementById('head-btn-l');
        head_btn.style.display = 'block';

        // 点击管理事件
        var flag = true;
        head_btn.addEventListener('tap', function() {
            // 管理
            if (flag) {
                document.getElementById('del-box').style.display = 'block';
                this.innerHTML = '完成';
                this.classList.add('finish-btn');
                // 有家地址
                if (home_ads != '') {
                    document.getElementById('home_icon').className = 'iconfont icon-quan1 icons';
                }
                // 有公司地址
                if (company_ads != '') {
                    document.getElementById('company_icon').className = 'iconfont icon-quan1 icons';
                }
                // 有常用地址
                if (used_ads.length != 0) {
                    var _lis = document.getElementById('used_list').querySelectorAll('.iconfont');
                    // console.log(_lis)
                    for (var i in _lis) {
                        _lis[i].className = 'icon iconfont icon-quan1 icons';
                    }
                }
                flag = false;


                // 勾选地址删除
                var ads_all = document.getElementById('ads-all');
                mui("#ads-all").on('tap','.icons',function(){
                    //console.log(this.classList)
                    if(this.classList.value.includes('icon-quan1')){
                        this.classList.remove('icon-quan1');
                        this.classList.add('icon-dui');
                        //console.log(this.classList)
                    }else{
                        this.classList.remove('icon-dui');
                        this.classList.add('icon-quan1');
                    }

                    var gous2 = ads_all.querySelectorAll('.icon-quan1').length;
                    //console.log(gous2)
                    if(gous2 == 0){
                        document.getElementById('all').getElementsByClassName('iconfont')[0].className = 'iconfont icon-dui icons';
                    }else{
                        document.getElementById('all').getElementsByClassName('iconfont')[0].className = 'iconfont icon-quan1 icons'
                    }
                });

                // 全选
                var flag3 = true;
                var _quans = ads_all.querySelectorAll('.icon-quan1');
                document.getElementById('all').addEventListener('tap',function(){
                    if(flag3){
                        this.children[0].className = 'iconfont icon-dui icons';
                        for(var i=0; i<_quans.length; i++){
                            _quans[i].className = 'iconfont icon-dui icons';
                        }
                        flag3 = false
                    }else{
                        this.children[0].className = 'iconfont icon-quan1 icons';
                        for(var i=0; i<_quans.length; i++){
                            _quans[i].className = 'iconfont icon-quan1 icons';
                            flag3 = true;
                        }
                    }
                })

                // 点击删除按钮
                var del_arr = [];
                document.getElementById('del-btn').addEventListener('tap',function(){
                    del_arr = [];
                    var _gous = ads_all.querySelectorAll('.icon-dui');
                    for(var i=0; i<_gous.length; i++){
                        //console.log(_gous[i].getAttribute('data-id'))
                        del_arr.push(_gous[i].getAttribute('data-id'))
                    }
                    // console.log(del_arr)
                    del(del_arr)

                });


            } else {
                // 完成
                document.getElementById('del-box').style.display = 'none';
                this.innerHTML = '管理';
                this.classList.remove('finish-btn');
                // 有家地址
                if (home_ads != '') {
                    document.getElementById('home_icon').className = 'iconfont icon-jia1 icons';
                }
                // 有公司地址
                if (company_ads != '') {
                    document.getElementById('company_icon').className = 'iconfont icon-gongsi icons';
                }
                // 有常用地址
                if (used_ads.length != 0) {
                    var _lis = document.getElementById('used_list').querySelectorAll('.iconfont');
                    //console.log(_lis)
                    for (var i in _lis) {
                        _lis[i].className = 'icon iconfont icon-shiliangzhinengduixiang icons';
                    }
                }
                flag = true;
            }


        });




    } else {
        //console.log(2)
    }

})(mui);

//删除
function del(ids){
    mui.ajax('/index/address/deleteaddress',{
        type: 'post',
        data:{ids:ids},
        dataType:'json',//服务器返回json格式数据
        type:'post',//HTTP请求类型
        success: function (data) {
            //console.log(data);
            setTimeout(function(){
                window.location.reload();//刷新当前页面.
            },1000);
        },
        error: function (data) {
            setTimeout(function(){
                window.location.reload();//刷新当前页面.
            },1000);
        }
    });
}

//新增/修改家庭地址
document.getElementById('home').addEventListener('tap', function() {
    mui.openWindow({
        url:top.location.origin+'/index/address/newaddress?type=1',
        id:'select-ads-index.html',
        createNew:true//是否重复创建同样id的webview，默认为false:不重复创建，直接显示
    })
});

//新增/修改公司地址
document.getElementById('company').addEventListener('tap', function() {
    mui.openWindow({
        url:top.location.origin+'/index/address/newaddress?type=2',
        id:'select-ads-index.html',
        createNew:true//是否重复创建同样id的webview，默认为false:不重复创建，直接显示
    })
});

//新增常用地址
document.getElementById('newaddress').addEventListener('tap', function() {
    mui.openWindow({
        url:top.location.origin+'/index/address/newaddress?type=3',
        id:'select-ads-index.html',
        createNew:true//是否重复创建同样id的webview，默认为false:不重复创建，直接显示
    })
});
//修改常用地址
mui('#used_list').on('tap','.common-used',function(){
    var id = this.getAttribute('data-id');
    //console.log(id);
    mui.openWindow({
        url:top.location.origin+'/index/address/newaddress?type=3&id='+id,
        id:'select-ads-index.html',
        createNew:true//是否重复创建同样id的webview，默认为false:不重复创建，直接显示
    })
})


//返回主页
document.getElementById('back').addEventListener('tap', function() {
    //alert(top.location.origin+'/index/user/index');
    mui.openWindow({
        url:top.location.origin+'/index/user/index',
        createNew:true
    })
});