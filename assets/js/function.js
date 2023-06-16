(function($){

  function validateRadioGroups(formId) {
    var form = $('#' + formId);
    var radioGroups = form.find('input[type="radio"][name]');
    var incompleteGroups = [];
  
    radioGroups.each(function() {
      var groupName = $(this).attr('name');
      var groupRadios = form.find('input[type="radio"][name="' + groupName + '"]');
      var isGroupChecked = groupRadios.is(':checked');
  
      if (!isGroupChecked) {
        incompleteGroups.push(groupName);
      }
    });
  
    return incompleteGroups;
  }
  
  
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
      function removeParamsExceptId(url, idParam="id") {
        var urlObject = new URL(url);
        var searchParams = new URLSearchParams(urlObject.search);
      
        // Supprimer tous les paramètres, sauf le paramètre spécifié 'id'
        searchParams.forEach(function(value, key) {
          if (key !== idParam) {
            searchParams.delete(key);
          }
        });
      
        // Mettre à jour l'URL avec les paramètres modifiés
        urlObject.search = searchParams.toString();
        
        return urlObject.toString();
      }
      function goBackCorrection(index) {

          var previousURL = removeParamsExceptId(document.referrer); // Récupérer l'URL de la page précédente
          var separator = previousURL.includes('?') ? '&' : '?';
          var data = 'index=' + index;
          var updatedURL = previousURL + separator + data; // Ajouter les données en tant que paramètres d'URL
          history.replaceState({}, '', updatedURL); // Modifier l'URL sans recharger la page

          window.location.href = previousURL; // Naviguer vers la page précédente

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
        var groupCount = {};
        // Objet pour suivre le nombre de boutons radio cochés par groupe
        $('.ng1-survey__message').hide();
      
        $('input[type="radio"]').change(function() {
          updateGroupCount();
          checkAllGroupsChecked();
          console.log(groupCount);
        });
      
        function updateGroupCount() {
          groupCount = {};
      
          $('input[type="radio"]').each(function() {
            var groupName = $(this).attr('name');
            var checkedCount = $('input[name="' + groupName + '"]:checked').length;
            groupCount[groupName] = checkedCount;
          });
        }
      
        function checkAllGroupsChecked() {
          var totalGroups = Object.keys(groupCount).length;
          var allGroupsChecked = true;
      
          $.each(groupCount, function(key, value) {
            if (value === 0) {
              allGroupsChecked = false;
              return false; // Sortir de la boucle each
            }
          });
      
          console.log(allGroupsChecked + " " + totalGroups);
      
          if (allGroupsChecked && totalGroups > 0) {
            // Tous les groupes de boutons radio sont cochés
            $('.ng1-survey__message').show();
          } else {
            // Au moins un groupe de boutons radio n'est pas cochée
            $('.ng1-survey__message').hide();
          }
        }
      
        function countAndDisplayElements() {
          var totalGroups = Object.keys(groupCount).length;
          var totalChecked = 0;
      
          $.each(groupCount, function(key, value) {
            totalChecked += value;
          });
      
          console.log("Total Groups: " + totalGroups);
          console.log("Total Checked: " + totalChecked);
      
          // Afficher l'élément correspondant ici (exemple)
          if (totalGroups === totalChecked) {
            $('#elementToDisplay').show();
          } else {
            $('#elementToDisplay').hide();
          }
        }
      
        updateGroupCount();
        checkAllGroupsChecked();
        countAndDisplayElements();
      });
      
      
      
      
      

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
        $('body').on('click','.formWitAutoNextJs input[type="radio"]', function() {
          var self = this;
          setTimeout(function() {
            showNextQuestion.call(self);
            $('.ng1-survey__loader').removeClass('active');
          },1000); // 3 secondes
         
        });
    
        // Ajouter le bouton de la question précédente
        $('.formWitAutoNextPreviousJs').click(showPreviousQuestion);
        $('.formWitAutoNextNextJs').click(showNextQuestion);
        var pos=$('#current').val();
        if(pos >1){
          goToQuestion(pos);
        }

        $(document).ready(function() {
          $('.goToQuestionJs').on('click', function () {
            goToQuestion($(this).data('index')); 
          });
          $('.goBackCorrectionJs').on('click', function () {
            goBackCorrection($(this).data('index')); 
          });
            $('#survey-form').submit(function(event) {
         
            // event.preventDefault(); // Empêche le formulaire de se soumettre normalement
            // var actionValue = $('.form-action').filter(':focus').data('action');

            // if (actionValue) {
            //     $(this).attr('action', actionValue);
            // } else {
            //     // Action par défaut si aucun bouton n'est sélectionné
            //     $(this).attr('action', 'defaultAction.php');
            // }
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
       
              var incompleteGroups = validateRadioGroups('survey-form'); 
              if (incompleteGroups.length > 0) {
                event.preventDefault();
                var errorMessage = 'Veuillez sélectionner une option pour les groupes suivants : ' + incompleteGroups.join(', ');
                var index = incompleteGroups[0].replace('reponse_','');
                goToQuestion(index);
              }else{
                $('#survey-form').unbind('submit').submit();
              }
            
            });
          });
          
    });

    
})(jQuery);