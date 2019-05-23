<?php

function zj_to_utf8($item)
{
    if (is_array($item)) {
        foreach ($item as $k => $v) {
            $item[$k] = zj_to_utf8($v);
        }
    } else {
        if (!mb_check_encoding($item, 'UTF-8')) {
            $item = mb_convert_encoding($item, "UTF-8",
                mb_detect_encoding($item, mb_list_encodings(), true));
        }
    }
    return $item;
}

/**
 * 返回api接口json格式
 * @param $errorNo  int 状态码
 * @param $errorMsg string 提示信息
 * @param $data     array  返回的数据
 */
function returnJson($errorNo='',$errorMsg='',$data='') {
    // 返回JSON数据格式到客户端 包含状态信息
    header('Content-Type:application/json; charset=utf-8');
    $item = array(
        'errorNo'=>$errorNo,
        'errorMsg'=>$errorMsg,
    );
    if($data && is_array($data)){
        $item['data']=$data;
    }
    exit(json_encode(zj_to_utf8($item),JSON_UNESCAPED_SLASHES));//JSON_UNESCAPED_SLASHES/JSON_FORCE_OBJECT
}

/**
 * 以sha256方式生成hash值
 * @return [type] [description]
 */
if (! function_exists('dealStrHidden')) {

    function hash256($string)
    {
        return hash('sha256', $string);
    }

}

/*
 * 发送消息
 * @author zf
 * @param str $user_id
 * @param str $title  标题
 * @param str $text   内容
 * @param str $msg_type_id  消息类型（默认通知）
*/

if (! function_exists('send_notification'))
{
    function send_notification($user_id,$title,$text,$site_id,$msg_type_id = 0){
        // $site_id = get_user_site_id($user_id);
        $ci =& get_instance();
        $ci->load->model('User_notification_model');
        $is_view = 0;//是否已读
        $time  = date('Y-m-d H:i:s',time()); //创建日期
        $ci->User_notification_model->add_notification($user_id,$title,$text,$site_id,$msg_type_id,$is_view,$time);
    }
}

//添加后台管理员对用户的操作记录日志
if (! function_exists('admin_operation_logs'))
{
    function admin_operation_logs($admin_id,$user_id,$operation_type,$description,$time=0,$business_id = 0){
        // $site_id = get_user_site_id($user_id);
        $ci =& get_instance();
        $ci->load->model('User_notification_model');
        $time  = date('Y-m-d H:i:s',time()); //创建日期
        $ci->User_notification_model->add_admin_operationlogs($admin_id,$user_id,$operation_type,$time,$description,$business_id);
    }
}

//添加后台管理员对银行卡的操作记录日志
if (! function_exists('admin_banks_logs'))
{
    function admin_banks_logs($admin_id,$m_bank_id,$type,$description,$time){
        // $site_id = get_user_site_id($user_id);
        $ci =& get_instance();
        $ci->load->model('User_notification_model');
        $time  = date('Y-m-d H:i:s',time()); //创建日期
        $ci->User_notification_model->add_admin_operbanklogs($admin_id,$m_bank_id,$type,$description,$time);
    }
}




/*
 * 根据user_id获取用户的站点 site_id
 * @author zuojun
 * @param str $user_id
*/
if (! function_exists('get_user_site_id'))
{
    function get_user_site_id($user_id)
    {
        $ci =& get_instance();
        $ci->load->model('User_model');
        return $ci->User_model->get_info($user_id)['site_id'];
    }
}

if (! function_exists('custom_log_message'))
{
    function custom_log_message($level,$message)
    {
        static $_log;

        if ($_log === NULL)
        {
            // references cannot be directly assigned to static variables, so we use an array
            $_log[0] =& load_class('Log', 'core');
        }

        $_log[0]->my_write_log($level, $message);
    }
}

/**
 * 密码加密
 *
 */

if (! function_exists('my_password_hash'))
{
    function my_password_hash($password)
    {
        $step1 = $password;
        $step2 = substr($step1, 0, 10);
        return md5($step1 . $step2);
    }
}

/**
 * 生成随机字符串
 * @param $length 长度
 */
function getRandChar($length){
    $str = null;
    $strPol = "0123456789abcdefghijklmnopqrstuvwxyz";
    $max = strlen($strPol)-1;

    for($i=0;$i<$length;$i++){
        $str.=$strPol[rand(0,$max)];
    }
    return $str;
}

/**
 * 邮件发送
 *
 */

