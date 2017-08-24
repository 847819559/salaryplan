<?php
namespace app\index\controller;
use think\Db;
use think\Request;
use think\Controller;
class Common extends Controller
{
	//查询
    public function getNav()
    {
    	$nav = Db::name('productclass')->select();
    	return $nav;
    }




}
