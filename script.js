let budget, eco, auto, tours;
const maxTours = 5;

// --- Scores initiaux ---
function initGame() {
    budget = 75;
    eco = 50;
    auto = 50;
    tours = 0;
    updateBars();
    document.getElementById("message").innerHTML = "";
    document.getElementById("badge").innerHTML = "";
    document.getElementById("restartBtn").style.display = "none";
    showChoices();
}

// --- Liste des décisions (50 choix) ---
const decisions = [
    { text: "💻 Installer Linux + LibreOffice", budget: -10, eco: +30, auto: +40, msg: "Tu te libère du tyran Microsoft 🧙‍♂️" },
    { text: "🪟 Acheter Windows + Microsoft 365", budget: -40, eco: -35, auto: -50, msg: "Licence obligatoire = dépendance éternelle 💸" },
    { text: "🔧 Réparer les PC en panne", budget: -5, eco: +40, auto: +20, msg: "Recycler, c'est régner ♻️👑" },
    { text: "🖥️ Acheter du matériel neuf", budget: -35, eco: -45, auto: -10, msg: "Hardware flambant neuf 💀 déchets gratuits" },
    { text: "📚 Utiliser Moodle (Libre)", budget: -5, eco: +20, auto: +30, msg: "Bravo ! L'éducation reste libre 📘💙" },
    { text: "📡 Passer à Google Classroom", budget: -10, eco: -15, auto: -40, msg: "Tu offres tes données élèves à Google 😱" },
    { text: "☁️ Héberger un Nextcloud local", budget: -8, eco: +25, auto: +35, msg: "Autonomie + données protégées 🔐" },
    { text: "🌍 Utiliser OneDrive/Dropbox scolaires", budget: -15, eco: -20, auto: -35, msg: "Tes données deviennent la propriété du cloud 🕵️‍♂️" },
    { text: "🛠️ Monter un atelier réparation avec élèves", budget: -10, eco: +35, auto: +25, msg: "Ils apprennent et recyclent ! 🏫" },
    { text: "📦 Acheter du matériel reconditionné", budget: -15, eco: +30, auto: +20, msg: "Moins de déchets, même performance ♻️" },
    { text: "💾 Installer une distribution Linux éducative", budget: -20, eco: +40, auto: +35, msg: "Autonomie garantie et durable 🖥️" },
    { text: "🏫 Former les enseignants à l'open source", budget: -5, eco: +10, auto: +40, msg: "Les enseignants deviennent autonomes 📘" },
    { text: "💻 Installer des Linux légers sur anciens PC", budget: -10, eco: +35, auto: +25, msg: "Du vieux PC reboosté ! 🚀" },
    { text: "🖥️ Installer des PC gamers neufs pour tous", budget: -50, eco: -45, auto: -15, msg: "Coût énorme et énergie gaspillée 🎮" },
    { text: "🌐 Utiliser une solution cloud privée locale", budget: -15, eco: +25, auto: +35, msg: "Autonomie et sécurité garanties 🔐" },
    { text: "📚 Proposer des logiciels libres pédagogiques", budget: -10, eco: +30, auto: +30, msg: "Enseignement libre et efficace 📘" },
    { text: "🌍 Installer un serveur local pour internet", budget: -10, eco: +35, auto: +40, msg: "Indépendance internet 🌐" },
    { text: "🖥️ Migrer tous les documents vers Linux", budget: -10, eco: +25, auto: +30, msg: "Autonomie et open source 📂" },
    { text: "📱 Fournir tablettes Android libres", budget: -10, eco: +20, auto: +30, msg: "Éducation numérique sans dépendance 📱" },
    { text: "🧑‍💻 Former élèves au code open source", budget: -5, eco: +10, auto: +40, msg: "Autonomie numérique garantie 👩‍💻" },
    { text: "📚 Mettre en place Moodle et Nextcloud", budget: -10, eco: +30, auto: +35, msg: "Environnement libre complet 📘" },
    { text: "🔧 Atelier maintenance informatique", budget: -10, eco: +25, auto: +20, msg: "Réparer et apprendre 💪" },
    { text: "🌐 Installer un VPN interne", budget: -5, eco: +10, auto: +25, msg: "Sécurité et autonomie 🔐" },
    { text: "🖥️ Installer des Linux éducatifs sur tablettes", budget: -10, eco: +30, auto: +25, msg: "Autonomie et éducation libre 📚" },
    { text: "🖥️ Installer des postes virtuels Linux", budget: -10, eco: +35, auto: +30, msg: "Moins de matériel, plus de liberté 💻" },
    { text: "📚 Créer une bibliothèque de logiciels libres", budget: -5, eco: +15, auto: +25, msg: "Accès facilité et liberté numérique 📘" },
];

// --- Affiche 2 choix aléatoires ---
function showChoices() {
    if (tours >= maxTours || budget <= 0 || budget >= 100) return endGame();

    const container = document.getElementById("choices");
    container.innerHTML = "";

    let a = decisions[Math.floor(Math.random() * decisions.length)];
    let b;
    do { b = decisions[Math.floor(Math.random() * decisions.length)]; } while (b === a);

    [a, b].forEach(d => {
        let btn = document.createElement("button");
        btn.className = "choice-btn";
        btn.textContent = d.text;
        btn.onclick = () => applyChoice(d);
        container.appendChild(btn);
    });
}

// --- Appliquer le choix ---
function applyChoice(d) {
    budget = Math.min(100, Math.max(0, budget + d.budget));
    eco = Math.min(100, Math.max(0, eco + d.eco));
    auto = Math.min(100, Math.max(0, auto + d.auto));

    document.getElementById("message").innerHTML = d.msg;
    tours++;
    updateBars();

    // Stop si budget dépassé ou épuisé
    if (budget <= 0 || budget >= 100 || tours >= maxTours) {
        endGame();
    } else {
        showChoices();
    }
}

// --- Met à jour les jauges ---
function updateBars() {
    document.getElementById("budgetFill").style.width = budget + "%";
    document.getElementById("ecoFill").style.width = eco + "%";
    document.getElementById("autoFill").style.width = auto + "%";
}

// --- Fin du jeu ---
function endGame() {
    let score = budget + eco + auto;
    let badgeText = "";

    if (score > 240) badgeText = "🧙‍♂️ Grand Druide du Libre !";
    else if (score > 180) badgeText = "🛡️ Irréductible gaulois codeur !";
    else if (score > 120) badgeText = "😬 Prisonnier de Microsoft...";
    else badgeText = "💀 Esclave de la Big Tech !";

    badgeText += ` | Score total : ${score} pts`;
    document.getElementById("badge").innerHTML = badgeText;
    document.getElementById("choices").innerHTML = "";
    document.getElementById("restartBtn").style.display = "inline-block";
}

// --- Restart ---
function restartGame() {
    initGame();
}

// --- Démarrer ---
initGame();
