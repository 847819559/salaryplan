<?php
namespace app\index\controller;

use think\Db;
use think\Request;
use think\Controller;
use think\Session;
use app\index\controller\Common;
use app\index\model\user\Userverify;
use app\index\model\user\Userinfo;
use app\index\model\product\VerifyLegal;
use app\index\model\product\CreateNumber;
use app\index\model\product\Productinfo;
use app\index\model\product\Order;

class Salaryplan extends Common
{

	//公共导航
    protected $nav = '';

    public function _initialize(){
        $this->nav = $this->getNav();
    }

	//薪计划
    public function salaryplan(){

        //接值
        $request = Request::instance();

        //支付成功 同步返回参数，
        //操作 1、添加支付日志 2、修改订单-支付状态 3、修改账户余额 4、修改产品-购买数量
        if(Request::instance()->has('trade_status','get')){

            $data['out_trade_no']      = $request->get('out_trade_no');        //商户订单号
            $data['total_fee']         = $request->get('total_fee');           //支付金额
            $data['is_success']        = $request->get('is_success');          //是否成功
            $data['notify_time']       = $request->get('notify_time');         //支付时间
            $data['subject']           = $request->get('subject');             //支付标题
            $data['payment_type']      = 2;                                    //支付标识
            $data['user_id']           = Session::get('user_id');              //用户id

            //添加 日志
            $pay_log = Db::table('pay_log')->insert($data);
            
            //修改 订单支付状态
            $order = Order::where(['order_sn'=>$data['out_trade_no']])->find();            
            $order->order_status = 2;
            $order_res = $order->save();

            //修改用户 账户金额
            $userinfo = Userinfo::get($data['user_id']);
            $userinfo->money = $userinfo['money'] - $data['total_fee'];
            $userinfo_res = $userinfo->save();

            //修改产品 购买数量
            $product = Productinfo::where(['id'=>$order['product_id']])->find();
            $product->invest_amount = $product['invest_amount'] + 1;
            $product->save();

            //如果 日志 订单 账户余额 操作完成 
            if($order_res && $userinfo_res){
                $this->success('交易成功',request()->domain().'/salaryplan');
            }

        }

        /*  每日 判断 10:30 分开始投资时，该产品状态为1  */
        $date = date('Y-m-d');
        $time = strtotime($date." 10:30:00");
        if(time() > $time){
            Db::table('productinfo')->where(['product_status'=>0,'product_type_id'=>4])->update(['product_status'=>1]);
        }

        /*导航*/
    	$nav_list = $this->nav; 

        /* 薪计划历史记录 */
        //获取  最新10条 薪计划信息
        $salaryplan_list = Productinfo::where(['product_type_id'=>4])
                                ->field('id,product_name,rate,deadline,product_amount,product_status,invest_amount')
                                ->order('id','desc')
                                ->limit(10)
                                ->select();
        $ids = array();
        foreach ($salaryplan_list as $key => $value) {
            $ids[] = $value['id'];
        }
        //根据 最新10条 薪计划 查询订单
        $order = Order::where('product_id','in',$ids)
                     ->field('product_id,COUNT(product_id) as count,SUM(order_amount) as num')
                     ->where('order_status','eq','2')
                     ->group('product_id')
                     ->order('id','desc')
                     ->select();
        //合并数组 得到 薪计划历史记录
        foreach ($salaryplan_list as $key => $value) {
            $salaryplan_list[$key]['rate'] = sprintf("%.1f",$value['rate']);
            foreach ($order as $k => $val) {
                if($value['id'] == $val['product_id']){
                    $salaryplan_list[$key]['count'] = $val['count'];
                    $salaryplan_list[$key]['num'] = number_format($val['num']);
                }
            }
        }
       
        /* 获取 当天（可加入）薪计划信息 */
        if($request->get('product_id')){
            $where = ['id'=>$request->get('product_id'),'product_type_id'=>4];
            $taday_salaryplan_list = Db::name('productinfo')->where($where)->find();
        } else {
            $taday_salaryplan_list = Db::name('productinfo')
                                    ->where('product_status=0 OR product_status=1')
                                    ->where('product_type_id=4')
                                    ->order('id','desc')
                                    ->find();
        }
        $surplus_num = 200 - $taday_salaryplan_list['invest_amount'];                    //剩余名额
        $taday_salaryplan_list['rate'] = sprintf("%.1f",$taday_salaryplan_list['rate']); //年化率保留一位小数
        $taday_salaryplan_list['day']  = date('d',$taday_salaryplan_list['invest_time']);//每月投资金额
        $time = date('Y-m-d',$taday_salaryplan_list['invest_time']+24*3600*365);
        $taday_salaryplan_list['outDay'] = date('Y年m月d日',strtotime($time));  // 计算退出日期

        /* 获取 当天薪计划 的加入记录 */  
        $taday_order_list = Db::table('order')
                            ->field('COUNT(id) as count,avg(order_amount) as average')
                            ->where('product_id','eq',$taday_salaryplan_list['id'])
                            ->where('order_status','eq','2')
                            ->find();
        $taday_order_list['average'] = ceil($taday_order_list['average']);//人均投资金额 取整

        /* 设置 开始投资 倒计时 */
        $getTime = $this->countDown();
       
        //模板数据
    	$data = [
            'nav'                   => $nav_list,
            'salaryplan_list'       => $salaryplan_list,
            'taday_salaryplan_list' => $taday_salaryplan_list,
            'taday_order_list'      => $taday_order_list,
            'surplus_num'           => $surplus_num,
            'getTime'               => $getTime,
        ];
    	return view('salaryplan',$data);
    }


