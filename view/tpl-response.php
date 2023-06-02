<?php 

$data= json_decode(urldecode($form_data),true);
$questions=$data['questions'];

$i = 0;
$previous_category_name = '';

foreach ($questions as $question) {
    $i++;
    ?>
    
   <?php
    foreach ($question['categorie'] as $category_id) {
        $category = get_term_by('id', $category_id, 'categorie_question');
        // Vérifier si le terme a été trouvé
        if ($category) {
            // Récupérer le nom de la catégorie
            $category_name = $category->name;
            
            // Vérifier si la catégorie est différente de l'occurrence précédente
            if ($category_name !== $previous_category_name) {
                ?>
                <div class="g1-survey__item ng1-survey__item_categorie">
                    <?php echo $category_name; ?>
                </div>
                <div class="ng1-survey__item">
                <?php
            }else{?>
                <div class="ng1-survey__item">
                    <?php
            }
            
            // Mettre à jour la variable $previous_category_name
            $previous_category_name = $category_name;
        }
    }
    ?>

    <div class="ng1-survey__question">
        <small><?php echo $i; ?>.</small> <?php echo $question['question'] ?>
    </div>
    <div class="ng1-survey__reponses">
        <?php Ng1SondagePlugin::convertTextAreaToRadioButtons( $question['reponse'],"reponse_".$i,$i,$category_name,${"reponse_".$i},true); ?>
    </div>

</div>

<?php
}
