<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Session;
class Login extends Controller{
    public function index(){
        return $this->fetch('login');
    }
    //调发送短信接口 返回json值
    public function tel(){
        $tel=$_POST['tel'];
        $code = rand(1000,9999);
        $url="http://api.k780.com/?app=sms.send&tempid=51105&param=usernm%3Dadmin%26code%3D$code&phone=$tel&appkey=23213&sign=89d9875888695333ad91320961af34dc";
        file_get_contents($url);
        return  json_decode($code,true);
    }
    //验证手机唯一性
    public function sel(){
        $data=input();
        $msg=Db::name('userinfo')->where('telephone',$data['telephone'])->select();
        if($msg){
            //成功返回注册
            echo 0;
        }else{
            //失败返回可以使用
            echo 1;
        }
    }
    public function add(){
        $data=input();
        //用户名
        $data['username']="DY".rand(10000,99999);
        $data['password']=md5($data['password']);
        //注册成功的时间
        $data['regtime']=time();
        $msg=Db::name('userinfo')->where('telephone',$data['telephone'])->select();
        if($msg){
            //手机号已被注册返回 0
            echo 0;
        }else{
            if($data['code_one']==$data['code_two']){
                unset ($data['code_one']);
                unset ($data['code_two']);
                $res=Db::name('userinfo')->insert($data);
                if($res){
                    //成功返回
                    echo 1;
                    //存 Session
                    $msg=Db::name('userinfo')->where('telephone',$data['telephone'])->find();
                    $id=$msg['id'];
                    Session::set('username',$msg['username']);
                    Session::set('user_id',$id);
                }
            }else{
                //手机验证码错误
                echo 2;
            }
        }
    }
    //登录
    function login(){
        $data=input();
        //手机号正确===》验证密码
        $pwd=md5($data['password']);
        $msg=Db::name('userinfo')->where(['telephone'=>$data['tel']])->find();

        if($pwd == $msg['password']){
            //成功存session
            $id=$msg['id'];
            $time=date("Y-m-d H:i:s",time());
            Db::table('userinfo')->where(['id'=>$id])->update(['realname'=>$time]);
            Session::set('username',$msg['username']);
            Session::set('user_id',$id);
            echo 3;
            
        }else{
            //用户名或密码错误
            echo 1;
        }

    }
    //退出登录
    public function login_out(){
        Session::delete('user_id');
        return $this->fetch('index/index');
    }
    //忘记密码
    public function forget(){
        return view();
    }
}