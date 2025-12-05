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

        // Analyze answers for advice
        $advice = $this->generateAdvice($answers, $questions);

        // Generate product suggestions
        $suggestions = $this->generateSuggestions($answers, $questions);

        return ['answered' => $answered, 'total' => $total, 'advice' => $advice, 'suggestions' => $suggestions];
    }

    private function generateAdvice($answers, $questions) {
        $advice = [];

        // Group 1: Usage numérique et préférence d'applications (Questions 1-2)
        if (isset($answers[1]) && isset($answers[2])) {
            $usage = $questions[0]["options"][$answers[1]];
            $app_pref = $questions[1]["options"][$answers[2]];

            if (strpos($usage, "Suivi d'entraînement") !== false && $app_pref === "Logiciels libres") {
                $advice[] = "Vous utilisez le numérique pour le suivi d'entraînement et privilégiez les logiciels libres : c'est un excellent choix pour votre vie privée ! Découvrez des applications comme Sports Tracker ou des outils open source qui respectent vos données.";
            } elseif (strpos($usage, "Suivi d'entraînement") !== false && $app_pref !== "Logiciels libres") {
                $advice[] = "Pour votre suivi d'entraînement, considérez passer aux alternatives libres pour une meilleure maîtrise de vos données personnelles.";
            } elseif ($app_pref === "Logiciels libres") {
                $advice[] = "Votre préférence pour les logiciels libres est louable ! La communauté NIRD peut vous aider à trouver des alternatives pour toutes vos activités sportives.";
            }
        }

        // Group 2: Sensibilité environnementale et préférences équipement (Questions 3-4)
        if (isset($answers[3]) && isset($answers[4])) {
            $impact = $questions[2]["options"][$answers[3]];
            $pref = $questions[3]["options"][$answers[4]];

            if (($impact === "Oui très" || $impact === "Un peu") && ($pref === "Durabilité" || $pref === "Réparabilité")) {
                $advice[] = "Votre sensibilité environnementale et votre préférence pour la durabilité/réparabilité vous rendent parfait pour la démarche NIRD ! Découvrez les ateliers de reconditionnement informatique dans les écoles.";
            } elseif ($impact === "Oui très" && $pref !== "Durabilité" && $pref !== "Réparabilité") {
                $advice[] = "Vous êtes sensible à l'impact environnemental : optez pour des équipements durables et réparables pour réduire l'obsolescence programmée.";
            }
        }

        // Group 3: Expérience reconditionnement et type d'outil (Questions 5-6)
        if (isset($answers[5]) && isset($answers[6])) {
            $recond = $questions[4]["options"][$answers[5]];
            $outil = $questions[5]["options"][$answers[6]];

            if (($recond === "Oui" || $recond === "Occasionnellement") && ($outil === "Montre/bracelet connecté" || $outil === "Smartphone")) {
                $advice[] = "Vous avez déjà expérimenté le reconditionnement et utilisez des équipements connectés : privilégiez les alternatives libres et open source pour une meilleure maîtrise de vos données.";
            } elseif ($recond === "Non mais pourquoi pas") {
                $advice[] = "Le reconditionnement est une excellente opportunité pour découvrir des équipements durables à moindre coût. La démarche NIRD peut vous guider dans cette voie.";
            }
        }

        // Group 4: Critères importants et alternatives libres (Questions 7-8)
        if (isset($answers[7]) && isset($answers[8])) {
            $critere = $questions[6]["options"][$answers[7]];
            $alt = $questions[7]["options"][$answers[8]];

            if (($critere === "Autonomie de l'appareil" || $critere === "Sobriété numérique") && ($alt === "Oui souvent" || $alt === "Oui une fois")) {
                $advice[] = "Votre attention à l'autonomie et la sobriété numérique, combinée à votre intérêt pour les alternatives libres, fait de vous un candidat idéal pour Linux ! Découvrez les distributions éducatives comme PrimTux.";
            } elseif ($alt === "Pas encore") {
                $advice[] = "Les alternatives libres offrent d'excellentes performances énergétiques et respectent mieux votre vie privée. N'hésitez pas à explorer cette voie.";
            }
        }

        // Group 5: Conseils utiles et recommandations (Questions 9-10)
        if (isset($answers[9]) && isset($answers[10])) {
            $conseil = $questions[8]["options"][$answers[9]];
            $reco = $questions[9]["options"][$answers[10]];

            if (($conseil === "Limiter l'impact environnemental" || $conseil === "Choisir des produits durables") && ($reco === "Oui" || $reco === "Oui pour débuter")) {
                $advice[] = "Votre intérêt pour la durabilité et les produits responsables vous rapproche de la démarche NIRD. Les recommandations de matériel durable sont disponibles sur le site, et les établissements pilotes peuvent vous accompagner.";
            } elseif ($reco === "Oui pour débuter") {
                $advice[] = "Pour débuter avec du matériel durable, contactez les établissements pilotes de la démarche NIRD qui proposent des ateliers de reconditionnement.";
            }
        }

        // Additional individual advice if no groups matched
        if (empty($advice)) {
            if (isset($answers[3]) && ($questions[2]["options"][$answers[3]] === "Oui très" || $questions[2]["options"][$answers[3]] === "Un peu")) {
                $advice[] = "Votre sensibilité à l'impact environnemental est un atout ! Découvrez les projets de reconditionnement informatique dans les écoles pour réduire l'obsolescence programmée.";
            }
            if (isset($answers[8]) && $questions[7]["options"][$answers[8]] === "Pas encore") {
                $advice[] = "Découvrez les alternatives libres pour vos activités sportives : elles respectent mieux votre vie privée et l'environnement.";
            }
        }

        if (empty($advice)) {
            $advice[] = "Merci d'avoir répondu au QCM ! Pour en savoir plus sur la démarche NIRD, visitez les différentes sections du site et rejoignez la communauté.";
        }

        return implode(" ", $advice);
    }

    private function generateSuggestions($answers, $questions) {
        $suggestions = [];

        // Question 1: Usage numérique
        if (isset($answers[1])) {
            $usage = $questions[0]["options"][$answers[1]];
            if (strpos($usage, "Suivi d’entraînement") !== false) {
                $suggestions[] = ['name' => 'Montres de sport', 'url' => 'https://www.decathlon.fr/search?Ntt=Montres+de+sport', 'description' => 'Pour suivre vos performances en temps réel.'];
            }
            if (strpos($usage, "Vidéos/coachings en ligne") !== false) {
                $suggestions[] = ['name' => 'Tapis de yoga', 'url' => 'https://www.decathlon.fr/search?Ntt=Tapis+de+yoga', 'description' => 'Idéal pour vos séances de relaxation et d\'étirement.'];
            }
        }

        // Question 3: Sensibilité environnementale
        if (isset($answers[3])) {
            $impact = $questions[2]["options"][$answers[3]];
            if ($impact === "Oui très" || $impact === "Un peu") {
                $suggestions[] = ['name' => 'Équipements durables', 'url' => 'https://www.decathlon.fr/search?Ntt=Équipements+durables', 'description' => 'Matériel sportif respectueux de l\'environnement.'];
            }
        }

        // Question 4: Préférences équipement
        if (isset($answers[4])) {
            $pref = $questions[3]["options"][$answers[4]];
            if ($pref === "Durabilité") {
                $suggestions[] = ['name' => 'Vêtements recyclés', 'url' => 'https://www.decathlon.fr/search?Ntt=Vêtements+recyclés', 'description' => 'Tenues sportives fabriquées à partir de matériaux recyclés.'];
            }
            if ($pref === "Réparabilité") {
                $suggestions[] = ['name' => 'Équipements modulaires', 'url' => 'https://www.decathlon.fr/search?Ntt=Équipements+modulaires', 'description' => 'Matériel facilement réparable et personnalisable.'];
            }
        }

        // Question 5: Expérience reconditionnement
        if (isset($answers[5])) {
            $recond = $questions[4]["options"][$answers[5]];
            if ($recond === "Oui" || $recond === "Occasionnellement") {
                $suggestions[] = ['name' => 'Produits reconditionnés', 'url' => 'https://www.decathlon.fr/search?Ntt=Produits+reconditionnés', 'description' => 'Équipements sportifs remis à neuf, économiques et écologiques.'];
            }
        }

        // Question 6: Type d'outil numérique
        if (isset($answers[6])) {
            $outil = $questions[5]["options"][$answers[6]];
            if ($outil === "Montre/bracelet connecté") {
                $suggestions[] = ['name' => 'Montres connectées', 'url' => 'https://www.decathlon.fr/search?Ntt=Montres+connectées', 'description' => 'Pour un suivi précis de vos activités sportives.'];
            }
            if ($outil === "Smartphone") {
                $suggestions[] = ['name' => 'Accessoires smartphone', 'url' => 'https://www.decathlon.fr/search?Ntt=Accessoires+smartphone', 'description' => 'Supports et protections pour vos entraînements.'];
            }
        }

        // Question 7: Critères importants
        if (isset($answers[7])) {
            $critere = $questions[6]["options"][$answers[7]];
            if ($critere === "Autonomie de l’appareil") {
                $suggestions[] = ['name' => 'Batteries externes', 'url' => 'https://www.decathlon.fr/search?Ntt=Batteries+externes', 'description' => 'Pour prolonger l\'autonomie de vos appareils.'];
            }
            if ($critere === "Robustesse") {
                $suggestions[] = ['name' => 'Équipements outdoor', 'url' => 'https://www.decathlon.fr/search?Ntt=Équipements+outdoor', 'description' => 'Matériel résistant pour toutes conditions.'];
            }
            if ($critere === "Légèreté") {
                $suggestions[] = ['name' => 'Vêtements techniques légers', 'url' => 'https://www.decathlon.fr/search?Ntt=Vêtements+techniques+légers', 'description' => 'Tenues légères et performantes.'];
            }
        }

        // Question 8: Alternatives libres
        if (isset($answers[8])) {
            $alt = $questions[7]["options"][$answers[8]];
            if ($alt === "Oui souvent" || $alt === "Oui une fois") {
                $suggestions[] = ['name' => 'Applications open source', 'url' => 'https://www.decathlon.fr/search?Ntt=Applications+open+source', 'description' => 'Outils numériques respectueux de votre vie privée.'];
            }
        }

        // Question 9: Conseils utiles
        if (isset($answers[9])) {
            $conseil = $questions[8]["options"][$answers[9]];
            if ($conseil === "Limiter l’impact environnemental") {
                $suggestions[] = ['name' => 'Produits écologiques', 'url' => 'https://www.decathlon.fr/search?Ntt=Produits+écologiques', 'description' => 'Équipements respectueux de l\'environnement.'];
            }
            if ($conseil === "Choisir des produits durables") {
                $suggestions[] = ['name' => 'Matériel durable', 'url' => 'https://www.decathlon.fr/search?Ntt=Matériel+durable', 'description' => 'Produits conçus pour durer dans le temps.'];
            }
        }

        // Question 10: Recommandations
        if (isset($answers[10])) {
            $reco = $questions[9]["options"][$answers[10]];
            if ($reco === "Oui" || $reco === "Oui pour débuter") {
                $suggestions[] = ['name' => 'Équipements polyvalents', 'url' => 'https://www.decathlon.fr/search?Ntt=Équipements+polyvalents', 'description' => 'Matériel adapté à plusieurs activités sportives.'];
            }
        }

        // Remove duplicates
        $uniqueSuggestions = [];
        foreach ($suggestions as $suggestion) {
            $key = $suggestion['name'];
            if (!isset($uniqueSuggestions[$key])) {
                $uniqueSuggestions[$key] = $suggestion;
            }
        }

        return array_values($uniqueSuggestions);
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
            $stmt = $this->pdo->prepare("INSERT INTO Reponses (id_utilisateur, id_question, reponse) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE reponse = VALUES(reponse)");
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
