<?php 
function sommeTableau($tableau) {
    $somme = 0;
    
    foreach ($tableau as $value) {
      $somme += $value;
    }
    
    return $somme;
  }



  
extract($_POST);
if($_POST && isset($nbpoints) && !empty($nbpoints)):

    $decodedString =strval(urldecode(mb_convert_encoding($nbpoints, 'UTF-8')));

$nbpoints= json_decode($decodedString,true);

    ob_start();
        include("tpl-chart.php");
    $svg = ob_get_clean();
    ob_start();



    $resultat = sommeTableau($nbpoints);
    switch ($resultat) {
        case ($resultat < 32):
          $post_id = 16575;
          break;
        case ($resultat > 32 && $resultat <= 75):
          $post_id = 16576;
          break;
        default:
          $post_id = 16577;
          break;
      }

    $args = array(
        'post_type' => 'resultat',
        'p' => $post_id,
        'posts_per_page' => 1 // Nombre de posts à afficher
      );

      $resultats = get_posts($args);

      if ($resultats) {
        foreach ($resultats as $resultat) {
        
         echo apply_filters('the_content',$resultat->post_content);
         echo $svg;
         echo $result = ob_get_clean(); 
        }
      } ?>
<?php else: ?>

<p>Votre nombre de points ne permet pas de donner de résultats.</p>
<?php
endif;