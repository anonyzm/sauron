<?php

namespace scheduler\console;

use common\console\BaseController;
use common\models\auth\RedditUser;
use yii\helpers\Console;

/**
 * Application setup and configuration
 *
 * @package console\controllers
 */
class RedditController extends BaseController
{
    public function actionAddUser($username, $password)
    {
        $redditUser = new RedditUser();
        $redditUser->setAttributes([
            'username' => $username,
            'password' => $password,
            'status' => RedditUser::STATUS_ACTIVE,
        ]);
        if(!$redditUser->save()) {
            var_dump($redditUser->errors);
            die();
        };
        Console::output("User `{$redditUser->username}` added!");
    }

    public function actionListUsers()
    {
        $users = RedditUser::find()->all();
        foreach ($users as $user) {
            Console::output(json_encode($user->attributes));
        }
    }

    public function actionCheckReset()
    {
        // если нет живых юзеров, то все юзеры с макс лимитом ошибок возвращаем в эктив
        $activeUsersCount = RedditUser::find()->where(['status' => RedditUser::STATUS_ACTIVE])->count();
        $maxErrorsUsersCount = RedditUser::find()->where(['status' => RedditUser::STATUS_MAX_ERRORS])->count();
        if($activeUsersCount === 0 && $maxErrorsUsersCount > 0) {
            RedditUser::updateAll(['status' => RedditUser::STATUS_ACTIVE], ['status' => RedditUser::STATUS_MAX_ERRORS]);
        }

        // если юзер больше часа висит с лимитом ошибок - вернем его в эктив
        $maxErrorsUsers = RedditUser::find()->where(['status' => RedditUser::STATUS_MAX_ERRORS])->all();
        /** @var RedditUser $user */
        foreach ($maxErrorsUsers as $user) {
            if (($user->updated_at + 3600) < time()) {
                $user->status = RedditUser::STATUS_ACTIVE;
                $user->save();
            }
        }

        $limitedUsers = RedditUser::find()->where(['status' => RedditUser::STATUS_RATE_LIMIT])->all();
        /** @var RedditUser $user */
        foreach ($limitedUsers as $user) {
            if (($user->rateLimitReset + $user->updated_at) < time()) {
                $user->status = RedditUser::STATUS_ACTIVE;
                $user->save();
            }
        }
    }
}