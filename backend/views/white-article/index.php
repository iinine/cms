<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\WhiteArticleSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '白帽文章';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="white-article-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('创建文章', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'id',
            'title',
            [
                'label' => '文章类型',
                'attribute' => 'type',
                'filter' => \common\models\WhiteArticle::getType(),
                'filterInputOptions' => ['prompt' => '所有类型', 'class' => 'form-control', 'id' => null],
                'content' => function ($model, $key, $index, $column) {
                    return \common\models\WhiteArticle::getType($model->type);
                }
            ],
            [
                'label' => '文章状态',
                'attribute' => 'status',
                'filter' => \common\models\WhiteArticle::getStatus(),
                'filterInputOptions' => ['prompt' => '所有状态', 'class' => 'form-control', 'id' => null,'value'=>'all'],
                'content' => function ($model, $key, $index, $column) {
                    return \common\models\WhiteArticle::getStatus($model->status);
                }
            ],
            [
                'label' => '发布历史　　　　',
                'attribute' => 'history',
                'content' => function ($model, $key, $index, $column) {
                    if (!empty($model->history)) {
                        $history = json_decode($model->history, true);
                        return 'DB: ' . $history['databases'];
                    } else {
                        return '';
                    }
                }
            ],
            'keywords',
            'db_name',
            //'cut_word',
            //'image_urls:ntext',
            //'from_path',
            'word_count',
            //'part_content:ntext',
            //'content:ntext',
            'created_at',
            //'updated_at',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
