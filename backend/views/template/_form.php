<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Template */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="template-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'content')->textarea(['rows' => 8]) ?>

    <?= $form->field($model, 'php_func')->textarea(['rows' => 5])->hint('需要自定义页面时,找后端填写此栏目') ?>

    <?= $form->field($model, 'cate')->radioList(\common\models\Template::getCate(), []) ?>

    <?= $form->field($model, 'type')->dropDownList(\common\models\Template::getType(), []) ?>

    <?= $form->field($model, 'en_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'intro')->textarea(['rows' => 3]) ?>

    <?= $form->field($model, 'img')->widget(\kartik\file\FileInput::classname(), [
        'options' => ['multiple' => false],
        'pluginOptions' => [
            'initialPreview' => [$model->img],
            // 是否展示预览图
            'initialPreviewAsData' => true,
            // 是否显示移除按钮，指input上面的移除按钮，非具体图片上的移除按钮
            'showRemove' => true,
            // 是否显示上传按钮，指input上面的上传按钮，非具体图片上的上传按钮
            'showUpload' => false,
        ],
    ])->fileInput();
    ?>

    <?= $form->field($model, 'status')->dropDownList(\common\models\Base::getBaseStatus(), []) ?>


    <!---->
    <!--    --><? //= $form->field($model, 'created_at')->textInput() ?>
    <!---->
    <!--    --><? //= $form->field($model, 'updated_at')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>