if (! function_exists('send_email'))
{
    function send_email($to, $replacement = array(),$site_id, $uniqueid = 'general')
    {
        $CI =& get_instance();
        $CI->load->library('email');
        $CI->load->model('Mail_queue_model');
        $CI->load->model('Mail_tpl_model');
        $tpl = $CI->Mail_tpl_model->get($uniqueid,$site_id);

        if(!$tpl){
            return false;
        }
        $content = $tpl['content'];
        foreach ($replacement as $search => $replace) {
            $content = str_replace("{" . $search . "}", $replace, $content);
        }

        $title = $tpl['title'];
        foreach ($replacement as $search => $replace) {
            $title = str_replace("{" . $search . "}", $replace, $title);
        }

        $CI->load->helper('config');
        //发送邮件
        $config=$CI->config->item('email_config');//加载配置

        $CI->email->initialize($config);//应用配置
        //设置发送内容
        $CI->email->from('hmf@baoquan.com', '算力网');//发送方
        $CI->email->to($to);//接收方
        $CI->email->subject($title);//标题
        $CI->email->message($content);//主题（内容样式可自行编辑）
        //$this->email->attach('./static/home/images/logo.png'); //可以传递附件
        // echo $this->email->send();//成功返回1

        return $CI->email->send();
    }
}

if (! function_exists('send_tongji_email'))
{
    function send_tongji_email()
    {
        $CI =& get_instance();

        $CI->load->library('email');
        $CI->load->helper('config');
        //发送邮件
        $config=$CI->config->item('email_config');//加载配置
        $CI->email->initialize($config);//应用配置


        //设置发送内容
        // $CI->email->from('zjaizyyy@163.com');//发送方
        // $to = '804018306@qq.com';

        $CI->email->from('zjun@numchain.com');//发送方
        $to = 'zjaizyyy@163.com';

        $title = 'asdfasdf';
        $content = 'sdfasdfasdfasdfasdf';
        $CI->email->to($to);//接收方
        $CI->email->subject($title);//标题
        $CI->email->message($content);//主题（内容样式可自行编辑）
        //$this->email->attach('./static/home/images/logo.png'); //可以传递附件
        // echo $this->email->send();//成功返回1
        if(!$CI->email->send()){
            echo $CI->email->print_debugger();
        }
    }
}

//json格式的数据
if (! function_exists('json_data'))
{
    function json_data($code = 200, $msg ='',$data = array()){
        $result = array(
            'error' => $code,
            'msg'   =>$msg,
            'data'  => $data
        );
        return $result;
    }
}

//每t算力收益
if (! function_exists('income_per_byte'))
{
    function income_per_byte(){
        $ci =& get_instance();
        $ci->load->service('Tool_service');
        $difficult_data_out = $ci->Tool_service->btcfans_common_data();

        $difficulty = str_replace(',', '', $difficult_data_out['data']['difficulty']);
        $btc_output = round(1000/($difficulty*7.158*0.001)*1800,8);

        $data['income_per_byte'] = round($difficult_data_out['data']['btc_price'] * $btc_output ,2);
        $data['income_per_byte_usd'] = round($difficult_data_out['data']['usd_price'] * $btc_output ,2);

        return $data;
    }
}

//生成验证码
if (! function_exists('generate_code'))
{
    function generate_code($length = 4) {
        $min = pow(10 , ($length - 1));
        $max = pow(10, $length) - 1;
        return rand($min, $max);
    }
}


//php生成邀请码
if (! function_exists('get_invite_code'))
{
    function get_invite_code() {
        $charid = strtoupper(md5(uniqid(mt_rand(), true)));

        $hyphen = chr(45);// "-"
        $uuid = substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,20,12);
        return $uuid;
    }
}

/**
 * 对象 转 数组
 *
 * @param object $obj 对象
 * @return array
 */
function object_to_array($obj) {
    $obj = (array)$obj;
    foreach ($obj as $k => $v) {
        if (gettype($v) == 'resource') {
            return;
        }
        if (gettype($v) == 'object' || gettype($v) == 'array') {
            $obj[$k] = (array)object_to_array($v);
        }
    }

    return $obj;
}


//获取站点配置
function get_site_domain($site_id)
{
    $ci =& get_instance();
    $ci->load->service('Site_service');
    $site_info = $ci->Site_service->get_site($site_id);

    if (empty($site_info)){
        returnJson('101',lang('unopen_site'));
    }
    return $site_info;
}


//获取配置信息
function config_info($name)
{
    $ci =& get_instance();
    $ci->load->model('Config_model');
    $config = $ci->Config_model->get_data_by_name($name);
    $fee = $config['value'];
    return $fee;

}


/**
 * 邮箱加密
 * @param  $email string
 * @return $email string
 */
function star_email($email){
    $arr = explode('@',$email);
    $str1 = $arr[0];
    $len = strlen($str1);
    if($len > 2){
        return str_replace(substr($str1,1,$len-2),str_repeat("*", $len-2),$str1).'@'.$arr[1];
    }
    return $email;


}

/**
 * 权限列表
 * @return $arr array
 */
