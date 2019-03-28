
// 评星事件
var stars = document.getElementById('stars');
var stars_img = stars.getElementsByTagName('img');
for(var i = 0; i<stars_img.length; i++){
    stars_img[i].index = i;
    stars_img[i].onclick = function(){
        var _index = this.index;
        // console.log(_index)
        switch(_index)
        {
            case 0:
                stars_img[0].src = '/assets/img/star1.png';
                stars_img[1].src = '/assets/img/star2.png';
                stars_img[2].src = '/assets/img/star2.png';
                stars_img[3].src = '/assets/img/star2.png';
                stars_img[4].src = '/assets/img/star2.png';
                document.getElementById('score').value = 1;
                break;
            case 1:
                stars_img[0].src = '/assets/img/star1.png';
                stars_img[1].src = '/assets/img/star1.png';
                stars_img[2].src = '/assets/img/star2.png';
                stars_img[3].src = '/assets/img/star2.png';
                stars_img[4].src = '/assets/img/star2.png';
                document.getElementById('score').value = 2;
                break;
            case 2:
                stars_img[0].src = '/assets/img/star1.png';
                stars_img[1].src = '/assets/img/star1.png';
                stars_img[2].src = '/assets/img/star1.png';
                stars_img[3].src = '/assets/img/star2.png';
                stars_img[4].src = '/assets/img/star2.png';
                document.getElementById('score').value = 3;
                break;
            case 3:
                stars_img[0].src = '/assets/img/star1.png';
                stars_img[1].src = '/assets/img/star1.png';
                stars_img[2].src = '/assets/img/star1.png';
                stars_img[3].src = '/assets/img/star1.png';
                stars_img[4].src = '/assets/img/star2.png';
                document.getElementById('score').value = 4;
                break;
            case 4:
                stars_img[0].src = '/assets/img/star1.png';
                stars_img[1].src = '/assets/img/star1.png';
                stars_img[2].src = '/assets/img/star1.png';
                stars_img[3].src = '/assets/img/star1.png';
                stars_img[4].src = '/assets/img/star1.png';
                document.getElementById('score').value = 5;
                break;
            default:
                console.log('错误')
        }
    }
}
// 评星事件 end


// input聚焦事件
var _input = document.getElementById('input');
_input.focus();
// input聚焦事件 end

// 选择标签事件
var tags = document.getElementById('tags');
var tags_span = tags.getElementsByTagName('span');
for(var i = 0; i<tags_span.length; i++){
    tags_span[i].index = i;
    tags_span[i].onclick = function(){
        var _input_val = _input.value;
        // console.log(_input_val)
        var _val = this.innerHTML;
        _input.value = _input_val + _val+' '; // 此处空格不可删
    }
}

//提交
document.getElementById('submit').addEventListener('tap',function(){
    var id = document.getElementById('id').value;
    var score = document.getElementById('score').value;
    var content = document.getElementById('input').value;
    //console.log(id);
    mui.ajax("/index/order/evaluate",{
        type: 'post',
        data:{id:id,score:score,content:content},
        dataType:'json',//服务器返回json格式数据
        type:'post',//HTTP请求类型
        success: function (data) {
            //console.log(data);
            layer.msg(data.msg);
            if(data.code == 1){
                setTimeout(function(){
                    window.location.href = data.url;//跳转
                },1000);
            }else{
                setTimeout(function(){
                    window.location.reload();//刷新当前页面.
                },1000);
            }
        },
        error: function (xhr,type,errorThrown) {
            // console.log(xhr.status);
            setTimeout(function(){
                window.location.reload();//刷新当前页面.
            },1000);
        }
    });

})