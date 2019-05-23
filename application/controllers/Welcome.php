<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends Base_Controller {

	public function index()
	{
        $a = FALSE;
	    var_dump(empty($a));
//	    $a = 11/0;
        $token = jwt_helper::create(1);
//        var_dump($token);die;
//        $CI = & self::get_instance();
//        $this->load->library('bcrypt');
        $password = 'ssss';
        //利用bcrypt生成密码哈希
        $hash = $this->bcrypt->hash_password($password);

        //验证密码是否正确
        $check = $this->bcrypt->check_password('ssss',$hash);
//        var_dump($this->load->helper('language'));die;
//        $this->load->helper('language');
        $level = 'hahha';
        $msg = 'qianissssssssssssssssssssssssssssssma';
        $type = 'db';

        //访问log组件
        $this->log->custom_write_log($level,$msg,'db');
        die;
//        log_message($level,$msg);die;
//	    returnJson('403',lang('ceshi'));die;



	    $this->my_form_validation->valid_url('http:/www.aa.com');die;

		$this->load->view('welcome_message');
	}


	function convert()
    {

        $db = $this->load->database('default',true);
        $sql = "select * from aaa";
        $list = $db->query($sql)->result_array();

        $csvpath = APPPATH.'cache/excel/aaa'.time().'.csv';
//        var_dump($csvpath);die;
        $handle = fopen( $csvpath, 'wb' );
        $header = array(
//            iconv( 'UTF-8', 'GB2312//IGNORE', 'asset' ),
            iconv( 'UTF-8', 'GBK', 'number' ),
            iconv( 'UTF-8', 'GBK', 'zifu' ),
            iconv( 'UTF-8', 'GBK', 'fudian' ),
            iconv( 'UTF-8', 'GBK', 'zifunumber' ),
            iconv( 'UTF-8', 'GBK', 'huobi' ),
        );
        fputcsv( $handle, $header );
        foreach($list as $value){
            $fields =   [
                $value['number']."\t",
                iconv('UTF-8','GBK',$value['zifu']."\t"),
                $value['fudian'],
                $value['zifunumber']."\t",
                $value['huobi']."\t",
            ];
            fputcsv( $handle, $fields);
        }
        fclose($handle);

    }
}
