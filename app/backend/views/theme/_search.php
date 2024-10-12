<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model api\models\search\ThemeSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="theme-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, '_id') ?>

    <?= $form->field($model, 'account_id') ?>

    <?= $form->field($model, 'status') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'words') ?>

    <?php // echo $form->field($model, 'limit') ?>

    <?php // echo $form->field($model, 'maxLimit') ?>

    <?php // echo $form->field($model, 'collected') ?>

    <?php // echo $form->field($model, 'minusWords') ?>

    <?php // echo $form->field($model, 'sources') ?>

    <?php // echo $form->field($model, 'persisted') ?>

    <?php // echo $form->field($model, 'scanned_at') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
