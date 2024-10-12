<?php

class m200522_174811_update_mentions_table_add_repersist_indexes extends \yii\mongodb\Migration
{
    public function up()
    {
        $this->createIndex('mention', ['source' => 1]);
        $this->createIndex('mention', ['source' => 1, 'external_id' => 1]);
        $this->createIndex('mention', ['updated_at' => 1]);
    }

    public function down()
    {

    }
}
