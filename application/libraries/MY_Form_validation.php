<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class MY_Form_validation
 * 自定义表单验证类
 */
class MY_Form_validation extends CI_Form_validation {

    function valid_url($url) {
        if(preg_match("/^http(|s):\/{2}(.*)\.([a-z]){2,}(|\/)(.*)$/i", $url)) {
            if(filter_var($url, FILTER_VALIDATE_URL)) return TRUE;
        }else{
            echo 'url格式不合法';
        }
        $this->CI->form_validation->set_message('valid_url', 'The %s must be a valid URL.');
    }
}