<?php
require_once 'PDO.php';
require_once 'model.php';

class QCMController {
    private $model;

    public function __construct() {
        $this->model = new QCMModel();
    }

    public function handleRequest() {
        $result = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answers'])) {
            $answers = $_POST['answers'];
            $result = $this->model->checkAnswers($answers);
        }
        return $result;
    }

    public function getQuestions() {
        return $this->model->getQuestions();
    }
}
?>
