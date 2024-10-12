<?php

class m200317_100253_update_presentation_mentions_table_change_attribute_names extends \yii\mongodb\Migration
{
    public function up()
    {
        $collection = Yii::$app->mongodb->getCollection(\common\models\presentation\PresentationMention::collectionName());
        $collection->aggregate([
            [
                '$addFields' => [
                    'media' => ['$ifNull' => ['$picture', '$media']]
                ],
            ],
            [
                '$out' => \common\models\presentation\PresentationMention::collectionName()
            ]
        ]);
    }

    public function down()
    {

    }
}
