<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\presentation\PresentationMention */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="presentation-mention-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'theme_id') ?>

    <?= $form->field($model, 'external_id') ?>

    <?= $form->field($model, 'link') ?>

    <?= $form->field($model, 'media') ?>

    <?= $form->field($model, 'media_type') ?>

    <?= $form->field($model, 'user_id') ?>

    <?= $form->field($model, 'username') ?>

    <?= $form->field($model, 'userlogin') ?>

    <?= $form->field($model, 'userpic') ?>

    <?= $form->field($model, 'text') ?>

    <?= $form->field($model, 'source') ?>

    <?= $form->field($model, 'external_link') ?>

    <?= $form->field($model, 'meta') ?>

    <?= $form->field($model, 'created') ?>

    <?= $form->field($model, 'persisted') ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
