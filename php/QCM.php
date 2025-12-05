<?php
session_start();
require_once 'PDO.php';
require_once 'controller.php';

$controller = new QCMController();
$result = $controller->handleRequest();
$questions = $controller->getQuestions();

include 'header.php';
?>

<main>
    <h2>QCM sur la Démarche NIRD</h2>
    <?php if (!$controller->isLoggedIn()): ?>
        <div class="login-section">
            <h3>Identification requise</h3>
            <p>Avant de commencer le QCM, veuillez vous connecter ou vous inscrire.</p>
            <p style="color: red; font-size: 0.9em; font-weight: bold;">Disclaimer : Ce QCM est à des fins éducatives uniquement. Les données collectées (nom et mot de passe) sont stockées à des fins de démonstration et ne sont pas destinées à un usage réel. Veuillez ne pas utiliser d'informations personnelles réelles. Utilisez des identifiants fictifs pour protéger votre vie privée. Les organisateurs et toutes autres personnes ne sont pas responsables des données fournit, car il a bien été stipulé d'utiliser de faux identifiants à des fins éducatives.</p>
            <form method="post" action="QCM.php">
                <label>Nom: <input type="text" name="nom" required></label><br>
                <label>Mot de passe: <input type="password" name="password" required></label><br>
                <button type="submit" name="login">Se connecter</button>
                <button type="submit" name="register">S'inscrire</button>
            </form>
        </div>
    <?php else: ?>
        <p>Connecté en tant que: <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Utilisateur'); ?> <a href="?logout=1">Se déconnecter</a></p>

        <?php if ($result && isset($result['advice'])): ?>
            <div class="result">
                <h3>Conseils : </h3>
                <div class="advice">
                    <h4>Conseils personnalisés :</h4>
                    <p><?php echo $result['advice']; ?></p>
                </div>
                <a href="QCM.php">Refaire le QCM</a>
            </div>
        <?php else: ?>
            <form method="post" action="QCM.php">
                <?php foreach ($questions as $question): ?>
                    <div class="question">
                        <h4><?php echo $question['rowid'] . '. ' . $question['question']; ?></h4>
                        <?php foreach ($question['options'] as $optionIndex => $option): ?>
                            <label>
                                <input type="radio" name="answers[<?php echo $question['rowid']; ?>]" value="<?php echo $optionIndex; ?>" required>
                                <?php echo $option; ?>
                            </label><br>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
                <button type="submit">Soumettre</button>
            </form>
        <?php endif; ?>
    <?php endif; ?>
</main>

<?php
include 'footer.php';
?>
