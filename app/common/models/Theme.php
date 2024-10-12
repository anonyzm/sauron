<?php
namespace common\models;

use common\models\presentation\MentionsDay;
use common\models\presentation\PresentationMention;
use common\models\traits\MongoActiveRecordTrait;
use Yii;
use yii\behaviors\AttributeTypecastBehavior;
use yii\behaviors\TimestampBehavior;
use yii\mongodb\ActiveRecord;
use yii\validators\StringValidator;

/**
 * Class Theme
 * @package common\models
 *
 * @property string $id
 * @property string $account_id
 * @property string $status
 * @property string $name
 * @property array $words
 * @property array $minusWords
 * @property array $sources
 * @property integer $limit
 * @property integer $maxLimit
 * @property integer $collected
 * @property integer $allowedLimit
 * @property integer $reserved
 * @property Account $account
 * @property integer $persisted
 * @property integer $scanned_at
 * @property integer $created_at
 * @property integer $updated_at
 */
class Theme extends ActiveRecord
{
    use MongoActiveRecordTrait {
        fields as mongoFields;
    }

    const STATUS_ACTIVE = 'active';     // активно - собираюсь
    const STATUS_INACTIVE = 'inactive'; // неактивно - пользователь отключил
    const STATUS_IDLE = 'idle';         // ожидание - кончился лимит
    const STATUS_DELETED = 'deleted';   // удалено - пользователь удалил

    const SOURCE_INSTAGRAM = 'instagram';
    const SOURCE_TWITTER = 'twitter';
    const SOURCE_REDDIT = 'reddit';
    const SOURCE_YOUTUBE = 'youtube';

    const MAX_WORDS = 5;
    const MAX_MINUS_WORDS = 10;

    public function attributes()
    {
        return [
            '_id',
            'account_id',
            'status',
            'name',
            'words',
            'limit',
            'maxLimit',
            'collected',
            'minusWords',
            'sources',
            'persisted',
            'scanned_at',
            'created_at',
            'updated_at',
        ];
    }

