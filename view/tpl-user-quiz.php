
<?php
// Récupérer l'utilisateur courant
$current_user = wp_get_current_user();

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
    // Afficher l'en-tête du tableau
    echo '<table>';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Titre</th>';
    echo '<th>Champ ACF</th>';
    echo '<th>Data</th>';
    echo '<th>Form Data</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    // Boucle sur les posts trouvés
    while ($query->have_posts()) {
        $query->the_post();
        $post_id = get_the_ID();

        // Récupérer les champs ACF
        $acf_field = get_field('nom_du_champ_acf', $post_id);

        // Récupérer les données du formulaire
        $form_data = get_post_meta($post_id, 'nom_de_la_meta_de_formulaire', true);

        // Afficher les informations dans le tableau
        echo '<tr>';
        echo '<td><a href="' . get_permalink() . '">' . get_the_title() . '</a></td>';
        echo '<td>' . $acf_field . '</td>';
        echo '<td>' . get_the_date() . '</td>';
        echo '<td>' . $form_data . '</td>';
        echo '</tr>';
    }

    // Fin de la boucle
    echo '</tbody>';
    echo '</table>';

    // Réinitialiser la requête principale de WordPress
    wp_reset_postdata();
} else {
    ?>
    <h2>Vous n'avez saisie aucun Quiz</h2>
<p>Nous vous invitons à prendre quelques instants pour remplir notre quiz sur la Qualité de Vie au Travail (QVT).</p>
<p> En répondant à ces questions, vous pourrez évaluer différents aspects de votre environnement professionnel et identifier des pistes d'amélioration.</p>
<p>C'est une occasion de réfléchir à votre situation actuelle et de recevoir des recommandations personnalisées pour améliorer votre bien-être au travail. </p>
<p>Ne tardez pas, saisissez cette opportunité pour investir dans votre qualité de vie professionnelle. Remplissez le quiz dès maintenant !</p>
    <?php
}

