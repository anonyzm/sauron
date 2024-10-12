<?php
namespace common\models;

use common\models\traits\MongoActiveRecordTrait;
use common\models\transport\data\TimezoneData;
use common\models\transport\payloads\SauronActionPayload;
use Yii;
use yii\behaviors\AttributeTypecastBehavior;
use yii\behaviors\TimestampBehavior;
use yii\mongodb\ActiveRecord;

/**
 * Class Theme
 * @package common\models
 *
 * @property string $id
 * @property string $status
 * @property string $alias
 * @property integer $limit
 * @property integer $maxLimit
 * @property integer $limitAvailable
 * @property integer $collected
 * @property integer $totalCollected
 * @property string $timezone
 * @property string $service_id
 * @property string $external_id
 * @property Theme[] $themes
 * @property integer $created_at
 * @property integer $updated_at
 */
class Account extends ActiveRecord
{
    use MongoActiveRecordTrait {
        fields as mongoFields;
    }

    public function init()
    {
        parent::init();
        $this->on(ActiveRecord::EVENT_BEFORE_UPDATE, [$this, 'beforeUpdate']);
    }

    public function attributes()
    {
        return [
            '_id',
            'service_id',
            'external_id',
            'alias',
            'limit',
            'maxLimit',
            'collected',
            'timezone',
            'created_at',
            'updated_at',
        ];
    }

    public function fields()
    {
        $fields = $this->mongoFields();
        unset($fields['created_at']);
        unset($fields['updated_at']);

        $fields['created'] = 'created';
        $fields['updated'] = 'updated';
        $fields['limitAvailable'] = 'limitAvailable';
        $fields['totalCollected'] = 'totalCollected';
        return $fields;
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['timestamp'] = [
            'class' => TimestampBehavior::className(),
        ];

        $behaviors['typecast'] = [
            'class' => AttributeTypecastBehavior::class,
        ];

        return $behaviors;
    }

    public function getThemes() {
        return $this->hasMany(Theme::className(), ['account_id' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['_id'], 'safe'],
            [['alias', 'service_id', 'external_id', 'timezone'], 'required'],
            [['alias', 'service_id', 'external_id', 'timezone'], 'string'],
            [['service_id', 'external_id'], 'unique', 'targetAttribute' => 'external_id'],
            [['service_id', 'alias'], 'unique', 'targetAttribute' => 'alias'],
            ['maxLimit', 'default', 'value' => 0],
            ['maxLimit', 'integer', 'min' => 0],
            ['limit', 'default', 'value' => 0],
            ['limit', 'integer', 'min' => 0],
            ['collected', 'default', 'value' => 0],
            ['collected', 'integer', 'min' => 0],
            //['limit', 'limitValidator'],
            ['timezone', 'timezoneValidator'],
            [['created_at', 'updated_at'], 'integer'],
        ];
    }

    /*public function limitValidator($attribute, $params, $validator) {
        $themesLimit = 0;
        foreach ($this->themes as $theme) {
            $themesLimit += $theme->limit;
        }

        if($themesLimit > $this->limit) {
            $this->addError($attribute, "Your account limit ({$this->limit}) can't be smaller than total themes limit ({$themesLimit})");
        }
    }*/

    public function timezoneValidator($attribute, $params, $validator) {
        try {
            $tz = date_default_timezone_get();
            date_default_timezone_set($this->$attribute);
        }
        catch(\Throwable $e) {
            $this->addError($attribute, "Timezone '{$this->$attribute}' is invalid");
        }
        finally {
            date_default_timezone_set($tz);
        }
    }

    public function beforeUpdate()
    {
        // пересчитываем слой представления, если была изменена таймзона
        $oldTimezone = $this->getOldAttribute('timezone');
        if ($this->timezone !== $oldTimezone) {
            // запускаем джоб по пересчету слоя представления для каждой темы
            foreach ($this->themes as $theme) {
                $message = Yii::$app->rabbit->createMessage([
                    'body' => new SauronActionPayload([
                        'action' => 'timezone',
                        'data' => new TimezoneData([
                            'theme_id' => $theme->id,
                            'timezone' => $this->timezone,
                        ]),
                    ]),
                ]);
                Yii::$app->rabbit->exchange(Yii::$app->params['presentationExchange'])
                    ->routingKey(Yii::$app->params['presentationRoutingKey'])
                    ->push($message);
            }
        }
    }

    public function getLimitAvailable() {
       $limitToCollect = 0;
        foreach ($this->themes as $theme) {
            if($theme->maxLimit > $theme->collected) {
                $limitToCollect += $theme->maxLimit - $theme->collected;
            }
        }
        return $this->maxLimit - $this->collected - $limitToCollect;
    }

    /**
     * @deprecated
     * @return int
     */
    public function getTotalCollected() {
        $collected = 0;
        foreach ($this->themes as $theme) {
            $collected += $theme->collected;
        }

        return $collected;
    }

    public function getUpdated() {
        return $this->updated_at;
    }

    public function getCreated() {
        return $this->created_at;
    }
}