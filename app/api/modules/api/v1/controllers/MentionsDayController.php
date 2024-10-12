<?php

namespace api\modules\api\v1\controllers;

use api\modules\api\v1\controllers\base\BaseActiveController;
use api\modules\api\v1\models\MentionsDaySearch;
use common\models\presentation\MentionsDay;

class MentionsDayController extends BaseActiveController
{
    public $modelClass = MentionsDay::class;
    public $searchModelClass = MentionsDaySearch::class;

    public function actions()
    {
        $actions = parent::actions();

        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);

        return $actions;
    }
}
