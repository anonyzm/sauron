<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\presentation\PresentationMention */

$this->title = 'Create Presentation Mention';
$this->params['breadcrumbs'][] = ['label' => 'Presentation Mentions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="presentation-mention-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