    public function fields()
    {
        $fields = $this->mongoFields();
        unset($fields['persisted']);
        unset($fields['scanned_at']);
        unset($fields['created_at']);
        unset($fields['updated_at']);

        $fields['created'] = 'created';
        $fields['updated'] = 'updated';
        $fields['scanned'] = 'scanned';
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

    public function init()
    {
        parent::init();

        $this->on(self::EVENT_AFTER_VALIDATE, [$this, 'onAfterValidate']);
        $this->on(self::EVENT_BEFORE_UPDATE, [$this, 'onBeforeSave']);
        $this->on(self::EVENT_BEFORE_INSERT, [$this, 'onBeforeSave']);
    }

    protected function onBeforeSave($event)
    {
        // нормализуем слова и стоп-слова (удаляем все кроме цифр,букв,пробелов)
        $words = [];
        foreach ($this->words as $key=>$word) {
            $word = preg_replace("/[^\w ]/u", '', $word);
            if('' !== trim($word)) { // если после "очистки" от слова ничего не осталось - убираем его вообще
                $words[] = $word;
            }
        }
        $minusWords = [];
        foreach ($this->minusWords as $key=>$word) {
            $word = preg_replace("/[^\w ]/u", '', $word);
            if('' !== trim($word)) { // если после "очистки" от минусслова ничего не осталось - убираем его вообще
                $minusWords[] = $word;
            }
        }

        // убираем индексы с массивов слов/стопслов/источников
        $this->words = array_values($words);
        $this->minusWords = array_values($minusWords);
        $this->sources = array_values($this->sources);
    }

    protected function onAfterValidate($event)
    {
        // если лимит исчерпан - ставим тему в режим ожидания
        if($this->status !== self::STATUS_INACTIVE && $this->allowedLimit <= 0) {
            $this->status = self::STATUS_IDLE;
        } // если лимит не нулевой и тема в статусе ожидания - активируем тему
        else if ($this->status === self::STATUS_IDLE && $this->allowedLimit > 0) {
            $this->status = self::STATUS_ACTIVE;
            // отмечаем последнее время сбора - время активации темы
            $this->scanned_at = time();
        }
        else if($this->status === self::STATUS_INACTIVE) {
            // удаляем если что-то было собрано но не заперсистчено
            $lastPresentationMention = PresentationMention::find()->where(['theme_id' => $this->id])->orderBy(['created' => SORT_DESC])->limit(1)->one();
            if ($lastPresentationMention) {
                $this->scanned_at = time();
                $this->persisted = null;
                Mention::deleteAll(['and', ['theme_id' => $this->id], ['>=', 'created', $lastPresentationMention->created]]);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['name', 'status', 'account_id', 'words', 'sources'], 'required'],
            [['_id', 'scanned'], 'safe'],
            [['status', 'account_id'], 'string'],
            ['status', 'in', 'range' => [self::STATUS_INACTIVE, self::STATUS_ACTIVE, self::STATUS_IDLE, self::STATUS_DELETED]],
            ['status', 'limitValidator'],
            [['limit', 'collected'], 'default', 'value' => 0],
            [['limit', 'maxLimit', 'collected', 'persisted', 'scanned_at', 'created_at', 'updated_at'], 'integer'],
            ['maxLimit', 'limitValidator'],
            [['name'], 'string', 'max' => 255],
            ['minusWords', 'default', 'value' => []],
            [['words', 'minusWords', 'sources'], 'arrayValidator'],
            [['words', 'sources'], 'nonEmptyArrayValidator'],
            [['words', 'sources'], 'nonEmptyElementsValidator'],
            [['words', 'minusWords', 'sources'], 'arrayValidator'],
            ['minusWords', 'minusWordsValidator'],
            ['words', 'maxElementsValidator', 'params' => ['count' => self::MAX_WORDS]],
            ['minusWords', 'maxElementsValidator', 'params' => ['count' => self::MAX_MINUS_WORDS]],
            [['words', 'minusWords', 'sources'], 'elementsMaxLengthValidator', 'params' => ['max' => 255]],
            [['words', 'minusWords', 'sources'], 'arrayUniqueValidator'],
            ['sources', 'sourcesValidator'],
        ];
    }

    public function limitValidator($attribute, $params, $validator) {
        // валидируем только лимиты активных и собранных тем, а также только если есть привязанный аккаунт (на всякий случай)
        if($this->account && in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_IDLE])) {
            $limitToCollect = 0;
            foreach ($this->account->themes as $theme) {
                if ($theme->id !== $this->id && $theme->maxLimit > $theme->collected) {
                    $limitToCollect += $theme->maxLimit - $theme->collected;
                }
            }
            $accountLimit = $this->account->maxLimit - ($this->account->collected - $this->collected) - $limitToCollect;

            if ($this->maxLimit > $accountLimit) {
                $this->addError($attribute, "Your theme limit ({$this->maxLimit}) exceeds account limit left ({$accountLimit})");
            }
        }
    }

    public function arrayValidator($attribute, $params, $validator) {
        if(!is_null($this->$attribute) && !is_array($this->$attribute)) {
            $this->addError($attribute, "`{$attribute}` should be an Array, ".gettype($this->$attribute).' given');
        }
    }

    public function arrayUniqueValidator($attribute, $params, $validator) {
        if(is_array($this->$attribute)) {
            if(count($this->$attribute) !== count(array_unique($this->$attribute))) {
                $this->addError($attribute, "`{$attribute}` contains duplicate values");
            }
        }
    }

    public function minusWordsValidator($attribute, $params, $validator) {
        $error = false;
        foreach ($this->minusWords as $minusWord) {
            if(in_array($minusWord, $this->words)) {
                $error = true;
                break;
            }
        }
        if($error) {
            $this->addError($attribute, "Minus words can't be same as search request");
        }
    }

    public function maxElementsValidator($attribute, $params, $validator) {
        if(is_array($this->$attribute)) {
            $count = count($this->$attribute);
            if($count > $params['count']) {
                $this->addError($attribute, "`{$attribute}` contains {$count} elements. Max {$params['count']} allowed");
            }
        }
    }

    public function nonEmptyArrayValidator($attribute, $params, $validator) {
        if(is_array($this->$attribute)) {
            if(count($this->$attribute) === 0) {
                $this->addError($attribute, "`{$attribute}` is an empty array");
            }
        }
    }

    public function nonEmptyElementsValidator($attribute, $params, $validator) {
        if(is_array($this->$attribute) && count($this->$attribute) > 0) {
            $error = false;
            foreach ($this->$attribute as $element) {
                if(empty($element)) {
                    $error = true;
                    break;
                }
            }

            if($error) {
                $this->addError($attribute, "`{$attribute}` contains empty values");
            }
        }
    }

    public function elementsMaxLengthValidator($attribute, $params, $validator) {
        $maxLength = $params['max'];
        $encoding = Yii::$app ? Yii::$app->charset : 'UTF-8';
        if(is_array($this->$attribute)) {
            foreach ($this->$attribute as $element) {
                $length = mb_strlen($element, $encoding);
                if($length > $maxLength) {
                    $this->addError($attribute, "`{$attribute}` element is too long ({$length}), maximum of {$maxLength} characters required");
                }
            }
        }
    }

    public function sourcesValidator($attribute, $params, $validator) {
        $sources = [];
        foreach (Yii::$app->params['sourcesAvailable'] as $source => $status) {
            if($status) {
                $sources[] = $source;
            }
        }

        if(is_array($this->$attribute)) {
            foreach ($this->$attribute as $source) {
                if(!in_array($source, $sources)) {
                    $this->addError($attribute, "`{$attribute}` contains invalid value `$source`");
                }
            }
        }
    }

    public function afterDelete()
    {
        Mention::deleteAll(['theme_id' => $this->id]);
        PresentationMention::deleteAll(['theme_id' => $this->id]);
        MentionsDay::deleteAll(['theme_id' => $this->id]);

        parent::afterDelete();
    }

    /**
     * Возвращаем доступный лимит, либо с самой темы, либо с аккаунта, у кого меньше
     * @return int|string
     */
    public function getAllowedLimit() {
        //return ($this->limit < $this->account->limit) ? $this->limit :  $this->account->limit;
        //return $this->limit;
        return $this->maxLimit - $this->collected;
    }

    public function getReserved() {
        return (($this->maxLimit > $this->collected) ? $this->maxLimit : $this->collected);
    }

    public function getAccount() {
        return $this->hasOne(Account::className(), ['_id' => 'account_id']);
    }

    public function getUpdated() {
        return $this->updated_at;
    }

    public function getCreated() {
        return $this->created_at;
    }

    public function getScanned() {
        return $this->scanned_at;
    }

    public function setScanned($value) {
        return $this->scanned_at = $value;
    }

    public function __toString()
    {
        $words = implode(' OR ', $this->words);
        $minusWords = '';
        foreach ($this->minusWords as $minusWord) {
            $minusWords .= '-'.$minusWord.' ';
        }
        $minusWords = trim($minusWords);
        return $words.' '.$minusWords;
    }
}