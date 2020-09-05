<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "long_keywords".
 *
 * @property int $id
 * @property string|null $m_down_name 移动端下拉词
 * @property string|null $m_search_name 移动端其他人还在搜
 * @property string|null $name 关键词名称
 * @property string|null $type 类型
 * @property string|null $m_related_name 相关搜索
 * @property string|null $m_other_name 移动变种下拉词
 * @property string|null $pc_down_name PC端下拉词
 * @property string|null $pc_search_name PC端其他人搜索
 * @property string|null $pc_related_name PC相关词
 * @property string|null $keywords 短尾词
 * @property int|null $key_id 短尾词id
 * @property int|null $key_search_num 短尾词搜索次数
 * @property int|null $status 1=有效 0=无效
 * @property string|null $remark 备注
 * @property int|null $from 1=百度  2=搜狗
 * @property string|null $url 来源地址
 * @property string|null $created_at 创建时间
 * @property string|null $updated_at 修改时间
 */
class LongKeywords extends Base
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'long_keywords';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['key_id', 'key_search_num', 'status', 'from', 'type'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['m_down_name', 'm_search_name', 'm_related_name', 'name', 'pc_down_name', 'm_other_name', 'pc_search_name', 'pc_related_name', 'keywords', 'remark', 'url'], 'string', 'max' => 255],
        ];
    }

    const TYPE_M_DOWN = 10;      //移动下拉词
    const TYPE_M_SEARCH = 20;    //移动其他人搜索词
    const TYPE_M_RELATED = 30;   //移动相关搜索
    const TYPE_M_OTHER = 40;     //移动端变种下拉词
    const TYPE_PC_SEARCH = 50;   //PC其他人搜索词
    const TYPE_PC_RELATED = 60;  //PC相关搜索词

    /**
     * @param string $key
     * @return string|string[]
     * 获取所有的类型
     */
    public static function getType($key = 'all')
    {
        $data = [
            self::TYPE_M_DOWN => '移动下拉词',
            self::TYPE_M_SEARCH => '移动其他人搜索词',
            self::TYPE_M_RELATED => '移动相关搜索',
            self::TYPE_M_OTHER => '移动端变种下拉词',
            self::TYPE_PC_SEARCH => 'PC其他人搜索词',
            self::TYPE_PC_RELATED => 'PC相关搜索词',
        ];
        return $key === 'all' ? $data : $data[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '关键词名称',
            'type' => '类型',
            'm_down_name' => '移动端下拉词',
            'm_search_name' => '移动端其他人搜索',
            'm_related_name' => '相关搜索',
            'm_other_name' => '移动端变种下拉词',
            'pc_down_name' => 'PC端下拉词',
            'pc_search_name' => 'PC端其他人搜索',
            'pc_related_name' => 'PC相关词',
            'keywords' => '短尾词',
            'key_id' => '短尾词id',
            'key_search_num' => '短尾词搜索次数',
            'status' => '状态',
            'remark' => '备注',
            'from' => '来源地址',
            'url' => 'Url',
            'created_at' => '创建时间',
            'updated_at' => '修改时间',
        ];
    }

    /** 标签 */
    public static function hotKeywords()
    {
        $keywords = LongKeywords::find()
            ->select('id,name as keywords')
            ->limit(15)
            ->orderBy('id desc')
            ->asArray()
            ->all();

        $domain = Domain::getDomainInfo();

        if ($domain) {
            foreach ($keywords as &$item) {
                $item['url'] = '/' . $domain->start_tags . $item['id'] . $domain->end_tags;
            }
        }
        return $keywords;
    }

    /**
     * @param $data
     * @return array
     * 创建一个长尾关键词
     */
    public static function createOne($data)
    {
        //判重 不可有所有重复的关键词
        $oldInfo = self::find()->where(['name' => $data['name']])->one();

        if (!empty($oldInfo)) {
            return [-1, $data['name'] . '   已经重复了'];
        }

        $model = new LongKeywords();

        $model->name = $data['name'];
        $model->type = $data['type'];
        $model->type_name = $data['type_name'];
        $model->m_down_name = $data['m_down_name'] ?? '';
        $model->m_search_name = $data['m_search_name'] ?? '';
        $model->m_related_name = $data['m_related_name'] ?? '';
        $model->m_other_name = $data['m_other_name'] ?? '';
        $model->pc_search_name = $data['pc_search_name'] ?? '';
        $model->pc_related_name = $data['pc_related_name'] ?? '';
        $model->key_search_num = $data['key_search_num'] ?? 0;
        $model->keywords = $data['keywords'];
        $model->key_id = $data['key_id'];
        $model->status = $data['status'] ?? 1;
        $model->remark = $data['remark'] ?? '';
        $model->from = $data['from'] ?? '';
        $model->url = $data['url'] ?? '';
        $model->created_at = date('Y-m-d H:i:s');

        if (!$model->save(false)) {
            return [-1, $model->getErrors()];
        } else {
            return [1, $model];
        }
    }

    /** 抓取百度的关键词 */
    public static function getKeywords()
    {
        set_time_limit(0);
        $redis = \Yii::$app->redis;
        $lastKeywords = $redis->get('last_keywords_id');

        if ($lastKeywords) {
            //获取所有的短尾关键词
            $keywords = BaiduKeywords::find()->select('id,keywords,m_pv')
                ->where(['>', 'id', $lastKeywords])
                ->limit(120)
                ->asArray()
                ->all();
        } else {
            //获取所有的短尾关键词
            $keywords = BaiduKeywords::find()->select('id,keywords,m_pv')
                ->where(['>', 'id', 1])
                ->limit(120)
                ->asArray()
                ->all();
        }

        $error = $sameArr = [];

        foreach ($keywords as $keyword) {
            //不可重复获取百度关键词
            $oldInfo = self::find()->select('keywords')->where(['keywords' => $keyword['keywords']])->one();
            if (empty($oldInfo)) {
                list($code, $msg) = self::getBaiduKey($keyword);
                if ($code < 0) {
                    $error[] = $msg;
                }

                sleep(10);
                if ($code == -10) {
                    sleep(20);
                }

                Tools::writeLog($keyword);
            } else {
                Tools::writeLog([$keyword, 'res' => '重复']);
                $sameArr[] = $keyword['keywords'] . '   重复！';
            }
        }

        echo '<pre>';
        print_r($sameArr);
        echo '<hr/>';
        print_r($error);
    }

    /**
     * 获取下拉词
     */
    public static function getBaiduKey($data)
    {
        //百度下拉框接口提取数据
        $url = 'http://m.baidu.com/sugrec?pre=1&p=3&ie=utf-8&json=1&prod=wise&from=wise_web&net=&os=&sp=&callback=jsonp&wd=' . $data['keywords'];
        $url2 = 'http://m.baidu.com/sugrec?pre=1&p=20&ie=utf-8&json=1&prod=wise&from=wise_web&net=&os=&sp=&callback=jsonp&wd=' . $data['keywords'];

        $resDown = Tools::curlGet($url);
        $resDown = str_replace('jsonp(', '', $resDown);
        $resDown = substr($resDown, 0, -1);

        $resDown = json_decode($resDown, true)['g'];

        if (!empty($resDown)) {
            $resDown = array_column($resDown, 'q');
        } else {
            return [-1, '下拉词没有抓取成功'];
        }

        $resOther = Tools::curlGet($url2);
        $resOther = str_replace('jsonp(', '', $resOther);
        $resOther = substr($resOther, 0, -1);
        $resOther = json_decode($resOther, true)['g'];
        if (!empty($resOther)) {
            $resOther = array_column($resOther, 'q');
        } else {
            $resOther = [];
//            return [-1, '变种下拉词没有抓取成功'];
        }

        //百度内容页提取数据
        $url3 = 'http://m.baidu.com/s?word=' . $data['keywords'];
        $res = Tools::curlGet($url3);

        if (strpos($res, '<a href="https://wappass.baidu.com/static/captcha/tuxing.html') !== false) {
            return [-10, '机器人验证不通过!'];
        }

        //清除多余字符串，加快正则匹配速度
        $res = substr($res, 397089);
        $res = substr($res, 0, -205027);

        //大家都在搜关键词 get
        preg_match('@哪些联想词属于不当内容(.*)? data-action-proxy="true@', $res, $reData);
        $arr = explode('&quot;text&quot;:&quot;', $reData[0]);
        $resSearch = [];
        $arr = array_slice($arr, 1, 6);
        foreach ($arr as $key => $item) {
            $resSearch[] = preg_replace('@&quot(.*)?@', '', $item);
        }

        //相关搜索词 get
        preg_match('@target="_self" data-visited="off" rl-node="" rl-highlight-color="rgba\(0, 0, 0, .08\)" rl-highlight-radius="5px" class="c-slink c-slink-new-strong c-gap-top-small c-gap-bottom-small c-gap-top-small(.*)?</span><!----><!----></a></div></div><!----></div>@', $res, $reData);
        $arr2 = explode('><!----><', $reData[1]);
        $resRelated = [];
        foreach ($arr2 as $key => $item) {
            if (($key + 1) % 2 == 0) {
                $value = substr($item, strpos($item, ">"));
                $value = str_replace(['</span', '>'], ['', ''], $value);
                $resRelated[] = $value;
            }
        }

        $error = [];

        $allKeyWords = [
            10 => $resDown,
            20 => $resSearch,
            30 => $resRelated,
            40 => $resOther,
        ];

        foreach ($allKeyWords as $key => $item) {
            foreach ($item as $k => $value) {
                $dataSave = [
                    'name' => $value,
                    'key_id' => $data['id'],
                    'type' => $key,
                    'keywords' => $data['keywords'],
                ];

                list($code, $msg) = self::createOne($dataSave);

                if ($code < 0) {
                    $error[] = $msg;
                }
            }
        }

        $keywords = self::find()->where(['key_id' => $data['id']])->all();
        $resOther = $resRelated = $resSearch = $resDown = [];
        foreach ($keywords as $keyword) {
            if ($keyword->type == self::TYPE_M_DOWN) {
                $resDown[] = [
                    'id' => $keyword->id,
                    'name' => $keyword->name
                ];
            } elseif ($keyword->type == self::TYPE_M_SEARCH) {
                $resSearch[] = [
                    'id' => $keyword->id,
                    'name' => $keyword->name
                ];
            } elseif ($keyword->type == self::TYPE_M_RELATED) {
                $resRelated[] = [
                    'id' => $keyword->id,
                    'name' => $keyword->name
                ];
            } elseif ($keyword->type == self::TYPE_M_OTHER) {
                $resOther[] = [
                    'id' => $keyword->id,
                    'name' => $keyword->name
                ];
            }
        }

        $mDownStr = json_encode($resDown, JSON_UNESCAPED_UNICODE);

        $mOtherStr = json_encode($resOther, JSON_UNESCAPED_UNICODE);

        $mRelatedStr = json_encode($resRelated, JSON_UNESCAPED_UNICODE);

        $mSearchStr = json_encode($resSearch, JSON_UNESCAPED_UNICODE);

        $dataSave = [
            'm_related_name' => $mRelatedStr,
            'm_search_name' => $mSearchStr,
            'm_down_name' => $mDownStr,
            'm_other_name' => $mOtherStr,
        ];

        self::updateAll($dataSave, ['key_id' => $data['id']]);
        $msg = self::find()->where(['key_id' => $data['id']])->one();

        self::pushReptile($msg);

        echo '<pre>';
        print_r($error);
    }

    /** 将新增的关键词推入到远程爬虫 */
    public static function pushReptile($data = [])
    {
        $url = Tools::reptileUrl() . '/keyword/save-keyword';
        //查询域名 栏目
        $info = BaiduKeywords::find()->select('id,domain_id,column_id')->where(['id' => $data->key_id])->one();
        if ($info) {
            //查询类型
            $column = DomainColumn::findOne($info->column_id);
            if ($column) {
                $dataPush = [
                    'note' => $data->keywords,
                    'fan_key_id' => $info->id,
                    'key_id' => $data->id,
                    'type' => $column->type,
                    'keywords' => $data->name,
                    'm_related_name' => $data->m_related_name,
                    'm_search_name' => $data->m_search_name,
                    'm_down_name' => $data->m_down_name,
                    'm_other_name' => $data->m_other_name,
                    'domain_id' => $info->domain_id,
                    'column_id' => $info->column_id,
                ];

                $res = Tools::curlPost($url, $dataPush);
                if (strpos($res, 'success') === false) {
                    print_r($res);
                    exit;
                    Tools::writeLog($res, 'reptile_keywords_error.log');
                } else {
                    Tools::writeLog([$data->name => '保存成功'], 'reptile_keywords.log');
                }
            }
        }
    }

    /** 将新增的关键词推入到远程爬虫 */
    public static function bdPushReptile($data = [], $id)
    {
        $url = Tools::reptileUrl() . '/keyword/save-keyword';
        //  $url = \Yii::$app->params['online_reptile_url'] . '/keyword/save-keyword';

        $resOther[] = [
            'id' => $data->id,
            'name' => $data->name
        ];

        $mOtherStr = json_encode($resOther, JSON_UNESCAPED_UNICODE);

        $dataPush = [
            'note' => $data->keywords,
            'fan_key_id' => $id,
            'key_id' => $data->id,
            'type' => $data->type_name,
            'keywords' => $data->name,
            'm_related_name' => '[]',
            'm_search_name' => '[]',
            'm_down_name' => '[]',
            'm_other_name' => $mOtherStr,
            'domain_id' => 0,
            'column_id' => 0,
        ];

        $res = Tools::curlPost($url, $dataPush);
        if (strpos($res, 'success') === false) {
            print_r($res);
            exit;
            Tools::writeLog($res, 'reptile_keywords_error.log');
        } else {
            Tools::writeLog([$data->name => '保存成功'], 'reptile_keywords.log');
        }
    }

    /**
     * 设定规则 定时拉取文章
     */
    public static function setRules()
    {
        set_time_limit(0);

        //查询所有栏目
        $domainColumn = DomainColumn::find()->select('id,type,domain_id,zh_name,name')->where([
            'status' => self::STATUS_BASE_NORMAL,
        ])->asArray()->all();

        $url = Tools::reptileUrl() . '/cms/article';


        foreach ($domainColumn as $column) {
            //查询分类规则
            $rules = ArticleRules::find()->where([
                'column_id' => $column['id']
            ])->asArray()->one();

            //只拉取有规则的
            if (!empty($rules)) {
                //根据短尾关键词 获取长尾关键词 小指数词
                $longKeywords = AllBaiduKeywords::find()->select('id,keywords as name,pid')->where([
                    'like', 'keywords', $column['zh_name'],
                ])
                    ->andWhere(['>=', 'm_pv', 1])
                    ->andWhere(['<=', 'm_pv', 10])
                    ->andWhere(['type' => $column['type']])
                    ->andWhere(['column_id' => null])
                    ->limit(1000)
                    ->asArray()
                    ->all();

                echo '<pre>';
                print_r($longKeywords);exit;

                foreach ($longKeywords as $key => $longKeyword) {
                    //检验是否拉取过数据
                    $oldArticleKey = PushArticle::findx($column['domain_id'])->where(['key_id' => $longKeyword['id']])->one();

                    if (!empty($oldArticleKey)) {
                        Tools::writeLog($column['zh_name'] . ' ---  ' . $longKeyword['name'] . '  长尾词已经拉取过了', 'set_rules.log');
                        continue;
                    }

                    echo $longKeyword['name'] . "<br/>";

                    //根据长尾关键词以及规则 从爬虫库拉取文章数据 保存到相应的文章表中
                    $data = [
                        'key_id' => $longKeyword['id'],
                        'keywords' => $longKeyword['name'],
                        'type' => strtoupper($column['type']),
                        'one_page_num_min' => $rules['one_page_num_min'],
                        'one_page_num_max' => $rules['one_page_num_max'],
                        'one_page_word_min' => $rules['one_page_word_min'],
                        'one_page_word_max' => $rules['one_page_word_max'],
                    ];

                    $bd = AllBaiduKeywords::findOne($longKeyword['id']);
                    $bd->domain_id = $column['domain_id'];
                    $bd->column_id = $column['id'];
                    $bd->save();

                    //发送请求至爬虫库
                    $res = Tools::curlPost($url, $data);

                    $res = json_decode($res, true);
                    $saveData = [];
                    if (isset($res[0]['from_path'])) {
                        foreach ($res as $re) {
                            //存储入库
                            $saveData[] = [
                                'column_id' => $column['id'],
                                'from_path' => $re['from_path'],
                                'domain_id' => $column['domain_id'],
                                'key_id' => $longKeyword['id'],
                                'title_img' => $re['title_img'],
                                'keywords' => $longKeyword['name'],
                                'column_name' => $column['name'],
                                'fan_key_id' => $longKeyword['pid'],
                                'rules_id' => $rules['id'],
                                'content' => $re['content'],
                                'intro' => $re['intro'],
                                'title' => $re['title'],
                                'user_id' => rand(1, 3822),
                                'push_time' => Tools::randomDate('20200501', ''),
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s'),
                            ];
                        }
                    }
//
                    if (!empty($saveData)) {
                        Tools::writeLog('保存' . $longKeyword['name'], 'set_rules.log');
                        PushArticle::batchInsertOnDuplicatex($column['domain_id'], $saveData);
//                        sleep(1);
                    } else {
                        Tools::writeLog('保存' . $longKeyword['name'], 'set_rules.log');
//                            echo '<pre>';
//                            var_export($data);
//                            exit;
                    }
                }
//                        sleep(5);
            }
        }
        exit;
    }
}
