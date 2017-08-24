<?php
namespace app\index\model\product;

use think\Model;

use think\Request;

/**
*  @author [name] zhaojie <[QQ Tel]> 847819559 15935001979 
*  添加 订单 类
*/

class Order extends Model
{

	/**
	 * 添加订单
	 * @param [type] $data [description] 添加订单数据源
	 * @return 自增 ID
	 */
	public function addOrder($data){

		$order = new Order;
		$order->data($data);
		$order->save();
		return $order->id;
		
	}

	


}