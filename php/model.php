<?php
require_once 'PDO.php';

class QCMModel {
    private $pdo;
    private $questions;

    public function __construct() {
        $this->pdo = getPDO();
        if ($this->pdo === false) {
            // Fallback to array if DB not available
            $this->questions = [
                [
                    'id' => 1,
                    'question' => 'Quelle est la capitale de la France ?',
                    'options' => ['Paris', 'Lyon', 'Marseille', 'Toulouse'],
                    'correct' => 0
                ],
                [
                    'id' => 2,
                    'question' => 'Quel est le système d\'exploitation promu par la démarche NIRD ?',
                    'options' => ['Windows', 'macOS', 'Linux', 'Android'],
                    'correct' => 2
                ],
                [
                    'id' => 3,
                    'question' => 'Qu\'est-ce que NIRD signifie ?',
                    'options' => ['Numérique Inclusif Responsable Durable', 'Nouvelle Initiative pour le Réel Développement', 'Numérique Intelligent et Révolutionnaire Durable', 'Nouveau Internet pour la Recherche et la Découverte'],
                    'correct' => 0
                ]
            ];
        }
    }

    // CRUD Methods

    // Create: Add a new question
    public function addQuestion($question, $options, $correct) {
        if ($this->pdo) {
            $stmt = $this->pdo->prepare("INSERT INTO questions (question, options, correct) VALUES (?, ?, ?)");
            $stmt->execute([$question, json_encode($options), $correct]);
            return $this->pdo->lastInsertId();
        } else {
            $id = count($this->questions) + 1;
            $this->questions[] = ['id' => $id, 'question' => $question, 'options' => $options, 'correct' => $correct];
            return $id;
        }
    }

    // Read: Get all questions
    public function getQuestions() {
        if ($this->pdo) {
            $stmt = $this->pdo->query("SELECT * FROM questions");
            $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($questions as &$q) {
                $q['options'] = json_decode($q['options'], true);
            }
            return $questions;
        } else {
            return $this->questions;
        }
    }

    // Update: Update a question by id
    public function updateQuestion($id, $question, $options, $correct) {
        if ($this->pdo) {
            $stmt = $this->pdo->prepare("UPDATE questions SET question = ?, options = ?, correct = ? WHERE id = ?");
            return $stmt->execute([$question, json_encode($options), $correct, $id]);
        } else {
            foreach ($this->questions as &$q) {
                if ($q['id'] == $id) {
                    $q['question'] = $question;
                    $q['options'] = $options;
                    $q['correct'] = $correct;
                    return true;
                }
            }
            return false;
        }
    }

    // Delete: Delete a question by id
    public function deleteQuestion($id) {
        if ($this->pdo) {
            $stmt = $this->pdo->prepare("DELETE FROM questions WHERE id = ?");
            return $stmt->execute([$id]);
        } else {
            foreach ($this->questions as $key => $q) {
                if ($q['id'] == $id) {
                    unset($this->questions[$key]);
                    $this->questions = array_values($this->questions);
                    return true;
                }
            }
            return false;
        }
    }

    public function checkAnswers($answers) {
        $questions = $this->getQuestions();
        $score = 0;
        $total = count($questions);
        foreach ($questions as $question) {
            if (isset($answers[$question['id']]) && $answers[$question['id']] == $question['correct']) {
                $score++;
            }
        }
        return ['score' => $score, 'total' => $total];
    }
}
?>
