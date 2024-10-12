<?php

namespace common\console;

use yii\console\Controller;
use yii\helpers\Console;

/**
 * Application setup and configuration
 *
 * @package console\controllers
 */
abstract class BaseController extends Controller
{
    public function actionIndex()
    {
        $this->run('/help', [$this->id]);

        return Controller::EXIT_CODE_NORMAL;
    }

    public function actionCheckConnections() {
        $dbConnections = [
            'mongodb' => 'MongoDB',
            'presentationMongodb' => 'Presentation MongoDB',
            'redis' => 'Redis',
            'rabbit' => 'Rabbit',
        ];

        foreach ($dbConnections as $connection => $title) {
            Console::stdout("Checking connection <$title>: ");
            $result = '%gOK%n';
            try {
                \Yii::$app->get($connection)->open();
            } catch (\Exception $exception) {
                echo $exception->getMessage().PHP_EOL;
                $result = '%rFail%n';
            }
            Console::output(Console::renderColoredString($result));
        }
    }
}
