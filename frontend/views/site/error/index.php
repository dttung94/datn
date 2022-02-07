<?php

use yii\web\HttpException;

/* @var $this \frontend\models\FrontendView */
/* @var $name string */
/* @var $message string */
/* @var $exception HttpException */
$this->subTitle = $name;
if ($exception->statusCode == 404) {
    echo $this->render("_404", [
        "name" => $name,
        "message" => $message,
        "exception" => $exception,
    ]);
} else if ($exception->statusCode == 401) {
    echo $this->render("_401", [
        "name" => $name,
        "message" => $message,
        "exception" => $exception,
    ]);
} else if ($exception->statusCode == 400) {
    echo $this->render("_400", [
        "name" => $name,
        "message" => $message,
        "exception" => $exception,
    ]);
} else {
    echo $this->render("_500", [
        "name" => $name,
        "message" => $message,
        "exception" => $exception,
    ]);
}