document.addEventListener('DOMContentLoaded', function() {
    loadQuestions();
});

function loadQuestions() {
    fetch('../php/QCM.php')
        .then(response => response.json())
        .then(data => {
            displayForm(data);
        })
        .catch(error => console.error('Error loading questions:', error));
}

function displayForm(questions) {
    let html = '<form id="qcmForm">';
    questions.forEach(question => {
        html += `<div class="question">
            <h4>${question.id}. ${question.question}</h4>`;
        question.options.forEach((option, index) => {
            html += `<label>
                <input type="radio" name="answers[${question.id}]" value="${index}" required>
                ${option}
            </label><br>`;
        });
        html += '</div>';
    });
    html += '<button type="submit">Soumettre</button></form>';
    document.getElementById('content').innerHTML = html;

    document.getElementById('qcmForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitAnswers(new FormData(this));
    });
}

function submitAnswers(formData) {
    fetch('../php/QCM.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        displayResult(data);
    })
    .catch(error => console.error('Error submitting answers:', error));
}

function displayResult(result) {
    let html = `<div class="result">
        <h3>Résultats</h3>
        <p>Vous avez obtenu ${result.score} sur ${result.total}.</p>
        <a href="#" onclick="loadQuestions()">Refaire le QCM</a>
    </div>`;
    document.getElementById('content').innerHTML = html;
}
