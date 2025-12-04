<?php
require_once 'PDO.php';
require_once 'controller.php';

$controller = new QCMController();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $questions = $controller->getQuestions();
    header('Content-Type: application/json');
    echo json_encode($questions);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $controller->handleRequest();
    header('Content-Type: application/json');
    echo json_encode($result);
}
?>
