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
                    'rowid' => 1,
                    'contenu' => json_encode([
                        'question' => 'Quel usage faites-vous le plus souvent du numérique dans vos activités sportives ?',
                        'options' => ['Suivi d’entraînement', 'Vidéos/coachings en ligne', 'Communication', 'Organisation de sorties', 'Aucun']
                    ])
                ],
                [
                    'rowid' => 2,
                    'contenu' => json_encode([
                        'question' => 'Lorsque vous suivez un programme sportif, quel type d’application privilégiez-vous ?',
                        'options' => ['Logiciels libres', 'Applications classiques du store', 'Outils web accessibles', 'Aucun outil particulier']
                    ])
                ],
                [
                    'rowid' => 3,
                    'contenu' => json_encode([
                        'question' => 'Êtes-vous sensible à l’impact environnemental de votre matériel sportif ou numérique ?',
                        'options' => ['Oui très', 'Un peu', 'Peu', 'Pas du tout']
                    ])
                ],
                [
                    'rowid' => 4,
                    'contenu' => json_encode([
                        'question' => 'Que privilégiez-vous pour votre équipement sportif ou numérique ?',
                        'options' => ['Durabilité', 'Polyvalence', 'Prix maîtrisé', 'Réparabilité']
                    ])
                ],
                [
                    'rowid' => 5,
                    'contenu' => json_encode([
                        'question' => 'Avez-vous déjà utilisé un appareil reconditionné pour vos activités sportives ?',
                        'options' => ['Oui', 'Occasionnellement', 'Non mais pourquoi pas', 'Non']
                    ])
                ],
                [
                    'rowid' => 6,
                    'contenu' => json_encode([
                        'question' => 'Quel type d’outil numérique vous aide le plus dans votre pratique sportive ?',
                        'options' => ['Montre/bracelet connecté', 'Smartphone', 'PC portable', 'Aucun équipement numérique']
                    ])
                ],
                [
                    'rowid' => 7,
                    'contenu' => json_encode([
                        'question' => 'Pour vos séances ou randonnées, quels critères sont les plus importants pour vous ?',
                        'options' => ['Autonomie de l’appareil', 'Robustesse', 'Légèreté', 'Prix', 'Sobriété numérique']
                    ])
                ],
                [
                    'rowid' => 8,
                    'contenu' => json_encode([
                        'question' => 'Avez-vous déjà cherché des alternatives libres ou responsables pour suivre vos efforts ?',
                        'options' => ['Oui souvent', 'Oui une fois', 'Pas encore', 'Non']
                    ])
                ],
                [
                    'rowid' => 9,
                    'contenu' => json_encode([
                        'question' => 'Quel type de conseils vous serait le plus utile pour concilier sport et numérique responsable ?',
                        'options' => ['Limiter l’impact environnemental', 'Réduire la dépendance aux applications', 'Optimiser l’équipement existant', 'Choisir des produits durables']
                    ])
                ],
                [
                    'rowid' => 10,
                    'contenu' => json_encode([
                        'question' => 'Êtes-vous intéressé par des recommandations de matériel sportif durable ou polyvalent ?',
                        'options' => ['Oui', 'Oui pour débuter', 'Occasionnellement', 'Non']
                    ])
                ]
            ];
        }
    }

    // CRUD Methods

    // Create: Add a new question
    public function addQuestion($question, $options, $correct) {
        if ($this->pdo) {
            $contenu = json_encode(['question' => $question, 'options' => $options, 'correct' => $correct]);
            $stmt = $this->pdo->prepare("INSERT INTO Questions (contenu) VALUES (?)");
            $stmt->execute([$contenu]);
            return $this->pdo->lastInsertId();
        } else {
            $id = count($this->questions) + 1;
            $this->questions[] = ['rowid' => $id, 'contenu' => json_encode(['question' => $question, 'options' => $options, 'correct' => $correct])];
            return $id;
        }
    }

    // Read: Get all questions
    public function getQuestions() {
        if ($this->pdo) {
            $stmt = $this->pdo->query("SELECT * FROM Questions");
            $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($questions as &$q) {
                $data = json_decode($q['contenu'], true);
                $q['question'] = $data['question'];
                $q['options'] = $data['options'];
            }
            return $questions;
        } else {
            $questions = [];
            foreach ($this->questions as $q) {
                $data = json_decode($q['contenu'], true);
                $questions[] = [
                    'rowid' => $q['rowid'],
                    'question' => $data['question'],
                    'options' => $data['options']
                ];
            }
            return $questions;
        }
    }

    // Update: Update a question by id
    public function updateQuestion($id, $question, $options, $correct) {
        if ($this->pdo) {
            $contenu = json_encode(['question' => $question, 'options' => $options, 'correct' => $correct]);
            $stmt = $this->pdo->prepare("UPDATE Questions SET contenu = ? WHERE rowid = ?");
            return $stmt->execute([$contenu, $id]);
        } else {
            foreach ($this->questions as &$q) {
                if ($q['rowid'] == $id) {
                    $q['contenu'] = json_encode(['question' => $question, 'options' => $options, 'correct' => $correct]);
                    return true;
                }
            }
            return false;
        }
    }

    // Delete: Delete a question by id
    public function deleteQuestion($id) {
        if ($this->pdo) {
            $stmt = $this->pdo->prepare("DELETE FROM Questions WHERE rowid = ?");
            return $stmt->execute([$id]);
        } else {
            foreach ($this->questions as $key => $q) {
                if ($q['rowid'] == $id) {
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
        $total = count($questions);
        $answered = count($answers);
        return ['answered' => $answered, 'total' => $total];
    }

    // User Management Methods

    // Register a new user
    public function registerUser($nom, $password) {
        if ($this->pdo) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("INSERT INTO Utilisateurs (nom, password) VALUES (?, ?)");
            return $stmt->execute([$nom, $hashedPassword]);
        } else {
            // Fallback: store in session or array (not persistent)
            return false;
        }
    }

    // Login user
    public function loginUser($nom, $password) {
        if ($this->pdo) {
            $stmt = $this->pdo->prepare("SELECT * FROM Utilisateurs WHERE nom = ?");
            $stmt->execute([$nom]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user && password_verify($password, $user['password'])) {
                return $user['rowid'];
            }
        }
        return false;
    }

    // Response Management Methods

    // Save user response
    public function saveResponse($id_utilisateur, $id_question, $reponse) {
        if ($this->pdo) {
            $stmt = $this->pdo->prepare("INSERT INTO Reponses (id_utilisateur, id_question, reponse) VALUES (?, ?, ?)");
            return $stmt->execute([$id_utilisateur, $id_question, $reponse]);
        } else {
            // Fallback: not implemented for array
            return false;
        }
    }

    // Get user responses
    public function getUserResponses($id_utilisateur) {
        if ($this->pdo) {
            $stmt = $this->pdo->prepare("SELECT * FROM Reponses WHERE id_utilisateur = ?");
            $stmt->execute([$id_utilisateur]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return [];
        }
    }
}
?>
