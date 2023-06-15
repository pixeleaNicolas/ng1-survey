<?php 
extract($_POST);

$reponses_et_questions = json_decode(Ng1SondagePlugin::inputDecode($all_questions),true);
ob_start();
//------------- REPONSE POUR PDF -----------
?>
<div class='pdf-resultat__items'>
    <?php
    $i = 0;
    $previous_category_name = '';
    
    foreach ($reponses_et_questions as $question):
        $i++;
       foreach ($question['categorie'] as $category_id): ?>
            <?php
            $category = get_term_by('id', $category_id, 'categorie_question');
            // Vérifier si le terme a été trouvé
            if ($category): ?>
            <?php
                // Récupérer le nom de la catégorie
                $category_name = $category->name;
                // Vérifier si la catégorie est différente de l'occurrence précédente
                if ($category_name !== $previous_category_name) : ?>
                    <div class="pdf-resultat__item">
                        <h1 class="pdf-resultat__item__categorie">  <?php echo $category_name; ?></h1>
                    </div>
                    <div class="pdf-resultat__item">
                <?php else: ?>
                    <div class="pdf-resultat__item">
                <?php endif; ?>
                <?php  $previous_category_name = $category_name; ?>
            <?php endif;?>
            <?php endforeach;?>

    
        <h2 class="pdf-resultat__question"><strong><?php echo $i; ?>.</strong> <?php echo $question['question'] ?></h2>
        <div class="pdf-resultat__reponses">
            <?php Ng1SondagePlugin::convertTextAreaToReponses( $question['reponse'],"reponse_".$i,$i,$category_name,${"reponse_".$i},true); ?>
        </div>
    </div>
    <?php endforeach;?>
</div>
<?php $reponse_html  = ob_get_clean(); 
//------------- __FIN__-----------


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
        foreach ($resultats as $resultat) {?>
         <H1>Profil : <?php echo $profil;?></H1>
         <?php echo $profil_icon;?>
         <div class='resultat__cols'>
         <div class='resultat__col'>
         <?php echo $content = apply_filters('the_content',$resultat->post_content); ?>
         </div>
         <div class='resultat__col'>
         <?php echo $svg; ?>
         </div>
        </div>
         <?php
         echo $result = ob_get_clean(); 
         
        $post_data=array();
        $post_data=$_POST;
        $post_data['content']=$content;
        
      if ( isset( $_POST['identifier'] ) && Ng1SondagePlugin::isTokenExists($_POST['identifier']) ) {
            $id_post_to_update= Ng1SondagePlugin::response_is_already_in_db($_POST['identifier']);
            $post_data['reponse_html']=$reponse_html;
            Ng1SondagePlugin::acf_save_survey_reponse($id_post_to_update,$post_data);
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