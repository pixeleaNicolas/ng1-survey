(function($){
    

    function saveSurvey() {

        // Sérialiser le formulaire
        var formDataObj = new FormData($('#survey-form')[0]);
        var formData = $('#survey-form').serialize();
      
        // Convertir la chaîne de requête en un objet
        var formObject = {};
        $.each(formData.split('&'), function() {
          var pair = this.split('=');
          var name = decodeURIComponent(pair[0]);
          var value = decodeURIComponent(pair[1]);
          formObject[name] = value;
        });
      
        // Filtrer les champs commençant par "reponse_"
        var filteredData = {};
        for (var key in formObject) {
          if (formObject.hasOwnProperty(key) && key.startsWith('reponse_')) {
            filteredData[key] = formObject[key];
          }
        }

        // Appeler la fonction AJAX pour enregistrer le formulaire
        $.ajax({
          url: myAjax.ajaxurl, // Utilisation de la variable ajaxurl
          type: 'POST',
          dataType: 'json',
          data: {
            action: 'save_form', // Action WordPress pour la fonction save_form_ajax
            form_data: filteredData,
            identifier: formDataObj.get('identifier'),
            current: formDataObj.get('current')
          },
          success: function(response) {
            // Afficher le message de succès
           // $('.ng1-survey__loader').removeClass('active');
   
            console.log(response.data);
          },
          error: function(xhr, status, error) {
    
            console.log( status);
            // Afficher une erreur en cas d'échec
            console.log(error);
          }
        });
      }
      
      $(document).ready(function() {
        // Exécuter saveSurvey() lors du clic sur le bouton #saveSurvey
        $('#saveSurvey').click(function() {
          saveSurvey();
        });
      
        // Exécuter saveSurvey() lors de tout changement dans les boutons radio
        $('input[type="radio"]').change(function() {
  
          // Désélectionner tous les boutons radio du même groupe
          $('input[name="' + $(this).attr('name') + '"]').closest('.ng1-survey__radio-group').removeClass('checked');
          // Ajouter la classe "checked" au .ng1-survey__radio-group associé au bouton radio sélectionné
          $(this).closest('.ng1-survey__radio-group').addClass('checked');
          $('.ng1-survey__loader').addClass('active');
        
          $("#current").val($(this).data('index')+1);
          saveSurvey();
        });
        
      });
      
})(jQuery);