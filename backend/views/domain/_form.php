<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Domain */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="domain-form">

    <?php $form = ActiveForm::begin(); ?>

    <?php

    if (strpos(Yii::$app->request->url, 'create') !== false) {
        $model->start_tags = \common\models\Tools::makeCode(3);
        $model->end_tags = \common\models\Tools::makeCode(2);
        $model->is_jump = 0;
        $model->jump_url = 'http://www.baidu.com';
    }

    echo $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'zh_name')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'intro')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'ip')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'start_tags')->textInput(['maxlength' => true])->hint('一旦创建请勿修改') ?>
    <?= $form->field($model, 'end_tags')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'baidu_token')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'baidu_password')->passwordInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'baidu_account')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'mip_time')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'links')->textarea(['rows' => 5])
        ->hint('实例： 百度|https://www.baodu.com/,头条|https://www.toutiao.com/ 【名称在网址前面，名称用|分割 新增的用，分割。 排序默认按照从左到右】') ?>
    <?= $form->field($model, 'is_jump')->radioList(\common\models\Base::getBaseS(), ['maxlength' => true])->hint('请确保有流量时才开启') ?>
    <?= $form->field($model, 'jump_url')->textInput(['maxlength' => true]) ?>


    <!--    --><? //= $form->field($model, 'status')->textInput() ?>
    <!---->
    <!--    --><? //= $form->field($model, 'user_id')->textInput() ?>
    <!---->
    <!--    --><? //= $form->field($model, 'created_at')->textInput() ?>
    <!---->
    <!--    --><? //= $form->field($model, 'updated_at')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
