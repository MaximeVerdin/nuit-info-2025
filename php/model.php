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
                        'question' => 'Que signifie NIRD ?',
                        'options' => ['Numérique Inclusif Responsable Durable', 'Nouvelle Initiative pour le Réel Développement', 'Numérique Intelligent et Révolutionnaire Durable', 'Nouveau Internet pour la Recherche et la Découverte'],
                        'correct' => 0
                    ])
                ],
                [
                    'rowid' => 2,
                    'contenu' => json_encode([
                        'question' => 'Quel système d\'exploitation la démarche NIRD promeut-elle principalement ?',
                        'options' => ['Windows', 'macOS', 'Linux', 'Android'],
                        'correct' => 2
                    ])
                ],
                [
                    'rowid' => 3,
                    'contenu' => json_encode([
                        'question' => 'Pourquoi la fin du support de Windows 10 est-elle favorable à la démarche NIRD ?',
                        'options' => ['Elle rend les machines obsolètes artificiellement', 'Elle encourage la migration vers des OS libres', 'Elle réduit les coûts pour Microsoft', 'Elle améliore la sécurité de Windows'],
                        'correct' => 1
                    ])
                ],
                [
                    'rowid' => 4,
                    'contenu' => json_encode([
                        'question' => 'Qu\'est-ce que PrimTux ?',
                        'options' => ['Une distribution Linux conçue pour les écoles', 'Un logiciel de bureautique', 'Un outil de reconditionnement', 'Une plateforme de formation en ligne'],
                        'correct' => 0
                    ])
                ],
                [
                    'rowid' => 5,
                    'contenu' => json_encode([
                        'question' => 'Quel pourcentage de l\'impact environnemental du numérique provient de la fabrication des équipements ?',
                        'options' => ['25%', '50%', '75%', '90%'],
                        'correct' => 2
                    ])
                ],
                [
                    'rowid' => 6,
                    'contenu' => json_encode([
                        'question' => 'Quel est l\'objectif de réemploi des biens informatiques en 2025 selon la loi AGEC ?',
                        'options' => ['25%', '50%', '75%', '100%'],
                        'correct' => 1
                    ])
                ],
                [
                    'rowid' => 7,
                    'contenu' => json_encode([
                        'question' => 'Selon la Doctrine technique du numérique pour l\'éducation, que permettent les services d\'infrastructures numériques choisis ?',
                        'options' => ['Intégrer des machines utilisant différents systèmes d\'exploitation pour la neutralité technologique', 'Forcer l\'usage de Windows uniquement', 'Limiter l\'accès à Internet', 'Augmenter les coûts d\'équipement'],
                        'correct' => 0
                    ])
                ],
                [
                    'rowid' => 8,
                    'contenu' => json_encode([
                        'question' => 'Quelle est la principale motivation de la démarche NIRD selon le document ?',
                        'options' => ['Réduire les coûts informatiques', 'Répondre à l\'urgence écologique et à l\'obsolescence programmée', 'Promouvoir les logiciels propriétaires', 'Augmenter la dépendance aux grandes entreprises'],
                        'correct' => 1
                    ])
                ],
                [
                    'rowid' => 9,
                    'contenu' => json_encode([
                        'question' => 'Quelles collectivités sont citées comme s\'orientant vers les logiciels libres ?',
                        'options' => ['Paris, Marseille, Toulouse', 'Lyon, Grenoble, Strasbourg', 'Bordeaux, Lille, Nantes', 'Nice, Rennes, Dijon'],
                        'correct' => 1
                    ])
                ],
                [
                    'rowid' => 10,
                    'contenu' => json_encode([
                        'question' => 'Quel est le rôle de la Forge des communs numériques éducatifs dans la démarche NIRD ?',
                        'options' => ['Vendre des logiciels propriétaires', 'Réunir, fédérer, mutualiser et documenter les efforts', 'Contrôler l\'usage des ordinateurs dans les écoles', 'Développer des applications mobiles'],
                        'correct' => 1
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
                $q['correct'] = $data['correct'];
            }
            return $questions;
        } else {
            $questions = [];
            foreach ($this->questions as $q) {
                $data = json_decode($q['contenu'], true);
                $questions[] = [
                    'rowid' => $q['rowid'],
                    'question' => $data['question'],
                    'options' => $data['options'],
                    'correct' => $data['correct']
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
        $score = 0;
        $total = count($questions);
        foreach ($questions as $question) {
            if (isset($answers[$question['rowid']]) && $answers[$question['rowid']] == $question['correct']) {
                $score++;
            }
        }
        return ['score' => $score, 'total' => $total];
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
