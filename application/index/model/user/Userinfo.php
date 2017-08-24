<?php
namespace app\index\model\user;

use think\Model;

use think\Request;

/**
*  @author [name] zhaojie <[QQ Tel]> 847819559 15935001979 
*  用户xx类
*/

class Userinfo extends Model
{

	// 设置当前模型对应的完整数据表名称
    protected $table = 'userinfo';

    // 转换 数据 类型
	protected $type = [
        'regtime'  => 'timestamp',
    ];


}