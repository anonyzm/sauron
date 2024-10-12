<?php
/**
 * Yii bootstrap file.
 * Used for enhanced IDE code autocompletion.
 * Note: To avoid "Multiple Implementations" PHPStorm warning and make autocomplete faster
 * exclude or "Mark as Plain Text" vendor/yiisoft/yii2/Yii.php file
 */
class Yii extends \yii\BaseYii
{
    /**
     * @var BaseApplication|WebApplication|ConsoleApplication the application instance
     */
    public static $app;
}

/**
 * Class BaseApplication
 * Used for properties that are identical for both WebApplication and ConsoleApplication
 *
 * @property \yii\i18n\I18N $i18n
 * @property \ladno\woody\components\rabbit\Producer $rabbit
 * @property \yii\mongodb\Connection $mongodb
 * @property \yii\mongodb\Connection $presentationMongodb
 * @property yii\redis\Mutex $mutex
 * @property understeam\slack\Client $slack
 * @property \ladno\instaparserlib\IgClient $instaparser
 * @property \ladno\proxyconveyor\interfaces\ProxyInterface $twitterProxy
 * @property \common\components\reddit\RedditAuthConveyor $reddit
 * @property \ladno\yii2settings\Component $settings
 * @property \common\interfaces\ProxyProviderInterface $proxyProvider
 */
abstract class BaseApplication extends yii\base\Application
{
}

/**
 * Class WebApplication
 * Include only Web application related components here
 *
 * @property User $user User component.
 */
class WebApplication extends yii\web\Application
{
}

/**
 * Class ConsoleApplication
 * Include only Console application related components here
 */
class ConsoleApplication extends yii\console\Application
{
}

/**
 * User component
 * Include only Web application related components here
 *
 * @property \app\models\User $identity User model.
 * @method \app\models\User getIdentity() returns User model.
 */
class User extends \yii\web\User
{
}