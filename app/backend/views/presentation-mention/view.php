<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\presentation\PresentationMention */

$this->title = $model->_id;
$this->params['breadcrumbs'][] = ['label' => 'Presentation Mentions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="presentation-mention-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => (string)$model->_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => (string)$model->_id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            '_id',
            [
                'attribute' => 'theme_id',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::a($model->theme_id, ['/theme/view', 'id' => $model->theme_id]);
                },
            ],
            'external_id',
            'link',
            'external_link',
            'media',
            'media_type',
            'user_id',
            'username',
            'userlogin',
            'userpic',
            'text',
            'source',
            //'meta',
            [
                'attribute' => 'meta',
                'value' => function ($model) {
                    return print_r($model->meta, true);
                },
            ],
            'created:datetime',
            'persisted:datetime',
        ],
    ]) ?>

</div>
