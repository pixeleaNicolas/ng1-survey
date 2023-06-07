(function($){
    function countReponsesParCategorie(data) {
        var countByCategorie = {};
      
        data.forEach(function(item) {
          var categorie = item.categorie;
          var reponse = item.reponse;
      
          if (!countByCategorie[categorie]) {
            countByCategorie[categorie] = {
              A: 0,
              B: 0,
              C: 0
            };
          }
      
          countByCategorie[categorie][reponse]++;
        });
      
        return countByCategorie;
      }

      function calculatePointsByCategorie(data) {
        var pointsByCategorie = {};
      
        data.forEach(function(item) {
          var categorie = item.categorie;
          var reponse = item.reponse;
      
          if (!pointsByCategorie[categorie]) {
            pointsByCategorie[categorie] = 0;
          }
      
          if (reponse === "A") {
            pointsByCategorie[categorie] += 1;
          } else if (reponse === "B") {
            pointsByCategorie[categorie] += 2;
          } else if (reponse === "C") {
            pointsByCategorie[categorie] += 3;
          }
        });
      
        return pointsByCategorie;
      }
      
      
    $(document).ready(function() {

        var currentQuestion = 1; // Numéro de la question actuelle
        var totalQuestions = $('.formWitAutoNextJs > div').length; // Nombre total de questions
       // Masquer toutes les questions sauf la première
       $('.formWitAutoNextJs > div').hide();
       $('.formWitAutoNextJs > div:first-child').show();
        // Fonction pour afficher la question suivante
        function showNextQuestion() {
            var currentDiv = $('.formWitAutoNextJs > div').eq(currentQuestion - 1); // Div de la question actuelle
            currentDiv.hide(); // Masquer la question actuelle
    
            currentQuestion++; // Passer à la question suivante
    
            if (currentQuestion <= totalQuestions) {
                var nextDiv = $('.formWitAutoNextJs > div').eq(currentQuestion - 1); // Div de la question suivante
                nextDiv.show(); // Afficher la question suivante
                $('#survey-form').attr('data-current', currentQuestion); // Mettre à jour l'attribut data-current
            }
        }
    
        // Fonction pour afficher la question précédente
        function showPreviousQuestion() {
            if (currentQuestion > 1) {
                var currentDiv = $('.formWitAutoNextJs > div').eq(currentQuestion - 1); // Div de la question actuelle
                currentDiv.hide(); // Masquer la question actuelle
    
                currentQuestion--; // Passer à la question précédente
    
                var previousDiv = $('.formWitAutoNextJs > div').eq(currentQuestion - 1); // Div de la question précédente
                previousDiv.show(); // Afficher la question précédente
                $('#survey-form').attr('data-current', currentQuestion); // Mettre à jour l'attribut data-current
            }
        }
        function goToQuestion(questionNumber) {
          var totalQuestions = $('.formWitAutoNextJs > div').length;
        
          if (questionNumber >= 1 && questionNumber <= totalQuestions) {
            var currentDiv = $('.formWitAutoNextJs > div').eq(currentQuestion - 1); // Div de la question actuelle
            currentDiv.hide(); // Masquer la question actuelle
        
            currentQuestion = questionNumber; // Passer à la question spécifiée
        
            var targetDiv = $('.formWitAutoNextJs > div').eq(currentQuestion - 1); // Div de la question cible
            targetDiv.show(); // Afficher la question cible
            $('#survey-form').attr('data-current', currentQuestion); // Mettre à jour l'attribut data-current
          }
        }
        
        // Écouteur d'événement pour les boutons radio
        $('.formWitAutoNextJs input[type="radio"]').on('click', showNextQuestion);
    
        // Ajouter le bouton de la question précédente
        $('.formWitAutoNextPreviousJs').click(showPreviousQuestion);
        $('.formWitAutoNextNextJs').click(showNextQuestion);
        var pos=$('#current').val();
        if(pos >1){
          goToQuestion(pos);
        }

        $(document).ready(function() {
            $('#survey-form').submit(function(event) {
         
              event.preventDefault(); // Empêche le formulaire de se soumettre normalement
              var actionValue = $('.form-action').filter(':focus').data('action');

              if (actionValue) {
                  $(this).attr('action', actionValue);
              } else {
                  // Action par défaut si aucun bouton n'est sélectionné
                  $(this).attr('action', 'defaultAction.php');
              }
              var reponseValues = [];
              $('input[type="radio"][name^="reponse_"]:checked').each(function() {
                var question_number = $(this).data('index');
                var reponse = $(this).val();
                var categorie = $(this).data('cat');
                var reponseAvecCategorie = reponse + ' (' + categorie + ')';
                console.log(reponseAvecCategorie);
              
                reponseValues.push({
                  question: question_number,
                  categorie: categorie,
                  reponse: reponse
                });
              });
          
              var reponseField = $('#reponses');
              reponseField.val(encodeURIComponent(JSON.stringify(countReponsesParCategorie(reponseValues))));
              var nbPoints= $('#nbpoints');
              nbPoints.val(encodeURIComponent(JSON.stringify( calculatePointsByCategorie(reponseValues))));
              // Vous pouvez également soumettre le formulaire ici si nécessaire
              //debugger;

              $('#survey-form').unbind('submit').submit();
            });
          });
          
    });

    
})(jQuery);