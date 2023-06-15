<div class="cols">
<div class="cols_col">
<?php
// Récupérer l'utilisateur courant
$current_user = wp_get_current_user();

$msg_quizz = false;


// Arguments de requête pour récupérer les posts du type "réponse" de l'utilisateur courant
$args = array(
    'post_type'      => 'reponse',
    'author'         => $current_user->ID,
    'posts_per_page' => 1, // Récupérer tous les posts
    'post_status' =>'draft',
);

// Exécution de la requête
$query = new WP_Query($args);

// Vérifier si des posts ont été trouvés
if ($query->have_posts()) {
    $msg_quizz = true;
    // Afficher l'en-tête du tableau
?>
<h2>Forumulaire en cours</h2>
<div class="quizz-resultat__items">
<?php
    // Boucle sur les posts trouvés
    while ($query->have_posts()) {
        $query->the_post();
        $post_id = get_the_ID();

        ?>
        <div class="quizz-resultat__item">
        <a href="/formulaire/?id=<?php echo $post_id; ?>">Continuer mon quizz</a>
<a href="/formulaire/?id=<?php echo $post_id; ?>"><svg xmlns="http://www.w3.org/2000/svg" width="30" height="29.085" viewBox="0 0 30 29.085">
  <path id="prev_next" d="M13.052,28.428.657,16.081A1.774,1.774,0,0,1,.164,15.4,2.259,2.259,0,0,1,0,14.531a1.97,1.97,0,0,1,.164-.8,1.82,1.82,0,0,1,.493-.657L13.1.588A1.964,1.964,0,0,1,14.6,0a2.048,2.048,0,0,1,1.5.681,2.224,2.224,0,0,1,.587,1.5,1.964,1.964,0,0,1-.587,1.5L7.371,12.419H27.887A2.085,2.085,0,0,1,30,14.531a2.05,2.05,0,0,1-2.113,2.16H7.371L16.15,25.47A2.085,2.085,0,0,1,16.737,27a1.939,1.939,0,0,1-.634,1.432,2.119,2.119,0,0,1-1.549.657A1.97,1.97,0,0,1,13.052,28.428Z" transform="translate(30 29.085) rotate(180)" fill="#00acaf"/>
</svg>
</a>
</div>
        <?php


    }
?>
</div>
<?php

    // Réinitialiser la requête principale de WordPress
    wp_reset_postdata();
}
// Arguments de requête pour récupérer les posts du type "réponse" de l'utilisateur courant
$args = array(
    'post_type'      => 'reponse',
    'author'         => $current_user->ID,
    'posts_per_page' => -1, // Récupérer tous les posts
);

// Exécution de la requête
$query = new WP_Query($args);

