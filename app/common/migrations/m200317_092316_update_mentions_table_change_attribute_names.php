<?php

class m200317_092316_update_mentions_table_change_attribute_names extends \yii\mongodb\Migration
{
    public function up()
    {
        $collection = Yii::$app->mongodb->getCollection(\common\models\Mention::collectionName());
        $collection->aggregate([
            [
                '$addFields' => [
                    'media' => ['$ifNull' => ['$picture', '$media']]
                ],
            ],
            [
                '$out' => \common\models\Mention::collectionName()
            ]
        ]);
    }

    public function down()
    {

    }
}
