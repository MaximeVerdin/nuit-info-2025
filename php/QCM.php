<?php
require_once 'PDO.php';
require_once 'controller.php';

$controller = new QCMController();
$result = $controller->handleRequest();
$questions = $controller->getQuestions();
?>
