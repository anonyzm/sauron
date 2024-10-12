<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Theme */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="theme-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, '_id') ?>

    <?= $form->field($model, 'account_id') ?>

    <?= $form->field($model, 'status') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'words') ?>

    <?php //= $form->field($model, 'limit') ?>

    <?= $form->field($model, 'maxLimit') ?>

    <?= $form->field($model, 'collected') ?>

    <?= $form->field($model, 'minusWords') ?>

    <?= $form->field($model, 'sources') ?>

    <?= $form->field($model, 'persisted') ?>

    <?= $form->field($model, 'scanned_at') ?>

    <?= $form->field($model, 'created_at') ?>

    <?= $form->field($model, 'updated_at') ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
