//document.getElementById('survey-form').addEventListener('submit', function(event) {
//    event.preventDefault(); // Empêche le comportement par défaut du formulaire
//
//    // Récupère les données du formulaire
//    var formData = new FormData(this);
//
//    // Convertit les données en JSON
//    var jsonData = JSON.stringify(Object.fromEntries(formData));
//
//    // Envoie les données JSON en utilisant AJAX
//    var xhr = new XMLHttpRequest();
//    xhr.open('POST', 'traitement_formulaire.php', true);
//    xhr.setRequestHeader('Content-Type', 'application/json');
//
//    xhr.onreadystatechange = function() {
//        if (xhr.readyState === 4 && xhr.status === 200) {
//            // Traitement après la réception de la réponse
//            console.log(xhr.responseText);
//        }
//    };
//
//    xhr.send(jsonData);
//});