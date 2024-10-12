<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Theme */
/* @var $mentionProvider \yii\data\ActiveDataProvider */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Themes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="theme-view">

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
                'attribute' => 'account_id',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::a($model->account_id, ['/account/view', 'id' => $model->account_id]);
                },
            ],
            'status',
            'name',
            [
                'attribute' => 'words',
                'value' => function ($model) {
                    return implode(', ', $model->words);
                },
            ],
            [
                'attribute' => 'minusWords',
                'value' => function ($model) {
                    return implode(', ', $model->minusWords);
                },
            ],
            //'limit',
            'maxLimit',
            'collected',
            [
                'attribute' => 'sources',
                'value' => function ($model) {
                    return implode(', ', $model->sources);
                },
            ],
            'persisted:datetime',
            'scanned_at:datetime',
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>

    <?php \yii\widgets\Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= \yii\grid\GridView::widget([
        'dataProvider' => $mentionProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'Picture',
                'format' => 'raw',
                'value' => function ($model) {
                    return $model->picture ? Html::img($model->picture, ['style' => 'width: 50px']) : null;
                },
            ],
            'external_id',
            [
                'attribute' => 'link',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::a('Link', $model->link, ['target' => '_blank']);
                },
            ],

            //'user_id',
            //'username',
            'userlogin',
            //'userpic',
            'text',
            'source',
            //'meta',
            'created:datetime',
            //'persisted',
            [
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', ['/presentation-mention/view', 'id' => $model->id] , ['title' => 'View', 'data-pjax' => 0]);
                }
            ],

//            ['class' => 'yii\grid\ActionColumn', 'template' => '{view}'],
        ],
    ]); ?>

    <?php \yii\widgets\Pjax::end(); ?>

</div>
