<?php


namespace common\models;

use Yii;

class YouDaoApi extends \yii\db\ActiveRecord
{


    public function startRequest($q)
    {
        $url = 'https://openapi.youdao.com/api';

        $salt = $this->create_guid();
        $args = array(
            'q' => $q,
            'appKey' => Yii::$app->params['YouDaoAppKey'],
            'salt' => $salt,
        );
        $args['from'] = 'zh-CHS';
        $args['to'] = 'en';
        $args['signType'] = 'v3';
//        $args['ext'] = 'mp3';
        $curtime = strtotime("now");

        $args['curtime'] = $curtime;
        $signStr = Yii::$app->params['YouDaoAppKey'] . $this->truncate($q) . $salt . $curtime . Yii::$app->params['YouDaoSecKey'];
        $args['sign'] = hash("sha256", $signStr);

        $ret = $this->call($url, $args);
        return $ret;
    }

    // 发起网络请求
    public function call($url, $args = null, $method = "post", $testflag = 0, $timeout = 2000, $headers = array())
    {
        $ret = false;
        $i = 0;
        while ($ret === false) {
            if ($i > 1)
                break;
            if ($i > 0) {
                sleep(1);
            }

            $ret = $this->callOnce($url, $args, $method, false, $timeout, $headers);
            $i++;
        }
        return $ret;
    }

    public function callOnce($url, $args = null, $method = "post", $withCookie = false, $timeout = 2000, $headers = array())
    {
        $ch = curl_init();

        if ($method == "post") {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($args));
            curl_setopt($ch, CURLOPT_POST, 1);

        } else {
            $data = $this->convert($args);
            if ($data) {
                if (stripos($url, "?") > 0) {
                    $url .= "&$data";
                } else {
                    $url .= "?$data";
                }
            }
        }

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if (empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        }

        if ($withCookie) {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $_COOKIE);
        }
        $r = curl_exec($ch);
        curl_close($ch);

        return $r;
    }

    public function convert(&$args)
    {
        $data = '';
        if (is_array($args)) {
            foreach ($args as $key => $val) {
                if (is_array($val)) {
                    foreach ($val as $k => $v) {
                        $data .= $key . '[' . $k . ']=' . rawurlencode($v) . '&';
                    }
                } else {
                    $data .= "$key=" . rawurlencode($val) . "&";
                }
            }
            return trim($data, "&");
        }
        return $args;
    }

    // uuid generator
    public function create_guid()
    {
        $microTime = microtime();
        list($a_dec, $a_sec) = explode(" ", $microTime);
        $dec_hex = dechex($a_dec * 1000000);
        $sec_hex = dechex($a_sec);
        $this->ensure_length($dec_hex, 5);
        $this->ensure_length($sec_hex, 6);
        $guid = "";
        $guid .= $dec_hex;
        $guid .= $this->create_guid_section(3);
        $guid .= '-';
        $guid .= $this->create_guid_section(4);
        $guid .= '-';
        $guid .= $this->create_guid_section(4);
        $guid .= '-';
        $guid .= $this->create_guid_section(4);
        $guid .= '-';
        $guid .= $sec_hex;
        $guid .= $this->create_guid_section(6);
        return $guid;
    }

    public function create_guid_section($characters)
    {
        $return = "";
        for ($i = 0; $i < $characters; $i++) {
            $return .= dechex(mt_rand(0, 15));
        }
        return $return;
    }

    public function truncate($q)
    {
        $len = $this->abslength($q);
        return $len <= 20 ? $q : (mb_substr($q, 0, 10) . $len . mb_substr($q, $len - 10, $len));
    }

    public function abslength($str)
    {
        if (empty($str)) {
            return 0;
        }
        if (function_exists('mb_strlen')) {
            return mb_strlen($str, 'utf-8');
        } else {
            preg_match_all("/./u", $str, $ar);
            return count($ar[0]);
        }
    }

    public function ensure_length(&$string, $length)
    {
        $strlen = strlen($string);
        if ($strlen < $length) {
            $string = str_pad($string, $length, "0");
        } else if ($strlen > $length) {
            $string = substr($string, 0, $length);
        }
    }
}