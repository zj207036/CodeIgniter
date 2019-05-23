<?php
/**
 * Created by PhpStorm.
 * User: zuojun
 * Date: 2019-04-26
 * Time: 11:11
 */

class jwt_helper extends CI_Controller
{
    const CONSUMER_KEY = 'YOUR_KEY'; // please replace YOUR_XX  YOUR_KEY
    const CONSUMER_SECRET = 'YOUR_SECRET'; // please replace YOUR_XX  YOUR_SECRET
    const CONSUMER_TTL = 86400; // second | 1 day

    // create token
    public static function create($userid)
    {
        $CI =& get_instance();
        $CI->load->library('JWT');
        $token = $CI->jwt->encode(array(
            'consumerKey' => self::CONSUMER_KEY,
            'userId' => $userid,
            'issuedAt' => date(DATE_ISO8601, strtotime("now")),
            'ttl' => self::CONSUMER_TTL
        ), self::CONSUMER_SECRET);
        return $token;
    }

    // validate token
    public static function validate($token)
    {
        $CI =& get_instance();
        $CI->load->library('JWT');
        try {
            $decodeToken = $CI->jwt->decode($token, self::CONSUMER_SECRET);
            // validate token is not expired
            $ttl_time = strtotime($decodeToken->issuedAt);
            $now_time = strtotime(date(DATE_ISO8601, strtotime("now")));
            if(($now_time - $ttl_time) > $decodeToken->ttl) {
                throw new Exception('Expired');
            } else {
                return true;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    // decode token
    public static function decode($token)
    {
        $CI =& get_instance();
        $CI->load->library('JWT');
        try {
            $decodeToken = $CI->jwt->decode($token, self::CONSUMER_SECRET);
            return $decodeToken;
        } catch (Exception $e) {
            return false;
        }
    }
}