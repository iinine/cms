<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Template */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="template-form">

    <?php $form = ActiveForm::begin();
    if (strpos(Yii::$app->request->url, 'create') !== false) {
        $model->cate = \common\models\Template::CATE_PC;
    }
    ?>

    <?= $form->field($model, 'type')->dropDownList(\common\models\Template::getType(), []) ?>

    <?= $form->field($model, 'cate')->radioList(\common\models\Template::getCate(), []) ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'en_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'content')->textarea(['rows' => 8]) ?>
    <a  href="/index.php/template/edit?id=<?= $model->id?>" class="btn_edit" target="_blank">【快捷键打开编辑器】 ctrl + x 【快捷键保存】 ctrl + s</a>
    <?= $form->field($model, 'php_func')->textarea(['rows' => 5])->hint('需要自定义页面时,找后端填写此栏目') ?>


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

<div class="showDiv_edit" style="display:none">
    <div class="showDiv_title">
        <h2>备注用户名</h2>
    </div>
    <div class="showDiv_con"> <span class="fr limit_num">2/30</span>
        <iframe href="/index.php/template/edit"> </iframe>

    </div>
</div>

<div style="text-align:center;">

    <script src="http://libs.baidu.com/jquery/2.0.0/jquery.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $(".btn_edit").click(function () {
                $.ajax({
                    url: "/",
                    type: "post",
                    data: {content: $("#template-content").val()},
                    success: function (data) {
                        $('.showDiv_opaciy, .showDiv_edit').show();
                    }
                });
            });
        });


        $(document).keydown(function (e) {
            // ctrl + s
            if (e.ctrlKey == true && e.keyCode == 88) {
                window.location.href= "/index.php/template/edit?id=<?= $model->id?>";  //格式为url？参数
            }
        });


        <?php $form = ActiveForm::begin();
        if (strpos(Yii::$app->request->url, 'create') !== false) {
        ?>
        $.ajax({
            url: '/index.php/template/get-name',
            type: 'GET',
            dataType: 'json',
            data: {type: $("#template-type").find("option:selected").val(), cate: $('input:radio:checked').val()},
            success: function (msg) {
                $("#template-name").val(msg.name);
                $("#template-en_name").val(msg.en_name);
            }
        })

        $("#template-type").change(function () {
            var html = '';
            $.ajax({
                url: '/index.php/template/get-name',
                type: 'GET',
                dataType: 'json',
                data: {type: $("#template-type").find("option:selected").val(), cate: $('input:radio:checked').val()},
                success: function (msg) {
                    $("#template-name").val(msg.name);
                    $("#template-en_name").val(msg.en_name);
                }
            })
        });

        $("#template-cate").change(function () {
            var html = '';
            $.ajax({
                url: '/index.php/template/get-name',
                type: 'GET',
                dataType: 'json',
                data: {type: $("#template-type").find("option:selected").val(), cate: $('input:radio:checked').val()},
                success: function (msg) {
                    $("#template-name").val(msg.name);
                    $("#template-en_name").val(msg.en_name);
                }
            })
        });
        <?php
        }
        ?>
    </script>