function privs(){
    $ci =& get_instance();
    if($ci->config->item('SITE')==='priv'){
        require(APPPATH.'config/priv.php');
    }else{
        require(APPPATH.'config/priv_one.php');
    }

    $arr = array();
    foreach ($admin_menu_file as $key => $val){
        foreach ($val as $v){
            $v['parent_name'] = $key;
            $arr[$v['id']] = $v;
        }
    }
    return $arr;
}

/**
 * 判断手机号格式是否正确
 */
function WSTIsPhone($phoneNo){
    // if(preg_match("/^1[34578]{1}\d{9}$/",$phoneNo)){
    if(preg_match("/^\d+$/",$phoneNo)){
        // if($phoneNo){ //取消手机号格式限制
        return true;
    }else{
        return false;
    }
}

/*
* 获取当前用户信息
* return string
*/

if (! function_exists('user_type'))
{
    function user_type($user_id){
        $ci =& get_instance();
        $ci->load->service('User_service');
        $user_info =$ci->User_service->get_info($user_id);
        if(!$user_info){
            return false;
        }

        if($user_info['email']=='')
        {
            $account_type = 'mobile';
        }
        else
        {
            $account_type = 'email';
        }

        $data['account_type'] = $account_type;
        $data['user_account'] = $user_info[$account_type];
        return  $data;
    }
}


