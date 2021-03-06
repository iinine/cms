<?php


namespace common\models;

use yii\web\UploadedFile;

/** 发布到cms系统 */
class Publish extends \yii\db\ActiveRecord
{

    /** 发布白帽文章到cms */
    public static function pushArticle($data)
    {
        $dbName = DbName::find()->where(['id' => $data['db_id']])->one();
        $data['db_name'] = $dbName->name;
        $data['db_tags_id'] = json_encode($data['db_tags_id']);
        $data['db_tags_name'] = json_encode($data['db_tags_name']);
        $data['host_name'] = str_replace('m.', 'https://www.', $dbName->domain);

        //异步发送请求保存数据到CMS数据库
        $url = 'http://' . $_SERVER['SERVER_ADDR'] . ':89/index.php?r=cms/set-article';
        $arr[] = $url;
        $res = Tools::curlPost($url, $data);
        if (strpos($res, 'success') === false) { //表示没有成功，则打印错误
            echo '<pre>';
            print_r($res);
            exit;
        }
    }

    /** 发布黑帽文章 */
    public static function pushBlackArticle($data)
    {
        $dbName = DbName::find()->where(['id' => $data['db_id']])->one();
        $data['db_name'] = $dbName->name;
        $data['db_tags_id'] = json_encode($data['db_tags_id']);
        $data['db_tags_name'] = json_encode($data['db_tags_name']);
        $data['host_name'] = str_replace('m.', 'https://www.', $dbName->domain);

        if (isset($data['flag'])) { //表示本地执行，直接填写线上ip
            //异步发送请求保存数据到CMS数据库
            $url = 'http://116.193.169.122:89/index.php?r=cms/set-article';
        } else {
            //异步发送请求保存数据到CMS数据库
            $url = 'http://' . $_SERVER['SERVER_ADDR'] . ':89/index.php?r=cms/set-article';
        }

        $arr[] = $url;
        $res = Tools::curlPost($url, $data);
        if (strpos($res, 'success') !== false) { //表示没有成功，则打印错误
            return [1, 'success'];
        } else {
            return [-1, $res];
        }
    }
}