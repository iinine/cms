<?php

namespace common\models;

use Yii;
use yii\db\Migration;

/**
 * This is the model class for table "push_article".
 *
 * @property int $id
 * @property int|null $b_id 索引黑帽文章id
 * @property int|null $column_id 类目id
 * @property string|null $column_name 类名
 * @property int|null $rules_id 规则id
 * @property int|null $domain_id 域名id
 * @property string|null $domain 域名
 * @property string|null $from_path 来路地址
 * @property string|null $keywords 关键词
 * @property string|null $title_img 标题图片地址
 * @property int|null $status 10=状态有效 20=无效
 * @property string|null $content 内容
 * @property string|null $intro 文章简介
 * @property string|null $title 标题
 * @property string|null $push_time 发布时间
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class PushArticle extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        //根据域名请求，判断该使用哪个表
        $domain = Domain::getDomainInfo();
        if ($domain) {
            return 'push_article_' . $domain->id;
        }
        return 'push_article';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['b_id', 'column_id', 'rules_id', 'domain_id', 'status'], 'integer'],
            [['content'], 'string'],
            [['push_time', 'created_at', 'updated_at'], 'safe'],
            [['column_name', 'domain', 'from_path', 'keywords', 'title_img', 'intro', 'title'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'b_id' => 'B ID',
            'column_id' => 'Column ID',
            'column_name' => 'Column Name',
            'rules_id' => 'Rules ID',
            'domain_id' => 'Domain ID',
            'domain' => 'Domain',
            'from_path' => 'From Path',
            'keywords' => 'Keywords',
            'title_img' => 'Title Img',
            'status' => 'Status',
            'content' => 'Content',
            'intro' => 'Intro',
            'title' => 'Title',
            'push_time' => 'Push Time',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /** 发布文章 */
    public static function createOne($data)
    {
        $model = new PushArticle();
        foreach ($data as $key => $item) {
            $model->$key = $item;
        }

        $model->created_at = date('Y-m-d H:i:s');
        if (!$model->save(false)) {
            return [-1, $model->getErrors()];
        } else {
            return [1, $model];
        }
    }

    /** 热门文章 */
    public static function hotArticle()
    {
        $article = PushArticle::find()
            ->select('id,title_img,push_time,title')
            ->limit(10)
            ->orderBy('title_img desc')
            ->asArray()
            ->all();

        foreach ($article as &$item) {
            $item['url'] = '/wen/' . $item['id'] . '.html';
        }

        return $article;
    }

    /** 获取用户名称 */
    public function getFanUser()
    {
        return $this->hasOne(FanUser::className(), ['id' => 'user_id']);
    }

    /** 最新文章 */
    public static function newArticle()
    {
        $article = PushArticle::find()
            ->select('id,title_img,push_time,title')
            ->limit(10)
            ->orderBy('id desc')
            ->asArray()
            ->all();
        foreach ($article as &$item) {
            $item['url'] = '/wen/' . $item['id'] . '.html';
        }
        return $article;
    }

    /** 创建新的表 */
    public static function createTable($id)
    {
        $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ENGINE=InnoDB';
        $migrate = new Migration();
        $migrate->createTable('{{%push_article_' . $id . '}}', [
            'id' => $migrate->primaryKey(),
            'b_id' => $migrate->integer(11)->defaultValue(0)->comment('索引黑帽文章id'),
            'column_id' => $migrate->integer(11)->defaultValue(0)->comment('类目id'),
            'column_name' => $migrate->string(255)->defaultValue('')->comment('类名'),
            'domain' => $migrate->string(25)->defaultValue('')->comment('域名'),
            'domain_id' => $migrate->integer(11)->defaultValue(0)->comment('域名id'),
            'from_path' => $migrate->string(255)->defaultValue('')->comment('来路地址'),
            'key_id' => $migrate->integer(11)->defaultValue(0)->comment('关键词id'),
            'keywords' => $migrate->string(30)->defaultValue('')->comment('关键词'),
            'rules_id' => $migrate->integer(11)->defaultValue(0)->comment('规则id'),
            'content' => $migrate->text()->comment('内容'),
            'title_img' => $migrate->integer(11)->defaultValue(0)->comment('关键词'),
            'status' => $migrate->smallInteger()->defaultValue(10)->comment('10=状态有效 20=无效'),
            'intro' => $migrate->string(255)->defaultValue('')->comment('文章简介'),
            'title' => $migrate->string(255)->defaultValue('')->comment('标题'),
            'push_time' => $migrate->dateTime()->comment('发布时间'),
            'created_at' => $migrate->dateTime()->comment('创建时间'),
            'updated_at' => $migrate->dateTime()->comment('修改时间'),
        ], $tableOptions);
        //关键字id索引
        $migrate->createIndex('key_id-index', 'push_article_' . $id, ['key_id'], false);
    }
}