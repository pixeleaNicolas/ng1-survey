<?php 
extract($_POST);
if($_POST && isset($nbpoints) && !empty($nbpoints)):
  $profil =  Ng1SondagePlugin::getLettreMaxSomme(Ng1SondagePlugin::inputDecode($reponses));
 //$resultat = Ng1SondagePlugin::sommeTableau($nbpoints);

    ob_start();

   include("tpl-chart.php");
    $svg = ob_get_clean();
    $profil_class= "square";
    ob_start();
    switch ($profil) {
      case ('A'):
        $post_id = 16575;
        $profil_class= "square";
        break;
      case ('B'):
        $post_id = 16576;
        $profil_class= "circle";
        break;
        case ('C'):
          $post_id = 16577;
          $profil_class= "triangle";
          break;

  }
  $profil_icon = "<div class='profil_".$profil_class."'></div>";

    $args = array(
        'post_type' => 'resultat',
        'p' => $post_id,
        'posts_per_page' => 1 // Nombre de posts à afficher
      );

      $resultats = get_posts($args);

      if ($resultats) {
        foreach ($resultats as $resultat) {
          echo '<H1>Profil : '.$profil.'</H1>';
         echo $profil_icon;
         echo $content = apply_filters('the_content',$resultat->post_content);
         echo $svg;
         
         echo $result = ob_get_clean(); 
         
      $post_data=array();
      $post_data=$_POST;
      $post_data['content']=$content;

      if ( isset( $_POST['identifier'] ) && Ng1SondagePlugin::isTokenExists($_POST['identifier']) ) {
          $id_post_to_update= Ng1SondagePlugin::response_is_already_in_db($_POST['identifier']);
          Ng1SondagePlugin::acf_save_survey_reponse($id_post_to_update,$post_data);// Vérifier le nonce
      } else {
        echo "Le nonce est invalide, gérer l'erreur";
      
      }
     }
      }
          
          ?>
<?php else: ?>

<p>Votre nombre de points ne permet pas de donner de résultats.</p>
<?php
endif;