    /**
     * 获取用户 当天 投资记录
     */
    public function tadayList(){

        $product_id = Input('product_id');
        //获取 当天薪计划 的加入记录
        $taday_order_list = Db::table('order')
                              ->join('userinfo','order.userid=userinfo.id','LEFT')
                              ->field('userinfo.id,username,order_amount,addtime')
                              ->where('product_id','eq',$product_id)
                              ->where('order_status','eq','2')
                              ->select();

        $data = array();
        foreach ($taday_order_list as $key => $value) {
            $data[$key]['userId'] = $key+1;      //序号
            $data[$key]['nickName'] = $this->substr_cut($value['username']);     //投资人
            $data[$key]['amount'] = number_format($value['order_amount']);       //月投资金额
            $data[$key]['createTime'] = $this->getTime($value['addtime']);   //加入时间
            $data[$key]['tradeMethod'] = 'PC';  //购买 平台
            $data[$key]['finalAmount'] = number_format($value['order_amount']);  //最终 投资金额
            $data[$key]['ucodeId'] = ''; 
        }

        echo json_encode($data);exit;
    }


    //将用户名进行处理，中间用星号表示  
    public function substr_cut($user_name){  
        //获取字符串长度  
        $strlen = mb_strlen($user_name, 'utf-8');  
        //如果字符创长度小于2，不做任何处理  
        if($strlen<2){  
            return $user_name;  
        }else{  
            //mb_substr — 获取字符串的部分  
            $firstStr = mb_substr($user_name, 0, 1, 'utf-8');  
            $lastStr = mb_substr($user_name, -1, 1, 'utf-8');  
            //str_repeat — 重复一个字符串  
            return $firstStr .'***'. $lastStr;  
        }  
    }  


    //时间 格式化
    public function getTime($time){

        return date('Y年m月d日 H:i',$time);
    }


    //薪计划 每期开始倒计时
    public function countDown(){

        //设置活动时间
        $date = date('Y-m-d');
        $starttimestr = $date." 03:00:00";
        $endtimestr   = $date." 10:30:00";

        //转化为时间戳
        $starttime = strtotime($starttimestr);
        $endtime   = strtotime($endtimestr); 

        // 将时间转化为unix时间戳         
        $now = strtotime(date('Y-m-d H:i:s'));
        // $remain = $endtime - $now;

        $remain_time   = $endtime - $now;; //剩余的秒数
        $remain_hour   = floor($remain_time/(60*60)); //剩余的小时
        $remain_minute = floor(($remain_time - $remain_hour*60*60)/60); //剩余的分钟数
        $remain_second = ($remain_time - $remain_hour*60*60 - $remain_minute*60); //剩余的秒数
        
        if($remain_hour < 0){
            $res['code'] = 0;
            $res['codeInfo'] = '开始';
        } else {
            $res['code'] = 1;
            $res['codeInfo'] = $remain_hour.'时'.$remain_minute.'分'.$remain_second.'秒';
        }
       
        return $res;

    }


    /**
     * 加入薪计划
     */
    public function JoinSalaryplan(){
        
        $request  = Request::instance();
        //获取 post 值
        $data = $request->post();
        //获取当前域名
        $domain = $request->domain();

        //判断 用户是否登录
        $Userverify = new Userverify;       
        $userStatus = $Userverify->userStatus();
        if(!$userStatus){
            $result['code'] = 100;
            $result['codeInfo'] = '用户未登录';
            $result['codeUrl'] = $domain.'/login_index';
            echo json_encode($result);exit;
        }

        //判断 数据是否合法
        $VerifyLegal = new VerifyLegal;
        $verify_res = $VerifyLegal->verifySalaryplanPrice($data['price']);
        if(!$verify_res){
            $result['code'] = 200;
            $result['codeInfo'] = '用户输入金额不合法';
            echo json_encode($result);exit;
        }

        //获取 投资 本期薪计划的详情
        $taday_salaryplan_list = Productinfo::where('id','eq',$data['product_id'])->find();
        $taday_salaryplan_list['amount'] = ($data['price'] * $taday_salaryplan_list['deadline']);  //总金额

        $res = $this->getHtml($taday_salaryplan_list,$data);
        echo json_encode($res);

    }
    
