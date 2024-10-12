<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Account */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="account-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, '_id') ?>

    <?= $form->field($model, 'service_id') ?>

    <?= $form->field($model, 'external_id') ?>

    <?= $form->field($model, 'alias') ?>

    <?php //= $form->field($model, 'limit') ?>

    <?= $form->field($model, 'maxLimit') ?>

    <?= $form->field($model, 'collected') ?>

    <?= $form->field($model, 'timezone') ?>

    <?= $form->field($model, 'created_at') ?>

    <?= $form->field($model, 'updated_at') ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
