<?php
namespace common\exceptions;

use yii\base\Exception;

class ParserException extends Exception
{
    public $data;

    public function __construct(string $message = "", $data = null, int $code = 0, Throwable $previous = null)
    {
        $this->data = $data;

        parent::__construct($message, $code, $previous);
    }
}