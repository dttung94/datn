<?php

use yii\web\HttpException;

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception HttpException */

if ($exception->statusCode == 404) {
    echo $this->render("_404", [
        "name" => $name,
        "message" => $message,
        "exception" => $exception,
    ]);
} elseif ($exception->statusCode == 403) {
    echo $this->render("_403", [
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