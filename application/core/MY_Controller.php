<?php
/**
 * Created by PhpStorm.
 * User: zuojun
 * Date: 2019-04-25
 * Time: 14:57
 */

class Base_Controller extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        header("Access-Control-Allow-Origin : * ");
        header("Access-Control-Max-Age : 3600 ");
        date_default_timezone_set('PRC');
        //初始化默认语言
        $this->lang->load(array('info','other','db','upload','form_validation'),'zh-hans');
    }
}