    //确认投资 确认信息遮罩层
    public function getHtml($taday_salaryplan_list,$data)
    {   
        
        return $res = '<div class="autoinvest-buy-main" style="display: block;">
                <div class="autoinvest-shadow"></div>
                <div class="autoinvest-buy-form" style="top: 0px;">
            <div class="form-header">
                <span class="form-title">支付</span>
                <span class="dialog-close-btn J-autoinvest-close">×</span>
            </div>
            <div class="form-content">
                <div class="pay-form">
            <div class="name"><span class="l-title">计划名称</span> <span class="text-value">'.$taday_salaryplan_list['product_name'].'</span></div>
            <div class="rate"><span class="l-title">预期年化利率</span> <span class="text-value">'.sprintf("%.2f",$taday_salaryplan_list['rate']).'%</span></div>
            <div class="method"><span class="l-title">收益处理</span> <span class="text-value">收益再投资</span></div>
            <div class="limit"><span class="l-title">理财期限</span> <span class="text-value invest-limit">'.$taday_salaryplan_list['deadline'].'个月</span></div>
            <div class="amount"><span class="l-title">月投资金额</span> <span class="text-value invest-money" data-invest-money="5500">'.$data['price'].'元</span></div>
            <div class="amount"><span class="l-title">总投资金额</span> <span class="text-value total-invest-money">'.$taday_salaryplan_list['amount'].'元</span></div>
            <div class="coupon fn-clear" id="coupon">
            <div class="coupon-component" data-reactid=".3">
            <div class="coupon-left" data-reactid=".3.0"><span class="l-title">优惠劵</span></div>
            <div class="coupon-right" data-reactid=".3.1">
            <div class="coupon-one-line" data-reactid=".3.1.0"><span data-reactid=".3.1.0.0"></span><span data-reactid=".3.1.0.1"></span></div>
            阿斯达所大所多
            <div class="j_gray_packet_tips " data-reactid=".3.1.1">本次投资可抵扣元</div> 
            
            <span data-reactid=".3.1.2"></span><div class="coupon-error" data-reactid=".3.1.3"></div></div></div></div>

            <div class="invest-d"><span class="l-title">每月投资日</span> <span class="text-value invest-date">每月'.substr(date('Ymd',$taday_salaryplan_list['invest_time']),-2).'号</span></div>
            <div class="real-money"><span class="l-title">应付金额</span> <span class="text-value actual-price" data-actual-money="5500">'.$data['price'].'元</span></div>
            <div class="agreement"><i class="icon-we-gouxuanicon"></i>我已阅读并同意签署<a href="/autoinvestplan/autoInvestPlanContract.action?autoInvestPlanId=20569" target="_blank">《薪计划170815期服务协议书》</a>及<a href="https://www.renrendai.com/pc/agreement/contract/currency/cmsId/58ec7c0d090cc9096532d0ca" target="_blank">《风险提示》</a></div>
        </div>
        </div>
            <div class="form-footer">
                <div class="submit J-autoinvest-submit">确定</div><div class="add-tip">
                        <div class="title">温馨提示</div>
                        <div class="msg">1、月投资日、月投资金额由加入时确定, 后续月份不支持修改。</div>
                        <div class="msg">2、为避免延期, 请每月提前充值至账户, 系统达到每月投资日自动划扣。</div>
                        <div class="msg">3、本计划不支持提前退出。</div>
                        <div class="msg">4、预期年化利率不代表实际收益。</div>
                    </div>
            </div>
        </div></div>';
    }

    

            
    //薪计划 服务协议
    public function agreement(){
        $request = Request::instance();
        $product_id = Input('product_id');

        if(Session::has('user_id')){
            $user_id = Session::get('user_id');
        } else {
            //获取当前域名
            $domain = $request->domain();
            return redirect($domain.'/login_index');exit;
        }

        $productinfo = Db::table('productinfo')->field('product_name')->find($product_id);
        $userinfo    = Db::table('userinfo')->field('telephone')->find($user_id);
        return view('agreement',['product_name'=>$productinfo['product_name'],'telephone'=>$userinfo['telephone']]);
    }


}