if (! function_exists('send_msg'))
{
    function send_msg($user_id, $replacement = array(), $uniqueid = ''){
        $ci =& get_instance();
        $ci->load->service('User_service');
        $user_info =$ci->User_service->get_info($user_id);
        $site_id = $user_info['site_id'];
        $info = user_type($user_id);
        if($info['account_type'] == 'email'){
            $res = send_email($info['user_account'],$replacement,$site_id , $uniqueid);
        }else{
            $res =sms_helper::sendSMS($info['user_account'],$replacement,$site_id,$uniqueid);
        }
        if(!$res) return false;
        return true;

    }

    /*
    * 根據郵箱或手機號獲取用戶ID
    * @param str $user_account
    * return int
    */
    function get_account_to_id($user_account){
        $ci =& get_instance();
        $ci->load->service('User_service');
        if(WSTIsPhone($user_account)){
            $user_info = $ci->User_service->get_info_by_mobile($user_account);
        }else{
            $user_info = $ci->User_service->get_info_by_email($user_account);
        }
        if(!$user_info)return false;
        return $user_info['id'];
    }


    //curl获取数据
    function get_user_assets_by_curl($user_id,$site_id=1){
        $ci =& get_instance();
        $ci->load->service('Zjys_assets_service');
        $arr = array();
        $userassets = $ci->Zjys_assets_service->list_all($site_id);
        $aaa = count($userassets);
        if($aaa == 0){
            returnJson('402','当前站点无资产数据！');
        }
        // var_dump($userassets);die;
        foreach ($userassets as  &$val) {
            $post_data = array(
                'id' => 1,
                'method' => 'balance.query',//查询资产余额
                'params' => array(
                    (int)$user_id,$val['asset_code']
                ),
            );
            $url = $ci->config->item('VIABTC_API_URL');
            // var_dump($url);die;
            // $url = 'http://localhost:8080';
            $post_data = json_encode($post_data);
            // var_dump($post_data);die;
            $ch = curl_init();
            // 设置请求为post类型
            curl_setopt($ch, CURLOPT_POST, 1);
            // 添加post数据到请求中
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            // 2. 设置请求选项, 包括具体的url
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            // 3. 执行一个cURL会话并且获取相关回复
            $response = curl_exec($ch);
            // var_dump($response);die;
            // 4. 释放cURL句柄,关闭一个cURL会话
            curl_close($ch);
            $response = object_to_array(json_decode($response));
            array_push($arr, $response['result']);

        }
        $newarray = array();
        foreach ($arr as $k => $v){
            foreach ($v as $key=>$value) {
                // var_dump($key);die;
                // $c2c_freeze = get_c2c_freezen_assets($key,$user_id);
                //获取币种所对平台法币价格
                // var_dump($key);
                if($key == $ci->config->item('C2C_ASSET')){
                    $price_trans = 1;
                }else{
                    $price_trans = get_market_last($key.'_'.$ci->config->item('C2C_ASSET'));
                    // var_dump($price_trans);
                }
                $last_balance = $ci->Zjys_user_withdraw_model->get_last_balance($key,$user_id);
                if(empty($last_balance))
                    $last_balance['balance'] = 0;
                //var_dump($last_balance);die;
                // var_dump($price_trans);die;
                $newarray[$key]['available'] = $value['available']*$price_trans;
                // $newarray[$key]['freeze']= $value['freeze']+$c2c_freeze; //交易冻结
                $newarray[$key]['freeze']= $value['freeze']*$price_trans; //交易冻结
                // $newarray[$key]['price_trans']= $price_trans; //交易冻结
                // $newarray[$key]['symbol']= $key.'_'.$ci->config->item('C2C_ASSET'); //交易冻结
                //获取当前用户的冻结金额
                // $withdraw_freeze = get_withdraw_freezen_assets($key,$user_id);
                $newarray[$key]['other_freeze'] = $last_balance['balance']*$price_trans;
                $newarray[$key]['total_trans'] = ($last_balance['balance'] + $value['freeze'] + $value['available'])*$price_trans;
            }
        }

        // var_dump($newarray);die;

        $asss = array();
        $aa = array_values($newarray);
        $new_array = array();
        foreach ($aa as $key => $value) {
            $val = array_values($value);
            foreach ($value as $k => $v) {
                if(! isset($new_array[$k]))
                    $new_array[$k] = $v;
                else
                    $new_array[$k] += $v;
            }
        }
        // var_dump($new_array);die;
        return $new_array;
    }

    //所有币种
    function get_user_assets_by_curl_all($user_id,$site_id=1){
        $ci =& get_instance();
        // echo $ci->config->item('VIABTC_API_URL');die;
        $ci->load->service('Zjys_assets_service');
        $arr = array();
        $userassets = $ci->Zjys_assets_service->get_systemassets();
        // var_dump($userassets);die;
        if(is_array($userassets) && empty($userassets)) returnJson('402','无资产数据');



        foreach ($userassets as  &$val) {
            $post_data = array(
                'id' => 1,
                'method' => 'balance.query',//查询资产余额
                'params' => array(
                    (int)$user_id,$val['asset_code']
                ),
            );
            $url = $ci->config->item('VIABTC_API_URL');
            // var_dump($url);die;
            // $url = 'http://localhost:8080';
            $post_data = json_encode($post_data);
            // var_dump($post_data);
            $ch = curl_init();
            // 设置请求为post类型
            curl_setopt($ch, CURLOPT_POST, 1);
            // 添加post数据到请求中
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            // 2. 设置请求选项, 包括具体的url
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            // 3. 执行一个cURL会话并且获取相关回复
            $response = curl_exec($ch);
            // var_dump($response);die;
            // 4. 释放cURL句柄,关闭一个cURL会话
            curl_close($ch);
            $response = object_to_array(json_decode($response));
            array_push($arr, $response['result']);
        }
        $newarray = array();
        //var_dump($arr);die();
        foreach ($arr as $k => $v){
            foreach ($v as $key=>$value) {
                // var_dump($key);die;
                // $c2c_freeze = get_c2c_freezen_assets($key,$user_id);
                //获取币种所对平台法币价格
                // var_dump($key);
                if($key == $ci->config->item('C2C_ASSET')){
                    $price_trans = 1;
                }else{
                    $price_trans = get_market_last($key.'_'.$ci->config->item('C2C_ASSET'));
                    // var_dump($price_trans);
                }
                $last_balance = $ci->Zjys_user_withdraw_model->get_last_balance($key,$user_id);
                if(empty($last_balance))
                    $last_balance['balance'] = 0;
                //var_dump($last_balance);die;
                $newarray[$key]['available'] = $value['available']*$price_trans;
                // $newarray[$key]['freeze']= $value['freeze']+$c2c_freeze; //交易冻结
                $newarray[$key]['freeze']= $value['freeze']*$price_trans; //交易冻结
                // $newarray[$key]['price_trans']= $price_trans; //交易冻结
                // $newarray[$key]['symbol']= $key.'_'.$ci->config->item('C2C_ASSET'); //交易冻结
                //获取当前用户的冻结金额
                // $withdraw_freeze = get_withdraw_freezen_assets($key,$user_id);
                $newarray[$key]['other_freeze'] = $last_balance['balance']*$price_trans;
                $newarray[$key]['total_trans'] = ($last_balance['balance'] + $value['freeze'] + $value['available'])*$price_trans;
            }
        }

        // var_dump($newarray);die;

        $asss = array();
        $aa = array_values($newarray);
        $new_array = array();
        foreach ($aa as $key => $value) {
            $val = array_values($value);
            foreach ($value as $k => $v) {
                if(! isset($new_array[$k]))
                    $new_array[$k] = $v;
                else
                    $new_array[$k] += $v;
            }
        }
        // var_dump($new_array);die;
        return $new_array;
    }



    function get_market_last($symbol)
    {
        // usleep(200000);
        $ci =& get_instance();
        $post_data = array(
            'id' => 1,
            'method' => 'market.last',//查询资产余额
            'params' => array(
                $symbol
            ),
        );
        $url = $ci->config->item('VIABTC_API_URL');
        // var_dump($url);die;
        // $url = 'http://localhost:8080';
        $post_data = json_encode($post_data);
        // var_dump($post_data);die;
        $ch = curl_init();
        // 设置请求为post类型
        curl_setopt($ch, CURLOPT_POST, 1);
        // 添加post数据到请求中
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        // 2. 设置请求选项, 包括具体的url
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        // 3. 执行一个cURL会话并且获取相关回复
        $response = curl_exec($ch);
        // var_dump($response);die;
        // 4. 释放cURL句柄,关闭一个cURL会话
        curl_close($ch);
        $response = object_to_array(json_decode($response));
        if($response['error']===NULL)
            return $response['result'];
        // var_dump($response);die;
    }

    //查询用户指定币种的资产明细
    function get_per_user_assets_by_curl($user_id,$site_id=1)
    {
        $ci =& get_instance();
        $ci->load->service('Zjys_assets_service');
        $arr = array();
        $userassets = $ci->Zjys_assets_service->list_all($site_id);
        // var_dump($userassets);
        foreach ($userassets as  &$val) {
            $post_data = array(
                'id' => 0,
                'method' => 'balance.query',//查询资产余额
                'params' => array(
                    (int)$user_id,$val['asset_code']
                ),
            );
            // $url = 'http://localhost:8080';
            $url = $ci->config->item('VIABTC_API_URL');
            $post_data = json_encode($post_data);
            // var_dump($post_data);die;
            $ch = curl_init();
            // 设置请求为post类型
            curl_setopt($ch, CURLOPT_POST, 1);
            // 添加post数据到请求中
            // var_dump($post_data);die;
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            // 2. 设置请求选项, 包括具体的url
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            // 3. 执行一个cURL会话并且获取相关回复
            $response = curl_exec($ch);
            // var_dump($response);
            // 4. 释放cURL句柄,关闭一个cURL会话
            curl_close($ch);
            $response = object_to_array(json_decode($response));
            array_push($arr, $response['result']);
        }
        $newarray = array();
        // var_dump($arr);die;
        foreach ($arr as $k => $v){
            foreach ($v as $key=>$value) {
                // var_dump($ci->config->item('C2C_ASSET'));die;
                $c2c_freeze = get_c2c_freezen_assets($key,$user_id);
                $withdraw_freeze = get_withdraw_freezen_assets($key,$user_id);
                $lock_freeze = get_lock_freezen_assets($key,$user_id);
                $activity_freeze = get_activity_freezen_assets($key,$user_id); //所有的活动冻结

                $newarray[$key]['available'] = $value['available']; //交易系统可用
                $newarray[$key]['freeze']= $value['freeze']; //交易系统冻结
                $newarray[$key]['c2c_freeze']= $c2c_freeze; //c2c冻结
                $newarray[$key]['withdraw_freeze']= $withdraw_freeze; //提现冻结
                $newarray[$key]['lock_freeze']= $lock_freeze; //提现冻结
                $newarray[$key]['activity_freeze']= $activity_freeze; //活动冻结总和
            }
        }
        // var_dump($newarray);die;
        return $newarray;
    }


    //查询用户指定币种的资产明细-所有
    function get_per_user_assets_by_curl_all($user_id,$site_id=1)
    {
        $ci =& get_instance();
        $ci->load->service('Zjys_assets_service');
        $arr = array();
        $userassets = $ci->Zjys_assets_service->get_systemassets();
        // var_dump($userassets);
        foreach ($userassets as  &$val) {
            $post_data = array(
                'id' => 0,
                'method' => 'balance.query',//查询资产余额
                'params' => array(
                    (int)$user_id,$val['asset_code']
                ),
            );
            // $url = 'http://localhost:8080';
            $url = $ci->config->item('VIABTC_API_URL');
            $post_data = json_encode($post_data);
            // var_dump($post_data);die;
            $ch = curl_init();
            // 设置请求为post类型
            curl_setopt($ch, CURLOPT_POST, 1);
            // 添加post数据到请求中
            // var_dump($post_data);die;
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            // 2. 设置请求选项, 包括具体的url
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            // 3. 执行一个cURL会话并且获取相关回复
            $response = curl_exec($ch);
            // var_dump($response);
            // 4. 释放cURL句柄,关闭一个cURL会话
            curl_close($ch);
            $response = object_to_array(json_decode($response));
            array_push($arr, $response['result']);
        }
        $newarray = array();
        // var_dump($arr);die;
        foreach ($arr as $k => $v){
            foreach ($v as $key=>$value) {
                // var_dump($ci->config->item('C2C_ASSET'));die;
                $c2c_freeze = get_c2c_freezen_assets($key,$user_id);
                $withdraw_freeze = get_withdraw_freezen_assets($key,$user_id);
                $lock_freeze = get_lock_freezen_assets($key,$user_id);
                $activity_freeze = get_activity_freezen_assets($key,$user_id); //所有的活动冻结

                $newarray[$key]['available'] = $value['available']; //交易系统可用
                $newarray[$key]['freeze']= $value['freeze']; //交易系统冻结
                $newarray[$key]['c2c_freeze']= $c2c_freeze; //c2c冻结
                $newarray[$key]['withdraw_freeze']= $withdraw_freeze; //提现冻结
                $newarray[$key]['lock_freeze']= $lock_freeze; //提现冻结
                $newarray[$key]['activity_freeze']= $activity_freeze; //活动冻结总和
            }
        }
        // var_dump($newarray);die;
        return $newarray;
    }

    //根据币种查询汇率，并换算成人民币的
    function assets_rate($asset){
        $ci =& get_instance();
        $ci->load->service('Zjys_assets_service');
        // echo 555;die;
        // $url = 'http://localhost:8080';
        $url = $ci->config->item('VIABTC_API_URL');
        $post_data = array(
            'id' => 1,
            // 'method' => 'balance.query',//查询资产余额
            'method' => 'market.last', //查询交易汇率
            'params' => array(
                $asset.'_CNT'
            ),
        );

        $post_data = json_encode($post_data);
        // var_dump($post_data);die;
        $ch = curl_init();
        // 设置请求为post类型
        curl_setopt($ch, CURLOPT_POST, 1);
        // 添加post数据到请求中
        // var_dump($post_data);die;
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        // 2. 设置请求选项, 包括具体的url
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        // 3. 执行一个cURL会话并且获取相关回复
        $response = curl_exec($ch);
        // var_dump($response);
        // 4. 释放cURL句柄,关闭一个cURL会话
        curl_close($ch);
        $response = object_to_array(json_decode($response));
        // if()
        // var_dump($response);die;
        return $response['result'];
    }
    //获取不同币种的提现冻结资金
    function get_withdraw_freezen_assets($assets,$user_id)
    {
        $ci =& get_instance();
        $ci->load->service('Zjys_withdraw_service');
        return $ci->Zjys_withdraw_service->get_withdraw_freezen_by_assets_and_userid($assets,$user_id);
    }

    //获取不同币种的C2c冻结资金
    function get_c2c_freezen_assets($assets,$user_id)
    {
        $ci =& get_instance();
        $ci->load->service('Zjys_c2corder_service');
        return $ci->Zjys_c2corder_service->get_c2c_freezen_by_assets_and_userid($assets,$user_id);
    }
    //获取用户币种的锁仓冻结
    function get_lock_freezen_assets($assets,$user_id)
    {
        $ci =& get_instance();
        $ci->load->service('Zjys_withdraw_service');
        return $ci->Zjys_withdraw_service->get_lock_freezen_by_assets_and_userid($assets,$user_id);
    }
    //获取所有活动冻结之和
    function get_activity_freezen_assets($assets,$user_id)
    {
        $ci =& get_instance();
        $ci->load->service('Zjys_withdraw_service');
        return $ci->Zjys_withdraw_service->get_activity_freezen_by_assets_and_userid($assets,$user_id);
    }

    //将毫秒级时间戳变为时间格式 2018-1-1 07:23:43.2341
    function get_microtime_format($time)
    {
        if(strstr($time,'.')){
            sprintf("%01.4f",$time); //小数点。不足三位补0
            list($usec, $sec) = explode(".",$time);
            $sec = str_pad($sec,4,"0",STR_PAD_RIGHT); //不足3位。右边补0
        }else{
            $usec = $time;
            $sec = "0000";
        }
        $date = date("Y-m-d H:i:s.x",$usec);
        return str_replace('x', $sec, $date);
    }

    function get_source($source){
        $site_id = substr($source,strpos($source,',')+1);
        $ci =& get_instance();
        $ci->load->service('Zjys_c2corder_service');
    }

    //用户买币更新账户余额
    function update_user_balance_bycurl($user_id,$asset,$amount,$id,$ss,$busi){
        $ci =& get_instance();
        $ci->load->service('Zjys_assets_service');
        // $url = 'http://localhost:8080';
        $url = $ci->config->item('VIABTC_API_URL');
        $post_data = array(
            'id' => 1,
            'method' => 'balance.update', //账户更改资金
            'params' => array(
                (int)$user_id,$asset,$busi,(int)$id,(string)$amount,$ss
            ),
        );
        $post_data = json_encode($post_data);
        // var_dump($post_data);die;
        $ch = curl_init();
        // 设置请求为post类型
        curl_setopt($ch, CURLOPT_POST, 1);
        // 添加post数据到请求中
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        // 2. 设置请求选项, 包括具体的url
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        // 3. 执行一个cURL会话并且获取相关回复
        $response = curl_exec($ch);
        // 4. 释放cURL句柄,关闭一个cURL会话
        curl_close($ch);
        $response = object_to_array(json_decode($response));
        // var_dump($response);die;
        return $response;
    }

    function update_user_balance_bycurl_unfreeze($user_id,$asset,$amount,$id,$ss){
        $ci =& get_instance();
        $ci->load->service('Zjys_assets_service');
        // $url = 'http://localhost:8080';
        $url = $ci->config->item('VIABTC_API_URL');
        $post_data = array(
            'id' => 1,
            'method' => 'balance.update', //账户更改资金
            'params' => array(
                (int)$user_id,$asset,'admin_updatemoney',(int)$id,$amount,$ss
            ),
        );

        $post_data = json_encode($post_data);
        // var_dump($post_data);die;
        $ch = curl_init();
        // 设置请求为post类型
        curl_setopt($ch, CURLOPT_POST, 1);
        // 添加post数据到请求中
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        // 2. 设置请求选项, 包括具体的url
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        // 3. 执行一个cURL会话并且获取相关回复
        $response = curl_exec($ch);
        // 4. 释放cURL句柄,关闭一个cURL会话
        curl_close($ch);
        $response = object_to_array(json_decode($response));
        // var_dump($response);die;
        return $response;
    }

    //  从redis中获取btc_usd 和 usd_cny
    function get_btctocny_byredis($what=null)
    {
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        $redis->select(15);
        $btc_usd = $redis->get('currency:btc_usd');
        $usd_cny = $redis->get('currency:usd_cny');
        if(!$btc_usd || !$usd_cny) return false;
        if($what==='btc_usd'){
            return $btc_usd;
        }
        if($what==='usd_cny'){
            return $usd_cny;
        }
        // $redis->set('key10','xx10000',30);//第三个参数是存续时间，单位是秒，如果不填则为永久
        return $btc_usd*$usd_cny;
    }

    /*
    时间戳时间段整合成时间戳的数组
     */
    function get_timearea_by_day($start_time,$end_time)
    {
        $start_time = strtotime(date('Y-m-d',$start_time));
        $end_time = strtotime(date('Y-m-d',$end_time));
        // echo $start_time.'||'.$end_time;die;
        $val = $end_time-$start_time;
        $res = [];
        if($val < 0) return false;
        if($val == 0 || $val<=86400){
            array_push($res, $start_time);
            return $res;
        }
        do {
            array_push($res, $start_time);
            $start_time += 86400;
        } while ($start_time<=$end_time);
        return $res;
    }



    //curl获取数据
    function get_user_assets_by_curl1($user_id){
        $ci =& get_instance();
        $ci->load->service('Zjys_assets_service');
        $arr = array();
        $userassets = $ci->Zjys_assets_service->list_all_alarm();
        foreach ($userassets as  &$val) {
            $post_data = array(
                'id' => 1,
                'method' => 'balance.query',//查询资产余额
                'params' => array(
                    (int)$user_id,$val['asset_code']
                ),
            );
            $url = $ci->config->item('VIABTC_API_URL');
            // var_dump($url);die;
            // $url = 'http://localhost:8080';
            $post_data = json_encode($post_data);
            // var_dump($post_data);
            $ch = curl_init();
            // 设置请求为post类型
            curl_setopt($ch, CURLOPT_POST, 1);
            // 添加post数据到请求中
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            // 2. 设置请求选项, 包括具体的url
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            // 3. 执行一个cURL会话并且获取相关回复
            $response = curl_exec($ch);
            // var_dump($response);die;
            // 4. 释放cURL句柄,关闭一个cURL会话
            curl_close($ch);
            $response = object_to_array(json_decode($response));
            array_push($arr, $response['result']);
        }
        $newarray = array();

        foreach ($arr as $k => $v){
            if(is_array($v)){
                foreach ($v as $key=>$value) {
                    if($key == $ci->config->item('C2C_ASSET')){
                        $price_trans = 1;
                    }else{
                        $price_trans = get_market_last($key.'_'.$ci->config->item('C2C_ASSET'));
                    }

                    $last_balance = $ci->Zjys_user_withdraw_model->get_last_balance($key,$user_id);
                    if(empty($last_balance))
                        $last_balance['balance'] = 0;
                    $newarray[$key]['available'] = $value['available']*$price_trans;
                    $newarray[$key]['freeze']= $value['freeze']*$price_trans; //交易冻结
                    $newarray[$key]['other_freeze'] = $last_balance['balance']*$price_trans;
                    $newarray[$key]['total_trans'] = ($last_balance['balance'] + $value['freeze'] + $value['available'])*$price_trans; //总余额


                    //用户获取充值资产(币充值(user_recharge_logs)/法币充值(c2c_orders)
                    $c2c_orders = $ci->Zjys_c2corder_model->get_c2corder($key,$user_id);
                    if(empty($c2c_orders))
                        $c2c_orders['total'] = 0;
                    $newarray[$key]['c2c'] = $c2c_orders['total']*1;
                    // var_dump($key,$newarray[$key]['c2c'],$c2c_orders['total'],$price_trans);
                    // echo 10000;
                    $recharge_logs = $ci->Zjys_recharge_logs_model->get_user_recharge_logs($key,$user_id);
                    if(empty($recharge_logs))
                        $recharge_logs['total']= 0;
                    $newarray[$key]['recharge'] = $recharge_logs['total']*$price_trans;
                    //  var_dump($key,$newarray[$key]['recharge'],$recharge_logs['total'],$price_trans);
                    // echo 20000;
                    //用户提现充值资产(币提现(user_withdraws)/法币提现)
                    $user_withdraws = $ci->Zjys_user_withdraw_model->get_user_withdraws($key,$user_id);
                    if(empty($user_withdraws))
                        $user_withdraws['total'] = 0;
                    $newarray[$key]['withdraws'] = $user_withdraws['total']*$price_trans;
                    // var_dump($key,$newarray[$key]['withdraws'],$user_withdraws['total'],$price_trans);
                    //     echo 30000;
                    //
                    $user_activity = $ci->Zjys_user_withdraw_model->get_user_activity($key,$user_id);
                    if(empty($user_activity))
                        $user_activity['total'] = 0;
                    $newarray[$key]['user_activity'] = $user_activity['total']*$price_trans;

                }
            }
        }

        // var_dump($newarray);die;

        $asss = array();
        $aa = array_values($newarray);
        $new_array = array();
        foreach ($aa as $key => $value) {
            $val = array_values($value);
            foreach ($value as $k => $v) {
                if(! isset($new_array[$k]))
                    $new_array[$k] = $v;
                else
                    $new_array[$k] += $v;
            }
        }
        // var_dump($new_array);die;
        return $new_array;
    }

    //获取配置OSS配置
    function ossconfig(){
        $CI =& get_instance();
        return $CI->config->item('OSS');
    }

    //根据参数获取前一天的开始时间戳
    function get_last_day($args)
    {
        $time = isset($args['time']) ? $args['time'] : false;
        //未传time参数，就去前一天的参数
        if($time === false){
            $start_time = strtotime(date('Y-m-d',time()-86400));
        }else{
            $start_time = strtotime($time);
        }
        return $start_time;
    }
    //给二维数组按照值进行排序
    function mulsort($sort,$new){
        $arrSort = array();
        foreach($new AS $uniqid => $row){
            foreach($row AS $key=>$value){
                $arrSort[$key][$uniqid] = $value;
            }
        }
        if($sort['direction']){
            array_multisort($arrSort[$sort['field']], constant($sort['direction']), $new);
        }
        return $new;
    }
    /*
        desc: Common Curl Function Integration
        params: $method 请求方式 $url 地址 $parms 参数（可以是数组，也可以是urlencode之后的字符串）
        return: object
     */
    function curl_common($url,$params,$method)
    {
        $ch        = curl_init();
        if($method==='post'){
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $response  = curl_exec($ch);
        curl_close($ch);
        return $response;

    }

    //二维数组去重复
    function assoc_unique($arr, $key) {
        $tmp_arr = array();
        foreach ($arr as $k => $v) {
            if (in_array($v[$key], $tmp_arr)) {//搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true
                unset($arr[$k]);
            } else {
                $tmp_arr[] = $v[$key];
            }
        }
        sort($arr); //sort函数对数组进行排序
        return $arr;
    }


    /**
     * Notes: 获取用户，当前币种的钱包余额
     * User: 张哲
     * Date: 2019-04-19
     * Time: 09:33
     * @param $user_id
     * @param $asset
     */
    function check_balance($user_id,$asset){
        $ci =& get_instance();
        $post_data = array(
            'id' => 0,
            'method' => 'balance.query',//查询资产余额
            'params' => array(
                (int)$user_id,$asset
            ),
        );
        $url = $ci->config->item('VIABTC_API_URL');
        $post_data = json_encode($post_data);
        // var_dump($post_data);die;
        $ch = curl_init();
        // 设置请求为post类型
        curl_setopt($ch, CURLOPT_POST, 1);
        // 添加post数据到请求中
        // var_dump($post_data);die;
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        // 2. 设置请求选项, 包括具体的url
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        // 3. 执行一个cURL会话并且获取相关回复
        $response = curl_exec($ch);
        // var_dump($response);
        // 4. 释放cURL句柄,关闭一个cURL会话
        curl_close($ch);
        $response = object_to_array(json_decode($response));
        return $response;
    }




}
