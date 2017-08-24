<?php
/**
 *----订单------
 */
namespace app\index\controller;
use think\Controller;
use \think\Session;
use \think\Request;
use \think\Db;
use app\index\model\product\CreateNumber;
use app\index\model\product\Productinfo;
use app\index\model\product\Order;
use app\index\controller\Common;
use app\index\model\Pay;//调用模型
error_reporting(0);
class Orders extends Common
{
	

    //公共导航
    protected $nav = '';

    public function _initialize(){
        $this->nav = $this->getNav();
    }


	//加入订单
    public function addSalaryplanOrder(){

        $CreateNumber = new CreateNumber();
        //接值
        $request = Request::instance();
        $product_id    = $request->post('product_id');             //产品id
        $order_amount  = $request->post('order_amount');           //投资金额
        $user_id       = Session::get('user_id');                  //用户id
        $order_sn      = $CreateNumber->createOrderNumber('XJH');  // 订单号
        $addtime       = time();                                   //下单时间
        $order_status  = 1;                                        //订单状态  1、未支付    2、已支付
        $interest_time = strtotime("+1 day");                      //计息开始时间
        $regular       = strtotime("+1 year");                     //锁定期
        $interest_end_time = strtotime("+1 year");                 //计息结束时间
        $rate          = 8;                                        //年化利率

        //判断 是否拥有产品
        $productinfo = Productinfo::get($product_id);
        if($productinfo['invest_amount'] >= 200){
            echo json_encode(array('code'=>3,'codeInfo'=>'暂无产品。。。'));exit;
        }

        //处理 入库（订单）数据
        $data = array(
            'order_sn'          => $order_sn,
            'userid'            => $user_id,
            'product_id'        => $product_id,
            'order_amount'      => $order_amount,
            'addtime'           => $addtime,
            'order_status'      => $order_status,
            'interest_time'     => $interest_time,
            'regular'           => $regular,
            'interest_end_time' => $interest_end_time,
            'rate'              => $rate,
            'type_id'           => 4,
        );

        $order = new Order();
        $order_id = $order->addOrder($data);
        if($order_id){
            echo json_encode(array('code'=>1,'codeInfo'=>base64_encode($order_id)));
        } else {
            echo json_encode(array('code'=>0,'codeInfo'=>'订单异常，请稍后再试。。。'));
        }

    }

    //订单支付
    public function OrderPayment(){
        $nav_list = $this->nav; //导航
        $request = Request::instance();
        $order_id = base64_decode($request->get('order_id'));

        $order = Order::get($order_id);
        return view('alipay',['order'=>$order,'nav'=>$nav_list]);
    }

    public function alipay()
    {//发起支付宝支付
        if(request()->isPost()){
            
            $Pay = new Pay;
            $result = $Pay->alipay([
                'notify_url' => request()->domain().'/alipay_notify',
                'return_url' => request()->domain().'/salaryplan',
                'out_trade_no' => input('post.orderid/s','','trim,strip_tags'),
                'subject' => input('post.subject/s','','trim,strip_tags'),
                'total_fee' => input('post.total_fee/f'),//订单金额，单位为元
                'body' => input('post.body/s','','trim,strip_tags'),
            ]);
            if(!$result['code']){
                return $this->error($result['msg']);
            }
            return $result['msg'];
        }
        $this->view->orderid = date("YmdHis").rand(100000,999999);
        return $this->fetch();
    }

    public function alipay_notify()
    {//异步订单通知
        $Pay = new Pay;
        $result = $Pay->notify_alipay();
        exit($result);
    }
}