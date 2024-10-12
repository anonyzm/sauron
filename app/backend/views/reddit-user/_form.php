<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\auth\RedditUser */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="reddit-user-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'username') ?>

    <?= $form->field($model, 'password')->passwordInput() ?>

    <?= $form->field($model, 'status') ?>

    <?= $form->field($model, 'created_at') ?>

    <?= $form->field($model, 'updated_at') ?>

    <?= $form->field($model, 'rateLimitRemaining') ?>

    <?= $form->field($model, 'rateLimitReset') ?>

    <?= $form->field($model, 'rateLimitUsed') ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
