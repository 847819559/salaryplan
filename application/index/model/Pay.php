<?php
namespace app\index\model;
use think\Validate;
use think\Log;
class Pay extends \think\Model
{
    public static $alipay_config = [
        'partner' 			=> '2088121321528708',//支付宝partner，2088开头数字
        'seller_id' 		=> '2088121321528708',//支付宝partner，2088开头数字
        'key' 				=> '1cvr0ix35iyy7qbkgs3gwymeiqlgromm',//支付宝密钥
        'sign_type' 		=> 'MD5',
        'input_charset' 	=> 'utf-8',
        'cacert' 			=> '',
        'transport' 		=> 'http',
        'payment_type' 		=> '1',
        'service' 			=> 'create_direct_pay_by_user',
        'anti_phishing_key'	=> '',
        'exter_invoke_ip' 	=> '',
    ];

    public function alipay($data=[])
    {//发起支付宝支付
        $validate = new Validate([
            ['out_trade_no','require|alphaNum','订单编号输入错误|订单编号输入错误'],
            ['total_fee','require|number|gt:0','金额输入错误|金额输入错误|金额输入错误'],
            ['subject','require','请输入标题'],
            ['body','require','请输入描述'],
            ['notify_url','require','异步通知地址不为空'],
        ]);
        if (!$validate->check($data)) {
            return ['code'=>0,'msg'=>$validate->getError()];
        }
        $config = self::$alipay_config;
        vendor('alipay.alipay');
        $parameter = [
            "service"       	=> $config['service'],
            "partner"       	=> $config['partner'],
            "seller_id"  		=> $config['seller_id'],
            "payment_type"		=> $config['payment_type'],
            "notify_url"		=> $data['notify_url'],
            "return_url"		=> $data['return_url'],
            "anti_phishing_key"	=> $config['anti_phishing_key'],
            "exter_invoke_ip"	=> $config['exter_invoke_ip'],
            "out_trade_no"		=> $data['out_trade_no'],
            "subject"			=> $data['subject'],
            "total_fee"			=> $data['total_fee'],
            "body"				=> $data['body'],
            "_input_charset"	=> $config['input_charset']
        ];
        $alipaySubmit = new \AlipaySubmit($config);
        return ['code'=>1,'msg'=>$alipaySubmit->buildRequestForm($parameter,"get", "确认")];
    }

    public function notify_alipay()
    {//异步订单结果通知
        $config = self::$alipay_config;
        vendor('alipay.alipay');
        $alipayNotify = new \AlipayNotify($config);
        if($result = $alipayNotify->verifyNotify()){
            if(input('trade_status') == 'TRADE_FINISHED' || input('trade_status') == 'TRADE_SUCCESS') {
                // 处理支付成功后的逻辑业务
                Log::init([
                    'type'  =>  'File',
                    'path'  =>  LOG_PATH.'../paylog/'
                ]);
                Log::write($result,'log');
                return 'success';
            }
            return 'fail';
        }
        return 'fail';
    }
}
?>