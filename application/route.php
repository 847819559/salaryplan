<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    /*'__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],*/
    //首页 带分页参数
    /*'[index]' => [
        ':page' => ['index/index/index',['method' => 'get'],['page' => '\d+']]
    ],*/

    'index'=>'index/index/index', //首页
    'uPlan'=>'index/index/uPlan', //U计划
    
    /* +---------------------------   薪计划   -----------------------------+ */
    'salaryplan'=>'index/salaryplan/salaryplan',            //新计划
    'joinsalaryplan'=>'index/salaryplan/JoinSalaryplan',    //加入新计划
    'tadaylist'=>'index/salaryplan/tadayList',              //薪计划 加入记录
    'agreement'=>'index/salaryplan/agreement',              //薪计划 服务协议

    /* ======= 订单 ======= */
    'addSalaryplanOrder'=>'index/orders/addSalaryplanOrder', //薪计划 订单入库
    'OrderPayment'=>'index/orders/OrderPayment', //薪计划 支付订单
    'alipay'=>'index/orders/alipay', //薪计划 确定支付
    'alipay_notify'=>'index/orders/alipay_notify', //支付异步请求


    /* +---------------------------   用户登录 注册   -----------------------------+ */
    'login_index'=>'index/Login/index',
    'login_tel'=>'index/Login/tel',//给手机发送验证码
    'login_add'=>'index/Login/add',//注册成功
    'login_select'=>'index/Login/sel',//判断手机号唯一
    'login'=>'index/Login/login',//登录
    'login_out'=>'index/Login/login_out',//退出
    'findPwd'=>'index/Login/findPwd',//找回密码


    /* +-----------------------------    收益计算    -------------------------------+ */
    'calculation'=>'index/IncomeCalculation/calculation',//收益计算
    'XJH-calculation'=>'index/IncomeCalculation/XJH_calculation',//收益计算
    

    'getUser'=>'index/index/getUser',     //测试数据接口
];
