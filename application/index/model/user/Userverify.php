<?php
namespace app\index\model\user;

use think\Model;

use think\Request;

use think\Session;

/**
*  @author [name] zhaojie <[QQ Tel]> 847819559 15935001979 
*  用户验证类
*/

class Userverify extends Model
{

	/**
	 * 判断 用户 是否登录
	 * @return [type] bool [description]
	 */
	public function userStatus(){

		if( Session::has('user_id') ){
			return true;
		} else {
			return false;
		}
	}

}