<?php

extract(get_fields());
    ob_start();
    include("tpl-chart.php");
    $svg = ob_get_clean();
    ob_start();

  $args = array(
    'post_type' => 'resultat',
    'p' => $profil,
    'posts_per_page' => 1 // Nombre de posts à afficher
  );

  $resultats = get_posts($args);
      if ($resultats) {
        foreach ($resultats as $resultat) {?>
       <h3> <?php the_title(); ?></h3>
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
     }
    }