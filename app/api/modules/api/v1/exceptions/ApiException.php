<?php
namespace api\modules\api\v1\exceptions;

use yii\web\HttpException;

class ApiException extends HttpException
{
    public $errors = null;

    public function __construct(array $errors, $message)
    {
        // преобразование нужно если в штатной валидации не переопределен мессадж
        foreach ($errors as &$itemsError) {
            foreach ($itemsError as &$error) {
                if (!is_object($error)) {
                    $error = new ApiErrorData([
                        'code' => 'unexpected_error',
                        'data' => [
                            'error' => $error,
                        ]
                    ]);
                }
            }
        }

        $this->errors = $errors;

        parent::__construct(422, $message);
    }
}
