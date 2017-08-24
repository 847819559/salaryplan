<?php
namespace app\index\model\product;

use think\Model;

use think\Request;

/**
*  @author [name] zhaojie <[QQ Tel]> 847819559 15935001979 
*  数据合法性验证类
*/

class VerifyLegal extends Model
{

	/**
	 * 判断 薪计划 金额是否合法
	 * @return [type] bool [description]
	 */
	public function verifySalaryplanPrice($price){

		//如果 金额 <500 提示
		if($price < 500){
			return '您最少购买500元';
			exit;
		} else if($price > 20000) {
			return '您最多只能购买20000元';
			exit;
		}

		$new_price = ($price / 100);
		if(!is_int($new_price)){
			return '递增金额需为100元的整数倍';
			exit;
		} else {
			return true;
		}

	}

}