// Vérifier si des posts ont été trouvés
if ($query->have_posts()) {
    $msg_quizz = true;
    ?>
    <h2>Mes Resultats</h2>
    <div class="quizz-resultat__items">
    <?php
 while ($query->have_posts()) :
     $query->the_post();
     $post_id = get_the_ID();?>
 <div class="quizz-resultat__item">
<a href='<?php the_permalink() ?>'>
Resultat du <?php  echo get_the_date("d/m/Y h:i",get_the_ID()); ?>

</a>
<?php 
$pdf= get_field('resultat_pdf');
if(!empty($fields)) extract($fields);
?>
<?php if(!empty($pdf)):?>
<a title="Télécharger le pdf" href="<?php echo wp_get_attachment_url($pdf); ?>" download><svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 30 30">
  <path id="picture_as_pdf_FILL0_wght400_GRAD0_opsz48" d="M90.8-866.275h1.8a1.342,1.342,0,0,0,.989-.4,1.342,1.342,0,0,0,.4-.989v-1.8a1.342,1.342,0,0,0-.4-.989,1.342,1.342,0,0,0-.989-.4H90.125a.733.733,0,0,0-.5.188.636.636,0,0,0-.214.5v6.307a.635.635,0,0,0,.214.5.732.732,0,0,0,.5.188.618.618,0,0,0,.488-.214.733.733,0,0,0,.188-.5Zm0-1.388v-1.8h1.8v1.8Zm7.988,4.5a1.355,1.355,0,0,0,.975-.4,1.32,1.32,0,0,0,.412-.989v-4.912a1.32,1.32,0,0,0-.412-.989,1.356,1.356,0,0,0-.975-.4H96.35a.733.733,0,0,0-.5.188.634.634,0,0,0-.214.5v6.273a.693.693,0,0,0,.214.52.7.7,0,0,0,.5.206Zm-1.762-1.387v-4.912h1.762v4.912Zm6.375-1.725h1.2a.618.618,0,0,0,.488-.214.733.733,0,0,0,.188-.5.66.66,0,0,0-.187-.488.66.66,0,0,0-.488-.187h-1.2v-1.8h1.2a.618.618,0,0,0,.488-.214.733.733,0,0,0,.188-.5.66.66,0,0,0-.187-.488.66.66,0,0,0-.488-.187h-1.875a.733.733,0,0,0-.5.188.636.636,0,0,0-.214.5v6.307a.635.635,0,0,0,.214.5.732.732,0,0,0,.5.188.618.618,0,0,0,.488-.214.733.733,0,0,0,.188-.5ZM86.75-854.5a2.159,2.159,0,0,1-1.575-.675,2.158,2.158,0,0,1-.675-1.575v-21a2.159,2.159,0,0,1,.675-1.575A2.159,2.159,0,0,1,86.75-880h21a2.159,2.159,0,0,1,1.575.675A2.159,2.159,0,0,1,110-877.75v21a2.158,2.158,0,0,1-.675,1.575,2.159,2.159,0,0,1-1.575.675Zm0-2.25h21v-21h-21ZM82.25-850a2.159,2.159,0,0,1-1.575-.675A2.158,2.158,0,0,1,80-852.25v-22.125a1.085,1.085,0,0,1,.325-.8,1.1,1.1,0,0,1,.806-.323,1.076,1.076,0,0,1,.8.323,1.1,1.1,0,0,1,.319.8v22.125h22.125a1.085,1.085,0,0,1,.8.325,1.1,1.1,0,0,1,.323.806,1.076,1.076,0,0,1-.323.8,1.1,1.1,0,0,1-.8.319Zm4.5-27.75v0Z" transform="translate(-80 880)" fill="#004b54"/>
</svg>
<?php endif; ?>
</a>
<a href='<?php the_permalink() ?>'><svg xmlns="http://www.w3.org/2000/svg" width="30" height="29.085" viewBox="0 0 30 29.085">
  <path id="prev_next" d="M13.052,28.428.657,16.081A1.774,1.774,0,0,1,.164,15.4,2.259,2.259,0,0,1,0,14.531a1.97,1.97,0,0,1,.164-.8,1.82,1.82,0,0,1,.493-.657L13.1.588A1.964,1.964,0,0,1,14.6,0a2.048,2.048,0,0,1,1.5.681,2.224,2.224,0,0,1,.587,1.5,1.964,1.964,0,0,1-.587,1.5L7.371,12.419H27.887A2.085,2.085,0,0,1,30,14.531a2.05,2.05,0,0,1-2.113,2.16H7.371L16.15,25.47A2.085,2.085,0,0,1,16.737,27a1.939,1.939,0,0,1-.634,1.432,2.119,2.119,0,0,1-1.549.657A1.97,1.97,0,0,1,13.052,28.428Z" transform="translate(30 29.085) rotate(180)" fill="#00acaf"/>
</svg>
</a>
</div>
<?php  endwhile; wp_reset_postdata(); ?>
 </div>
<?php
    wp_reset_postdata();
} ?>
<?php
if(empty($msg_quizz)):
    ?>
    <div class="msg_quizz">
    <h2>Vous n'avez saisie aucun Quiz</h2>
<p>Nous vous invitons à prendre quelques instants pour remplir notre quiz sur la Qualité de Vie au Travail (QVT).</p>
<p> En répondant à ces questions, vous pourrez évaluer différents aspects de votre environnement professionnel et identifier des pistes d'amélioration.</p>
<p>C'est une occasion de réfléchir à votre situation actuelle et de recevoir des recommandations personnalisées pour améliorer votre bien-être au travail. </p>
<p>Ne tardez pas, saisissez cette opportunité pour investir dans votre qualité de vie professionnelle. Remplissez le quiz dès maintenant !</p>
</div>
    <?php
endif;
?>
</div>
<div class="cols_col">
   <?php include_once "tpl-modification.php"; ?>
</div>
</div>