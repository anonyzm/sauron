<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\PresentationMentionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Presentation Mentions';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="presentation-mention-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <!--p>
        <?= Html::a('Create Presentation Mention', ['create'], ['class' => 'btn btn-success']) ?>
    </p-->

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'Media',
                'format' => 'raw',
                'value' => function ($model) {
                    return $model->media ? Html::img($model->media, ['style' => 'width: 50px']) : null;
                },
            ],
            [
                'attribute' => 'theme_id',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::a($model->theme_id, ['/theme/view', 'id' => $model->theme_id]);
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
            [
                'attribute' => 'external_link',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::a('External Link', $model->external_link, ['target' => '_blank']);
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

            ['class' => 'yii\grid\ActionColumn', 'template' => '{view}'],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
