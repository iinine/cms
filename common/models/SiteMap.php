<?php

namespace common\models;

use common\models\Domain;

use Yii;


/**
 * This is the model class for table "site_map".
 *
 * @property int $id
 * @property int|null $domain_id
 * @property int|null $type 10=PC 20=移动
 * @property string|null $file_name 文件名称
 * @property int|null $last_id 生成文件名称最后一位数字
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class SiteMap extends Base
{
    const TYPE_PC_XML = 10;
    const TYPE_M_XML = 20;
    const TYPE_PC_TXT = 30;
    const TYPE_M_TXT = 40;

    const MAX_NUM = 40000;

    public static function getType($key = 'all')
    {
        $data = [
            self::TYPE_PC_XML => 'PC_XML',
            self::TYPE_M_XML => 'M_XML',
            self::TYPE_PC_TXT => 'PC_TXT',
            self::TYPE_M_TXT => 'M_TXT',
        ];
        return $key === 'all' ? $data : $data[$key];
    }

    /** 获取文件名后缀 */
    public static function getExt($type)
    {
        $ext = '';
        if ($type == self::TYPE_PC_XML) {
            $ext = '_pc.xml';
        } elseif ($type == self::TYPE_M_XML) {
            $ext = '_m.xml';
        } elseif ($type == self::TYPE_PC_TXT) {
            $ext = '_pc.txt';
        } elseif ($type == self::TYPE_M_TXT) {
            $ext = '_m.txt';
        }
        return $ext;
    }

    /** 获取域名PC或者移动端 */
    public static function domainExt($type)
    {
        $ext = '';
        if ($type == self::TYPE_PC_XML) {
            $ext = 'www.';
        } elseif ($type == self::TYPE_M_XML) {
            $ext = 'm.';
        } elseif ($type == self::TYPE_PC_TXT) {
            $ext = 'www.';
        } elseif ($type == self::TYPE_M_TXT) {
            $ext = 'm.';
        }
        return $ext;
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'site_map';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['domain_id', 'type', 'last_id', 'last_url_id', 'number', 'start_url_id', 'update_start_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['file_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'domain_id' => 'Domain ID',
            'type' => 'Type',
            'file_name' => 'File Name',
            'last_id' => 'Last ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /** 创建一个网址地图 */
    public static function createOne($data)
    {
        $model = new SiteMap();
        $model->domain_id = $data['domain_id'];
        $model->type = $data['type'];
        $model->file_name = $data['file_name'];
        $model->last_url_id = $data['last_url_id'];
        $model->update_start_id = $data['update_start_id'];
        $model->start_url_id = $data['start_url_id'];
        $model->number = $data['number'];
        $model->last_id = $data['last_id'];
        $model->created_at = date('Y-m-d H:i:s');

        if (!$model->save(false)) {
            return [-1, $model->getErrors()];
        }
    }

    /** 根据域名获取新生成的网站地图名称 */
    public static function getFileName($domain, $type, $add = 0)
    {
        $siteMap = self::find()->where([
            'domain_id' => $domain->id,
            'type' => $type
        ])->orderBy('last_id desc')->one();

        if (empty($siteMap)) {
            return [$domain->id . '_site_1' . self::getExt($type), 0, 0];
        } else {
            if ($add == 1) {
                $lastUrlId = $siteMap->last_id + 1;
            } else {
                $lastUrlId = $siteMap->last_id;
            }
            return [$domain->id . '_site_' . $lastUrlId . self::getExt($type), $siteMap->last_url_id, $siteMap];
        }
    }

    /** 更新所有的网站地图 */
    public static function setAllSiteMap()
    {
        $domains = Domain::find()->all();
        foreach ($domains as $key => $domain) {
            self::setMap($domain, self::TYPE_PC_XML);
            echo ($key + 1) . '  ' . $domain->name . '  TYPE_PC_XML 更新完成' . PHP_EOL;
            self::setMap($domain, self::TYPE_M_XML);
            echo $domain->name . '  TYPE_M_XML 更新完成' . PHP_EOL;
            self::setMap($domain, self::TYPE_PC_TXT);
            echo $domain->name . '  TYPE_PC_TXT 更新完成' . PHP_EOL;
            self::setMap($domain, self::TYPE_M_TXT);
            echo $domain->name . '  TYPE_M_TXT 更新完成' . PHP_EOL;
        }
        exit();
    }

    //跳转到最后的一个更新地址
    public static function jumpUrl($domain, $name)
    {
        //查询最后的 文件数
        $str = "http://{$domain}/{$name}";
        header("location:{$str}");
        exit();
    }

    /** 设置最新的网站地图 */
    public static function setMap($domain = 0, $type = 40)
    {
        $max = self::MAX_NUM;      //单个文件最大的承载量

        //兼容批量生成xml模块
        if (!empty($domain)) {
            $domainModel = $domain;
            $_GET['domain'] = 0;
            $query = PushArticle::findx($domainModel->id);
            $jump = 0;
        } else {
            $jump = 1;
            $query = PushArticle::find();
            $domainModel = Domain::getDomainInfo();
        }

        list($fileName, $lastUrlId, $siteMap) = self::getFileName($domainModel, $type);
        $path = __DIR__ . '/../../frontend/web/';

        //网站距离上一次的 更新量
        $articles = $query->select('id,column_name,key_id,user_id')
            ->where(['>=', 'id', $lastUrlId])
            ->limit(40000)
            ->orderBy('id desc')
            ->asArray()
            ->all();

//        self::dd($articles);

        $articlesNum = count($articles);

        //考虑到标签以及用户含量 所以文章 url数量 x 3 取近似值
        $articleCount = $articlesNum * 3;

        //当不是更新时 直接跳转到最新一个地图
        if ($siteMap && empty(\Yii::$app->request->get('update', 0)) && $jump) {
            self::jumpUrl($domainModel->name, $siteMap->file_name);
        }

        //当有网站地图时 判断数量
        if ($siteMap && $siteMap->number <= $max) {
            $remain = $max - $siteMap->number;
            if ($remain >= $articleCount) { //当剩余容量大于 更新量时 则重新写入该文件
                //网站距离上一次的 更新量
                $articlesTotal = $query->select('id,column_name,key_id,user_id')
                    ->where(['>=', 'id', $siteMap->start_url_id])
                    ->where(['<=', 'id', $articles[0]['id']])
                    ->limit(40000)
                    ->orderBy('id desc')
                    ->asArray()
                    ->all();

                if ($type == self::TYPE_PC_XML || $type == self::TYPE_M_XML) {
                    list($data, $urlNum) = self::setXml($articlesTotal, $domainModel, $type);
                } else {
                    list($data, $urlNum) = self::setTxt($articlesTotal, $domainModel, $type);
                }

                list($fileName, $lastId) = self::getFileName($domainModel, $type);
                file_put_contents($siteMap->file_name, $data);

                //更新
                $siteMap->number = $urlNum;
                $siteMap->update_start_id = $siteMap->last_url_id;  //上次的最后id
                $siteMap->last_url_id = $articles[0]['id'];
                $siteMap->save(false);
                if ($jump) {
                    self::jumpUrl($domainModel->name, $siteMap->file_name);
                }
            } else {  //剩余容量小于时，则写入新的文件
                if ($type == self::TYPE_PC_XML || $type == self::TYPE_M_XML) {
                    list($data, $urlNum) = self::setXml($articles, $domainModel, $type);
                } else {
                    list($data, $urlNum) = self::setTxt($articles, $domainModel, $type);
                }
                list($fileName, $lastId) = self::getFileName($domainModel, $type, 1);
                file_put_contents($path . $fileName, $data);
                $dataSave = [
                    'domain_id' => $domainModel->id,
                    'type' => $type,
                    'file_name' => $fileName,
                    'start_url_id' => $articles[$articlesNum - 1]['id'],
                    'update_start_id' => $articles[$articlesNum - 1]['id'],
                    'number' => $urlNum,
                    'last_url_id' => $articles[0]['id'],
                    'last_id' => $lastId,
                ];
                self::createOne($dataSave);
                if ($jump) {
                    self::jumpUrl($domainModel->name, $siteMap->file_name);
                }
            }
        } else { //没有网站地图时 则新建网站地图
            if ($type == self::TYPE_PC_XML || $type == self::TYPE_M_XML) {
                list($data, $urlNum) = self::setXml($articles, $domainModel, $type);
            } else {
                list($data, $urlNum) = self::setTxt($articles, $domainModel, $type);
            }
            list($fileName, $lastId) = self::getFileName($domainModel, $type, 1);
            file_put_contents($path . $fileName, $data);
            $dataSave = [
                'domain_id' => $domainModel->id,
                'type' => $type,
                'file_name' => $fileName,
                'start_url_id' => $articles[$articlesNum - 1]['id'],
                'update_start_id' => $articles[$articlesNum - 1]['id'],
                'number' => $urlNum,
                'last_url_id' => $articles[0]['id'],
                'last_id' => $lastId,
            ];
            self::createOne($dataSave);
            if ($jump) {
                self::jumpUrl($domainModel->name, $siteMap->file_name);
            }
        }
    }


    public static function setTxt($articles, $domainModel, $type)
    {
        $data = [];
        foreach ($articles as $article) {
            //会员网址
            $data[] = 'https://' . self::domainExt($type) . $domainModel->name . '/user/index_' . $article['user_id'] . '.html';
            //文章网址
            $data[] = 'https://' . self::domainExt($type) . $domainModel->name . '/' . $article['column_name'] . '/' . $article['id'] . '.html';
            //标签网址
            $data[] = 'https://' . self::domainExt($type) . $domainModel->name . '/' . $domainModel->start_tags . $article['key_id'] . $domainModel->end_tags;
        }
        $data = array_unique($data);
        return [implode(PHP_EOL, $data), count($data)];
    }

    public static function setXml($articles, $domainModel, $type)
    {

        if ($type == self::TYPE_PC_XML) {
            $data = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.google.com/schemas/sitemap/0.84">';
        } else {
            $data = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:mobile="http://www.baidu.com/schemas/sitemap-mobile/1/">';
        }

        $users = [];
        $number = 0;
        foreach ($articles as $key => $article) {
            if (!in_array($article['user_id'], $users)) {  //判重
                //用户网址
                $urlUser = $urlPc = 'https://' . self::domainExt($type) . $domainModel->name . '/user/index_' . $article['user_id'] . '.html';
                $data .= '
                    <url>
                    <loc>' . $urlUser . '</loc>
                    <lastmod>' . $article['push_time'] . '</lastmod>
                    <changefreq>daily</changefreq>
                    <priority>1.0</priority>
                    </url>
                    ';
                $number += 1;
            }
            $users[] = $article['user_id'];
            $number += 2;

            //文章网址
            $urlPc = 'https://' . self::domainExt($type) . $domainModel->name . '/' . $article['column_name'] . '/' . $article['id'] . '.html';
            //标签网址
            $tagPc = 'https://' . self::domainExt($type) . $domainModel->name . '/' . $domainModel->start_tags . $article['key_id'] . $domainModel->end_tags;

            $data .= '
                    <url>
                    <loc>' . $urlPc . '</loc>
                    <lastmod>' . $article['push_time'] . '</lastmod>
                    <changefreq>daily</changefreq>
                    <priority>1.0</priority>
                    </url>
                    ';

            $data .= '
                    <url>
                    <loc>' . $tagPc . '</loc>
                    <lastmod>' . $article['push_time'] . '</lastmod>
                    <changefreq>daily</changefreq>
                    <priority>1.0</priority>
                    </url>
                    ';

        }

        $data .= '
                    </urlset>';

        return [$data, $number];

    }
}