<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\search\PresentationMentionSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="presentation-mention-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, '_id') ?>

    <?= $form->field($model, 'theme_id') ?>

    <?= $form->field($model, 'external_id') ?>

    <?= $form->field($model, 'link') ?>

    <?= $form->field($model, 'picture') ?>

    <?php // echo $form->field($model, 'user_id') ?>

    <?php // echo $form->field($model, 'username') ?>

    <?php // echo $form->field($model, 'userlogin') ?>

    <?php // echo $form->field($model, 'userpic') ?>

    <?php // echo $form->field($model, 'text') ?>

    <?php // echo $form->field($model, 'source') ?>

    <?php // echo $form->field($model, 'meta') ?>

    <?php // echo $form->field($model, 'created') ?>

    <?php // echo $form->field($model, 'persisted') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
