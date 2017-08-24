<?php
namespace app\index\model\product;

use think\Model;

use think\Request;

/**
*  @author [name] zhaojie <[QQ Tel]> 847819559 15935001979 
*  生成 编号 类
*/

class CreateNumber extends Model
{

	/**
	 * 生成 薪计划  编号 规则
	 */
	public function getSalaryplanNumber(){

		$type_name = '薪计划'; 
		$date = substr(date('Ymd'),2);
		$salaryplanNumber = $type_name.$date.'期';
		return $salaryplanNumber;
	}

	/**
	 * 生成 订单号 规则
	 * $type 订单前缀（用来识别 订单类别）
	 */
	public function createOrderNumber($type){

		$str = date('YmdHis').mt_rand(100,999);
		$order_number = $type.$str;
		return $order_number;

	}


}