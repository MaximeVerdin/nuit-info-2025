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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['answers'])) {
                $answers = $_POST['answers'];
                $result = $this->model->checkAnswers($answers);
                // Save responses if user is logged in
                if (isset($_SESSION['user_id'])) {
                    foreach ($answers as $question_id => $answer) {
                        $this->model->saveResponse($_SESSION['user_id'], $question_id, $answer);
                    }
                }
            } elseif (isset($_POST['register'])) {
                $nom = $_POST['nom'];
                $password = $_POST['password'];
                $result = $this->model->registerUser($nom, $password) ? 'registered' : 'register_failed';
            } elseif (isset($_POST['login'])) {
                $nom = $_POST['nom'];
                $password = $_POST['password'];
                $user_id = $this->model->loginUser($nom, $password);
                if ($user_id) {
                    $_SESSION['user_id'] = $user_id;
                    $result = 'logged_in';
                } else {
                    $result = 'login_failed';
                }
            }
        }
        return $result;
    }

    public function getQuestions() {
        return $this->model->getQuestions();
    }

    public function getUserResponses($user_id) {
        return $this->model->getUserResponses($user_id);
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function logout() {
        unset($_SESSION['user_id']);
    }
}
?>
