<?php 


if(!empty($nbpoints)): ?>
    <?php
    $points_cat=array();
    $resultat_par_categorie =$nbpoints;
    if(!is_array($nbpoints)){
        $resultat_par_categorie = json_decode(stripslashes(urldecode($nbpoints)),true); 
    }

    foreach ($resultat_par_categorie as $key=>$value) { 
        $category = get_term_by('id', $key, 'categorie_question');
            // Vérifier si le terme a été trouvé
            if ($category) {
                // Récupérer le nom de la catégorie
                $category_name =  Ng1SondagePlugin::enleverAvantTiret($category->name);
                $points_cat[$category_name] =$value;
            }
    } ?>
    <div class="ng1-survey__chart"> 
       <pre><?php var_dump( $resultat_par_categorie); ?></pre>
        <?php echo $svgChart = Ng1SondagePlugin::generateSpiderChart($points_cat); ?>
    </div>  
    <?php endif;?>  