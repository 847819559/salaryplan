<?php
namespace app\index\controller;

use think\Db;
use think\Request;
use think\Controller;
use app\index\model\user\Userverify;
use app\index\model\user\Userinfo;
use app\index\model\product\CreateNumber;
use app\index\model\product\Productinfo;
use app\index\controller\Common;

class Index extends Common
{

    //公共导航
    protected $nav = '';

    /**
     * 初始化
     * $nav   公共导航
     * @return [type] [description]
     */
    public function _initialize(){

        $this->nav = $this->getNav();
    }


	//首页
    public function index(){

        //优选计划
        $excellentplan = Productinfo::where(['product_status'=>1,'product_type_id'=>3])->find();
        $excellentplan['rate'] = $this->sprintf($excellentplan['rate']);
        //U计划
        $uplan = Productinfo::where(['product_type_id'=>2,'is_show'=>1])->select();
        foreach ($uplan as $key => $value) {
            $uplan[$key]['rate'] = $this->sprintf($value['rate']);
        }
        //薪计划
        $salaryplan = Productinfo::where('product_status=0 OR product_status=1')
                                    ->where('product_type_id=4')
                                    ->order('id','desc')
                                    ->find();
        $salaryplan['rate'] = $this->sprintf($salaryplan['rate']);
        $salaryplan['day']  = date('d',$salaryplan['invest_time']); //每月投资日

        //遍历数据
        $data = [
            'nav'           => $this->nav,     //导航
            'excellentplan' => $excellentplan, //优选计划
            'uplan'         => $uplan,         //U计划
            'salaryplan'    => $salaryplan,    //薪计划
        ];
    	return view('index',$data);
    }


    public function sprintf($data){
        return sprintf("%.1f",$data);
    }


    //U计划
    public function uPlan(){

        //遍历数据
        $data = [
            'nav' => $this->nav,   //导航
        ];
    	return view('uPlan',$data);
    }

    //测试接口
    public function getUser(){
    	$CreateNumber = new CreateNumber;
        $res = $CreateNumber->getSalaryplanNumber();
        echo $res;
    }



}
