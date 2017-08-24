<?php
namespace app\index\controller;
use think\Db;
use think\Request;
use think\Controller;
use app\index\controller\Common;
class IncomeCalculation extends Common
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

	// 收益计算
    public function calculation()
    {
    	$nav_list = $this->nav; //导航
    	return view('calculation',[
    			'nav' => $nav_list,
    		]);
    }

    //薪计划 收益 计算
    public function XJH_calculation(){
        
        $price = Input('price'); //价格
        $year_cate = Input('cate'); //利率
        $fixMonth = Input('fixMonth'); //投资周期
        $new_price = 0;
        $month_cate = ($year_cate/100)/$fixMonth; //月利率

        $i = 1;
        while ($i <= $fixMonth) {
            $new_price = floor(floor((($new_price + $price)*$month_cate)) + ($new_price + $price));
            $i++;
        }
        echo $new_price-($price*$fixMonth);
    }




}
