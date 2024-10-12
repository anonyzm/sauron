<?php
namespace api\modules\api\v1\components;

use api\modules\api\v1\exceptions\ApiException;

class ErrorHandler extends \yii\web\ErrorHandler
{
    /**
     * Converts an exception into an array.
     * @param \Exception|\Error $exception the exception being converted
     * @return array the array representation of the exception.
     */
    protected function convertExceptionToArray($exception)
    {
        $array = parent::convertExceptionToArray($exception);

        if ($exception instanceof ApiException) {
            $array['errors'] = $exception->errors;
        }

        return $array;
    }
}
