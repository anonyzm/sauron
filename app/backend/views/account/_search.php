<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model api\models\search\AccountSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="account-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, '_id') ?>

    <?= $form->field($model, 'service_id') ?>

    <?= $form->field($model, 'external_id') ?>

    <?= $form->field($model, 'alias') ?>

    <?= $form->field($model, 'limit') ?>

    <?php // echo $form->field($model, 'maxLimit') ?>

    <?php // echo $form->field($model, 'collected') ?>

    <?php // echo $form->field($model, 'timezone') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
