<?php

class m190821_140609_init_collections extends \yii\mongodb\Migration
{
    public function up()
    {
        //$this->createCollection('service');

        //$this->createCollection('account');
        $this->createIndex('account', ['service_id' => 1]);

        //$this->createCollection('theme');
        $this->createIndex('theme', ['account_id' => 1]);
        $this->createIndex('theme', ['status' => 1]);
        $this->createIndex('theme', ['scanned_at' => -1]);

        //$this->createCollection('mention');
        $this->createIndex('mention', ['theme_id' => 1]);
        $this->createIndex('mention', ['theme_id' => 1, 'external_id' => 1], ['unique' => true]);
        $this->createIndex('mention', ['created' => 1]);
        $this->createIndex('mention', ['created' => -1]);

//        TODO: убрано т.к. эти 2 коллекции вынесены в отдельную базу
//        $this->createCollection('presentation_mention');
//        $this->createIndex('presentation_mention', ['theme_id' => 1]);
//        $this->createIndex('presentation_mention', ['theme_id' => 1, 'source' => 1]);
//        $this->createIndex('presentation_mention', ['theme_id' => 1, 'external_id' => 1], ['unique' => true]);
//        $this->createIndex('presentation_mention', ['created' => 1]);
//        $this->createIndex('presentation_mention', ['created' => -1]);

//        $this->createCollection('mentions_day');
//        $this->createIndex('mentions_day', ['theme_id' => 1]);
//        $this->createIndex('mentions_day', ['day_timestamp' => -1]);
//        $this->createIndex('mentions_day', ['day_timestamp' => 1]);
//        $this->createIndex('mentions_day', ['source' => 1]);
//        $this->createIndex('mentions_day', ['theme_id' => 1, 'day_timestamp' => 1, 'source' => 1], ['unique' => true]);
    }

    public function down()
    {

    }
}
