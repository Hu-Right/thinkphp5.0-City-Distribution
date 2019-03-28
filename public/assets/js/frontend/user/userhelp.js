mui.init()

var li = document.getElementsByTagName('li')

//console.log(li);
//法律条款和隐私
li[0].addEventListener('tap',function(){
    mui.openWindow({url:top.location.origin+'/index/user/loginindex/user/helpShow?title=法律条款和隐私&id=3',id:'helpShow',createNew:true})
})
//常见问题
li[1].addEventListener('tap',function(){
    mui.openWindow({url:top.location.origin+'/index/user/helpShow?title=常见问题&id=4',id:'helpShow',createNew:true})
})
//意见反馈
li[2].addEventListener('tap',function(){
    mui.openWindow({url:top.location.origin+'/index/user/advise',id:'advise',createNew:true})
})
//人工服务
li[3].addEventListener('tap',function(){
    mui.openWindow({url:top.location.origin+'/index/user/helpShow?title=人工服务&id=6',id:'helpShow',createNew:true})
})
//关于我们
li[4].addEventListener('tap',function(){
    mui.openWindow({url:top.location.origin+'/index/user/helpShow?title=关于我们&id=7',id:'helpShow',createNew:true})
})
