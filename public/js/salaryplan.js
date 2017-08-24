

$(function(){
    //判断 是否 有名额
    isPlaces()
    var product_id = $('.product_id').val();
     $.ajax({
        url:'tadaylist',
        data:{product_id:product_id},
        type:'POST',
        dataType:'json',
        success:function(data){
            $('#autoinvest-product-join-record').empty();
            var obj = '';
            $.each(data,function(k,v){
                
                obj += '<div class="data-list fn-clear odd" data-reactid=".0.$0">';
                obj += '<div class="d-index" data-reactid=".0.$0.0">'+v.userId+'</div>';
                obj += '<div class="d-name" data-reactid=".0.$0.1">'+v.nickName+'</div>';
                obj += '<div class="d-invest-money" data-reactid=".0.$0.2">'+v.amount+'</div>';
                obj += '<div class="d-source" data-reactid=".0.$0.3"><i class="icon-we-shoujiicon" data-reactid=".0.$0.3.0"></i></div>';
                obj += '<div class="d-join-time" data-reactid=".0.$0.4">'+v.createTime+'</div>';
                obj += '</div>';      
            })
        $('#autoinvest-product-join-record').html(obj);
        }
    })
})


//输入 金额 点击事件
$('#salaryplan-product-buy-amount').on('keyup',function(){

    //验证 用户输入金额是否合法
    verifyPrice();
})

//点击 提交 点击事件
$('#salaryplan-product-input').on('click',function(){

    var price = $('#salaryplan-product-buy-amount').val().replace(/\s/g, "");
    if(price == ''){
        $('#autoinvest-product-input-tip').html('您最少购买500元');
        $('#salaryplan-product-input').css({"pointer-events":"none",'background':'#dbdbdb','color':'#fff'});
        return;
    }
    //验证 用户输入金额是否合法
    var this_page = 'salaryplan'; //本页路径
    var price = $('#salaryplan-product-buy-amount').val().replace(/\s/g, "");
    var product_id = $('.product_id').val();
    $.ajax({
        url:'joinsalaryplan',
        data:{this_page:this_page,price:price,product_id:product_id},
        type:'POST',
        dataType:'json',
        success:function(data){
            //未登录，跳转登陆页面
            if(data.code == 100){
                location.href = data.codeUrl+'?tokenUrl='+this_page;
            } else {
                $('#box').html(data);
            }
        }
    })

})
   

/**
 * 更改计划介绍数据 
 */
$(document).on("click",'.tab-bar div',function () {
    var _index = $(this).index();
    var _ul = $(".tab-bar");
    var _detail = $(".content");
    // 更改样式
    $(this).addClass('active');
    $(this).siblings().removeClass('active');
    // 更改显示数据
    _detail.children().eq(_index).show();
    _detail.children().eq(_index).siblings().hide();
})

//  X  关闭遮罩层
$(document).on('click','.dialog-close-btn',function(){
    $('#box').empty();
})

//获取数据，添加数据库
$(document).on('click','.J-autoinvest-submit',function(){
    verifyPrice();
    var product_id = $('.product_id').val();
    var order_amount = $('#salaryplan-product-buy-amount').val();

    $.ajax({
        url:'addSalaryplanOrder',
        data:{product_id:product_id,order_amount:order_amount},
        type:'POST',
        dataType:'json',
        success:function(data){
            if(data.code == 1){
                location.href='OrderPayment?order_id='+data.codeInfo;    
            } else {
                $('#box').empty();
                alert(data.codeInfo);
            }
        }
    })
})



//验证 用户输入金额是否合法
function verifyPrice(){

    //判断 是否 有名额
    isPlaces()

    //获取 用户输入金额 并出去所有空格
    var price = $('#salaryplan-product-buy-amount').val().replace(/\s/g, "") == '' ? 0 : $('#salaryplan-product-buy-amount').val().replace(/\s/g, "");
    var min   = $('#salaryplan-product-buy-amount').attr('data-min').replace(/\s/g, "");
    var max   = $('#salaryplan-product-buy-amount').attr('data-max').replace(/\s/g, "");

    if(parseInt(price) < parseInt(min)){
        $('#autoinvest-product-input-tip').html('您最少购买500元');
        $('#salaryplan-product-input').css({"pointer-events":"none",'background':'#dbdbdb','color':'#fff'});
        return;
    } else {
        $('#autoinvest-product-input-tip').html('');
        $('#salaryplan-product-input').css({"pointer-events":"auto",'background':'#FF721F','color':'#fff'});
    }

    if(parseInt(price) > parseInt(max)){
        $('#autoinvest-product-input-tip').html('您最多只能购买20000元');
        $('#salaryplan-product-input').css({"pointer-events":"none",'background':'#dbdbdb','color':'#fff'});
        return;
    } else {
        $('#autoinvest-product-input-tip').html('');
        $('#salaryplan-product-input').css({"pointer-events":"auto",'background':'#FF721F','color':'#fff'});
    }

    if(price%100 != 0){
        $('#autoinvest-product-input-tip').html('递增金额需为100元的整数倍');
        $('#salaryplan-product-input').css({"pointer-events":"none",'background':'#dbdbdb','color':'#fff'});
        return;
    } else {
        $('#autoinvest-product-input-tip').html('');
        $('#salaryplan-product-input').css({"pointer-events":"auto",'background':'#FF721F','color':'#fff'});
    }

}

//判断 是否 有名额
function isPlaces(){
    var current = $('.current').html();
    if(current == 0){
        $('#autoinvest-product-input-tip').html('投资名额已满，谢谢参与。');
        $('#salaryplan-product-input').css({"pointer-events":"none",'background':'#dbdbdb','color':'#fff'});
        return;    
    }
     
}

//判断 输入金额书否是 100 的倍数
/*function isInteger(price) {
    return Math.floor(price) === price;
}*/


