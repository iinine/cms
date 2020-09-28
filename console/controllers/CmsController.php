<?php

namespace console\controllers;

use common\models\AllBaiduKeywords;
use common\models\BaiduKeywords;
use common\models\BlackArticle;
use common\models\DbName;
use common\models\FanUser;
use common\models\LongKeywords;
use common\models\MipFlag;
use common\models\SiteMap;
use common\models\Tools;
use common\models\ArticleRules;

class CmsController extends \yii\console\Controller
{
    /**
     *开始跑所有数据库
     * http://yii.com/index.php?r=cms/start-run
     */
    public function actionStartRun()
    {
        //每分钟检测执行一次
        $res = DbName::find()->all();
        $arr = [];
        //遍历每个数据库，推送
        foreach ($res as $re) {
            //定时不为空
            if (!empty($re->mip_time)) {
                $date = date('Y-m-d', time());
                $time = $date . ' ' . $re->mip_time . ':00';
                $limitTime = strtotime($time) - time();

                //当到达执行时间时，开始执行
                if ($limitTime < 90 && $limitTime > 0) { //表示执行
                    print_r($limitTime);
                    Tools::writeLog($re->name . '已执行');
                    $url = 'http://' . $_SERVER['SERVER_ADDR'] . ':89/index.php?r=cms&db_name=' . $re->name;
                    $arr[] = $url;
                    Tools::curlGet($url);
                } else {
                    echo '当前时间:' . time();
                    echo '  时间差:' . $limitTime;
                    echo PHP_EOL;
                    echo $re->name . '  执行时间：' . $time;
                    echo PHP_EOL;
                }
            }
        }
        print_r($arr);
    }

    /**
     * 设置标签页
     */
    public function actionSetTags()
    {
        //每天检测执行一次
        $res = DbName::find()->all();
        $arr = [];
        //遍历每个数据库，推送
        foreach ($res as $re) {
            $domain = str_replace('m.', '', $re->domain);
            $url = 'http://' . $_SERVER['SERVER_ADDR'] . ':89/index.php?r=cms/set-tags&db_name=' . $re->name . '&domain=' . $domain;
            $arr[] = $url;
            Tools::curlGet($url);
        }
        print_r($arr);
    }

    /**
     * 抓取百度关键词
     */
    public function actionCatchBd()
    {
        set_time_limit(0);
        (new BaiduKeywords())->getSdkWords();
    }

    /**
     * 抓取百度长尾词
     */
    public function actionCatchBaidu()
    {
        LongKeywords::pushReptile();
        LongKeywords::getKeywords();
    }

    /** 生成泛目录缓存 */
    public function actionCacheFan()
    {
        $url = 'https://www.ysjj.org.cn/?index.php&catch_web=1';
        Tools::curlGet($url);
    }

    /** 生成泛目录缓存 */
    public function actionPushFan()
    {
        $url = 'https://www.ysjj.org.cn/?index.php&push=1';
        Tools::curlGet($url);
    }

    /** 推送黑帽文章
     * cms/push-black-article
     */
    public function actionPushBlackArticle()
    {
        (new BlackArticle())->pushArticle();
    }

    public function actionCreateUser()
    {
        (new FanUser())->createMany();
    }

    public function actionPushK()
    {
        return BaiduKeywords::pushKeywords();
    }

    public function actionSetRules()
    {
        global $argv;
        $domainId = $argv[2] ?? '';
        LongKeywords::setRules($domainId);
    }


//    public function actionPushPa()
//    {
//        BaiduKeywords::pushPa();
//    }

    /** 设置链接 */
    public function actionSetUrl()
    {
        MipFlag::crontabSet();
    }

    /** 推送Mip */
    public function actionSetMip()
    {
        MipFlag::pushMip();
    }

    /** 推送Mip */
    public function actionSetMipM()
    {
        MipFlag::pushMipM();
    }

    public function actionTransA()
    {
        //翻译文章
        LongKeywords::rulesTrans();
    }

    public function actionSetList()
    {


//        exit;

        //查询指定20个站 的规则
        $domainIds = BaiduKeywords::getDomainIds();
        //查询出所有的规则分类
        $articleRules = ArticleRules::find()->select('category_id')->where(['in', 'domain_id', $domainIds])->asArray()->all();
        $itemData = [];

        $step = 50;
        for ($i = 0; $i <= 100; $i++) {
            foreach ($articleRules as $key => $rules) {
                $keywords = AllBaiduKeywords::find()
                    ->select('id,keywords,type')
                    ->where([
                        'column_id' => 0,
                        'status' => 10,
                        'type_id' => $rules['category_id']
                    ])
                    ->andWhere(['>', 'updated_at', '2020-09-26 10:00:00'])
                    ->andWhere([
                        'catch_status' => 100
                    ])
                    ->andWhere(['back_time' => null])
                    ->orderBy('id desc')
                    ->offset($i * $step)
                    ->limit($step)
                    ->asArray()
                    ->all();

                foreach ($keywords as $keyword) {
                    $data[] = [
                        'keyword' => $keyword['keywords'],
                        'key_id' => $keyword['id'],
                        'id' => 0,
                        'type' => $keyword['type'],
                    ];
                }
            }
        }

//        echo '<pre>';
//        print_r($data);
//        exit;

        $url = 'http://8.129.37.130/index.php/distribute/set-keyword';
        Tools::curlPost($url, ['res' => json_encode($data)]);
        exit;

//        echo '<pre>';
//        print_r($data);
//        exit;
//
//        $data = [];

//        $urlGet = 'http://8.129.37.130/index.php/distribute/set-keyword';
//
//        Tools::curlNewGet()
//        echo '<pre>';
//        print_r($data);exit;

        $url = 'http://8.129.37.130/index.php/distribute/set-keyword';
        $res = Tools::curlPost($url, ['res' => json_encode($data)]);
        print_r($res);
    }

    public function actionStartMap()
    {
        SiteMap::setAllSiteMap();
    }
}