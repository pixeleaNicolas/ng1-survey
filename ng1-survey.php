<?php
/*
Plugin Name: Plugin  de sondage Pixelea
Description: Un plugin de sondage nécessitant le plugin ACF.
Version: 1.0
Author: GEHIN Nicolas
License: GPLv2 or later
*/

// Vérifier si le plugin ACF est actif avant d'initialiser le plugin de sondage
if (class_exists('ACF')) {
    add_action('plugins_loaded', 'sondage_plugin_init');
}

function sondage_plugin_init() {



    // Initialisation du plugin de sondage
    $sondage_plugin = new Ng1SondagePlugin();

    // generation de pdf
    //require_once plugin_dir_path(__FILE__) . 'ng1-wkhmltopdf.php';
    //$inputURL = '<html><body><h1>Bravo !</h1></body></html>';
    //$pdfGenerator = new Ng1Wkhtmltopdf($inputURL);




    $sondage_plugin->init();
}

class Ng1SondagePlugin {
    private $formulaire;

    public function __construct() {
       // $this->formulaire = new Ng1FormulaireClass();
    }

    public function init() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('init', array($this, 'register_post_type_formulaire'));
        add_action('init', array($this, 'register_post_type_resultat'));
        add_action('init', array($this, 'register_post_type_reponse'));
        add_action('init',  array($this,'create_custom_taxonomy'));
        add_action('acf/include_fields',  array($this,'generate_reponse_form'));
        add_shortcode('ng1_survey', array($this, 'ng1_survey_shortcode'));
        register_activation_hook(__FILE__,  array($this,'create_validation_page'));
        add_filter('the_content', array($this,'modify_validation_content'));
        add_action('wp_ajax_save_form', array($this,'save_form_ajax'));
        add_action('wp_ajax_nopriv_save_form', array($this,'save_form_ajax'));
    }

    /**
     * Calcule la somme des valeurs d'un tableau.
     *
     * @param array $tableau Le tableau d'entiers.
     * @return int La somme des valeurs du tableau.
     */
    public static function sommeTableau($tableau) {
        $somme = 0;
        
        foreach ($tableau as $value) {
            $somme += $value;
        }
        
        return $somme;
    }

    /**
     * Décode une chaîne encodée et renvoie le résultat en tant que tableau associatif.
     *
     * @param string $input La chaîne encodée.
     * @return array Le tableau associatif résultant du décodage.
     */
    public static function inputDecode($input) {
        $decodedString = strval(urldecode(mb_convert_encoding($input, 'UTF-8')));
        return json_decode($decodedString, true);
    }

    /**
     * Trouve la lettre ayant la plus grande somme pour chaque élément d'un tableau multidimensionnel.
     *
     * @param array $tableau Le tableau multidimensionnel.
     * @return string La lettre ayant la plus grande somme.
     */
    public static function getLettreMaxSomme($tableau) {
        $lettres = array('A', 'B', 'C');
        $sommes = array('A' => 0, 'B' => 0, 'C' => 0);
    
        foreach ($tableau as $element) {
            foreach ($lettres as $lettre) {
                $sommes[$lettre] += $element[$lettre];
            }
        }
    
        $lettreMaxSomme = '';
        $maxSomme = 0;
    
        foreach ($sommes as $lettre => $somme) {
            if ($somme > $maxSomme) {
                $maxSomme = $somme;
                $lettreMaxSomme = $lettre;
            }
        }
    
        return $lettreMaxSomme;
    }

        
    public static function enleverAvantTiret($chaine) {
        $pos = strpos($chaine, "–");
        if ($pos !== false) {
            return mb_substr($chaine, $pos + 1, null, 'UTF-8');
        }
        return $chaine;
    }
        
        // Fonction pour enregistrer le formulaire en cours via AJAX
    public function save_form_ajax() {
        // Vérifier la requête AJAX
        if (wp_doing_ajax()) {
            // Vérifier la méthode de la requête
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Récupérer les données du formulaire
                $formData = $_POST;
        
                // Faire quelque chose avec les données (par exemple, les enregistrer en base de données)
                $this->save_form($formData);
        
                // Répondre à la requête AJAX avec un message de succès
                wp_send_json_success('Le formulaire a été enregistré avec succès.');
            }
        }
        
    }

    public function save_form($data){
        $already_exist = Ng1SondagePlugin::response_is_already_in_db($data['identifier']);
    $current=$data['current'];
        if(! $already_exist ){
            $post_args = array(
                'post_title'    => date('d M Y H:i:s').' - En cours ', // Titre de la publication
                'post_type'     => 'reponse', // Type de publication personnalisé
                'post_status'   => 'draft', // Statut de publication $this->creer_tableau_textarea_to_array_reponses($data)
                'post_content' => json_encode($data['identifier'])." - ".json_encode($already_exist)." - ".json_encode($data['form_data']).' - '.$current,
            );
            $post_id = wp_insert_post($post_args);
        }else{
            $post_id=$already_exist;
            $my_post = array(
                'ID'           => $post_id,
                'post_content' => json_encode($data['identifier'])." - ".json_encode($already_exist)." - ".json_encode($data['form_data']).' - '.$current,
            );
        
        // Update the post into the database
            wp_update_post( $my_post );
        }
        if (function_exists('update_field')) {
            update_field('identifier', $data['identifier'], $post_id);
            update_field('current', $current, $post_id);
            foreach($data['form_data'] as $key =>$value){
                update_field($key, $value, $post_id);
            }
        }
    }
    public static function generateUniqueToken() {
        // Générer un jeton unique
        $token = bin2hex(random_bytes(16));
    
        // Chemin vers le fichier texte contenant les jetons
        $tokenFile = WP_PLUGIN_DIR . '/ng1-survey/temp/tokens.txt';
    
        // Vérifier si le jeton généré existe déjà
        while (Ng1SondagePlugin::isTokenExists($token, $tokenFile)) {
            $token = bin2hex(random_bytes(16));
        }
    
        // Ajouter le jeton généré à la liste des jetons existants
        file_put_contents($tokenFile, $token . PHP_EOL, FILE_APPEND);
    
        // Retourner le jeton unique
        return $token;
    }
    
    public static function isTokenExists($token, $tokenFile=WP_PLUGIN_DIR . '/ng1-survey/temp/tokens.txt') {
        // Lire les jetons déjà présents dans le fichier
        $existingTokens = file($tokenFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
        // Vérifier si le jeton existe déjà
        return in_array($token, $existingTokens);
    }
    
    public function ng1_survey_shortcode($atts) {
        //shortcode : ng1_survey'
        // Récupérer l'ID du formulaire à partir des attributs
        $atts = shortcode_atts(array(
            'id' => '',
            'view' =>'form'
        ), $atts);
        $form_id = $atts['id'];
        $view = $atts['view'];

        // Vérifier si l'ID du formulaire est spécifié
        if (! empty($form_id) && $view='form') {
             // Code pour afficher et traiter le formulaire de sondage avec l'ID spécifié
             $form_fields= get_fields($form_id);
             $i=0;
             ob_start();
             // Inclure le contenu du fichier du formulaire
             include_once 'view/tpl-form.php';

             return ob_get_clean();
        }else if($view == 'myQuiz'){
            ob_start();
            include_once 'view/tpl-user-quiz.php';
            return ob_get_clean();
        }else if($view == 'resultat'){
            ob_start();
            include_once 'view/tpl-resultat.php';
            return ob_get_clean();
        }else if($view == 'reponse'){
            ob_start();
            include_once 'view/tpl-reponse-user.php';
            return ob_get_clean();
        }
        return;
    }
    /**
 * Remplace les derniers caractères d'une chaîne.
 *
 * @param string $chaine La chaîne à modifier.
 * @param int $increment L'incrément à utiliser pour la numérotation.
 * @return string La chaîne modifiée.
 */
    public function remplacer_derniers_caracteres($chaine, $increment) {
    if (strlen($chaine) < 3) {
        return $chaine;  // La chaîne est trop courte, pas de remplacement possible
    }

    $prefixe = substr($chaine, 0, -3);  // Partie de la chaîne sans les 3 derniers caractères
    $numerotation = substr($chaine, -3);  // Les 3 derniers caractères

    $increment = intval($increment);  // Conversion en nombre entier
    $numerotation = str_pad($increment, 3, '0', STR_PAD_LEFT);  // Formatage sur 3 caractères avec des zéros

    return $prefixe . $numerotation;
    }

    /**
 * Crée un tableau à partir d'un texte contenant des lignes au format "clé: valeur".
 *
 * @param string $texte Le texte à traiter.
 * @return array Le tableau résultant.
 */
    public function creer_tableau_textarea_to_array_reponses($texte) {
    $lignes = explode("\n", $texte);
    $tableau = array();

    foreach ($lignes as $ligne) {
        $ligne = trim($ligne);

        if (!empty($ligne)) {
            $parties = explode(':', $ligne, 2);
            $cle = trim($parties[0]);
            $valeur = trim($parties[1]);
            $tableau[$cle] = $valeur;
        }
    }

    return $tableau;
    }
    /**
 * Récupère un tableau à partir d'une chaîne encodée au format JSON et URL.
 *
 * @return array Le tableau obtenu à partir de la chaîne encodée.
 */
    public static function get_array_from_url_encode_json($encoded_string) {
    // Récupérer la chaîne encodée depuis la source (par exemple, une requête HTTP)

    // Décoder la chaîne encodée depuis le format URL
    $encoded_string = urldecode($encoded_string);

    // Décoder la chaîne encodée depuis le format JSON
    $decoded_array = json_decode($encoded_string, true);

    // Vérifier si le décodage a réussi
    if ($decoded_array === null) {
        // Le décodage a échoué, gérer l'erreur si nécessaire
        // Par exemple, retourner un tableau vide ou afficher un message d'erreur
        return array();
    }

    // Le décodage a réussi, retourner le tableau obtenu
    return $decoded_array;
    }
    public static function get_only_reponse_array($array) {
    $result = array();
  
    foreach ($array as $key => $value) {
      if (strpos($key, 'reponse_') === 0) {
        $result[$key] = $value;
      }
    }
  
    return $result;
      }

    /**
 * Génère le formulaire de réponses.
 */
    public function generate_reponse_form() {
   
    $questions = get_field("questions", 12);

    $form_field = array();
    $increment = 0;

    foreach ($questions as $question) {
        $increment++;
  
        $form_field[] = array(
            'key' => $this->remplacer_derniers_caracteres('field_647edafbf9992', $increment),
            'label' => $question['question'],
            'name' => 'reponse_'.$increment,
            'aria-label' => '',
            'type' => 'radio',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'choices' => $this->creer_tableau_textarea_to_array_reponses($question['reponse']),
            'default_value' => '',
            'return_format' => 'value',
            'allow_null' => 0,
            'other_choice' => 0,
            'layout' => 'vertical',
            'save_other_choice' => 0,
        );
    }

    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group(array(
        'key' => 'group_647edab2e7863',
        'title' => 'Questionnaire',
        'fields' => $form_field,
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'reponse',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
        'show_in_rest' => 0,
    ));
    }

    /**
 * Affiche le label des réponses possibles pour un groupe de champs ACF.
 *
 * @param string $response_acf_group_key La clé du groupe de champs ACF.
 * @param string $reponse_id identifiant de la réponse (clé du champ réponse exemple reponse_1)
 * @param string $reponse_value valeur de la réponse
 */
    public static function show_form_response($response_acf_group_key, $reponse_id,$reponse_value) {
    $fields = acf_get_fields($response_acf_group_key); // Récupérer les champs du groupe

    foreach ($fields as $field) {
        extract($field); // Extraction des variables du tableau $field
    
        if (isset($name) && isset($type) && $name === $reponse_id) {
            if ($type === 'radio') {
                ?>
                <div class="<?php echo $name; ?>">
                    <div class="question">
                        <?php echo $label; ?>
                    </div>
                    <div class="reponses">
                        <?php foreach ($choices as $key => $item): ?>
                            <?php if ($reponse_value == $key): ?>
                                <p class="is_response">
                            <?php else: ?>
                                <p>
                            <?php endif; ?>
                            <?php echo $item; ?>
                            <?php if ($reponse_value == $key): ?>
                                </p>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php
            }
        }
    }
    
    }
    public function enqueue_scripts() {
        // Enregistrer et charger les scripts et les styles nécessaires pour le sondage
        wp_register_script('sondage-script', plugins_url('assets/js/function.js', __FILE__), array('jquery'), '1.0', true);
        wp_enqueue_script('sondage-script');

        wp_enqueue_script('survey-ajax-script', plugins_url('assets/js/ajax.js', __FILE__), array('jquery'), '1.0', true);

        // Localiser le script avec la variable ajaxurl
        wp_localize_script('survey-ajax-script', 'myAjax', array('ajaxurl' => admin_url('admin-ajax.php')));

        wp_register_style('sondage-style', plugins_url('style.css', __FILE__));
        wp_enqueue_style('sondage-style');
    }
    public static function convertTextAreaToRadioButtons($textareaValue,$name,$question_nb="1", $cat='', $val='',$readonly=false,$post_id=false) {
        $lines = explode("\n", $textareaValue);
  
        foreach ($lines as $index => $line) {
            $line = trim($line);
    
            if ($line !== '') {
                $parts = explode(':', $line);
                $value = trim($parts[0]);
                $label = trim($parts[1]);
                if (!empty($post_id)){
                    $val=get_field($name,$post_id);
         
                }
                    if($val ==$value){
                        $check= 'checked';
                    }else{
                        $check= '';
                    }
             
             

                ?>
                <?php if($readonly){ 
                        $attr_readonly='readonly';
                    }else{
                        $attr_readonly='';
                    } 
                ?>
                <div>
                    <label class="ng1-survey__label" for="<?php echo $name . '_' . $value; ?>">
                    <?php if($attr_readonly):?>
                        <div class="ng1-survey__radio ng1-survey__radio_div <?php echo $check; ?>">
                        <?php if(!empty($check)):?>
                        <em class="ng1-survey__radio_div__response">
                        <?php endif; ?>
                        <?php echo $label; ?>
                        <?php if(!empty($check)):?>
                        </em>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                        <input  class="ng1-survey__radio" type="radio" data-index="<?php echo $question_nb; ?>" data-cat='<?php echo $cat; ?>' name="<?php echo $name; ?>" value="<?php echo $value; ?>" <?php echo $check; ?> id="<?php echo $name . '_' . $value; ?>">
                        <input  type="hidden"  value='<?php echo $cat; ?>' name="<?php echo 'cat_'.$name; ?>" >
                        <?php echo $label; ?>
                        <?php endif; ?>
                    </label>
                </div>
                <?php
            }
        }
    }
 

    
    public static function response_is_already_in_db($identifier) {

            $args = array(
                'post_type' => 'reponse',
                'posts_per_page' => 1,
                'meta_query' => array(
                    array(
                        'key' => 'identifier',
                        'value' => $identifier
                    )
                    ),
                    'post_status' => array('publish',  'draft'), 
            );
        
            $query = new WP_Query($args);
        
            if ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                wp_reset_postdata();
        
                // La valeur est déjà présente, retourne l'ID du post
                return $post_id;
            }
        
            // La valeur est unique
            return false;
        }
        
    /**
     * Compte le nombre de "A", "B" et "C" en fonction de la catégorie de la réponse
     * et calcule le score pour chaque catégorie.
     *
     * @param array $data Le tableau de données contenant les catégories et les réponses.
     * @return array Un tableau associatif avec les catégories comme clés et un tableau contenant les compteurs et le score pour chaque réponse.
     */
    public static function countResponsesByCategory($data) {
        $responseCounts = array();
        
        $currentCategory = null;
        
        foreach ($data as $key => $value) {
            if (strpos($key, 'cat_reponse_') === 0) {
                $currentCategory = $value;
            } elseif (strpos($key, 'reponse_') === 0) {
                if ($currentCategory) {
                    if (!isset($responseCounts[$currentCategory])) {
                        $responseCounts[$currentCategory] = array(
                            'A' => 0,
                            'B' => 0,
                            'C' => 0,
                            'score' => 0
                        );
                    }
                    
                    if ($value === 'A') {
                        $responseCounts[$currentCategory]['A']++;
                        $responseCounts[$currentCategory]['score'] += 1;
                    } elseif ($value === 'B') {
                        $responseCounts[$currentCategory]['B']++;
                        $responseCounts[$currentCategory]['score'] += 2;
                    } elseif ($value === 'C') {
                        $responseCounts[$currentCategory]['C']++;
                        $responseCounts[$currentCategory]['score'] += 3;
                    }
                }
            }
        }
        
        return $responseCounts;
    }
    /**
     * Calcule le nombre de points par catégorie en fonction des réponses.
     *
     * @param array $data Le tableau de données contenant les catégories et les réponses.
     * @return array Un tableau associatif avec les catégories comme clés et le score total pour chaque catégorie.
     */
    public static function calculateScoreByCategory($data) {
        $scores = array();
        
        $currentCategory = null;
        
        foreach ($data as $key => $value) {
            if (strpos($key, 'cat_reponse_') === 0) {
                $currentCategory = $value;
            } elseif (strpos($key, 'reponse_') === 0) {
                if ($currentCategory) {
                    if (!isset($scores[$currentCategory])) {
                        $scores[$currentCategory] = 0;
                    }
                    
                    if ($value === 'A') {
                        $scores[$currentCategory] += 1;
                    } elseif ($value === 'B') {
                        $scores[$currentCategory] += 2;
                    } elseif ($value === 'C') {
                        $scores[$currentCategory] += 3;
                    }
                }
            }
        }
        
        return $scores;
    }
    public static function acf_save_survey_reponse($post_id=false,$data) {
        $current_user = wp_get_current_user();



        extract($data);
        // Vérifiez si les données du formulaire ont été soumises
        if (!empty($form_data)) {

            // Vérifiez si les champs ne sont pas vides
            if (!empty($reponses) && !empty($nbpoints) && !empty($form_data)) {
        

                // Vérifiez si le champ ACF existe
                if (function_exists('update_field')) {
                    $counters = array(
                        'A' => 0,
                        'B' => 0,
                        'C' => 0
                    );
                    
                    // Utilisez update_field() pour mettre à jour les champs ACF
                    //update_field('reponses', $reponses, $post_id);
                   // 
                    update_field('nbpoints', $nbpoints, $post_id);
                    update_field('form_data', $form_data, $post_id);
                    update_field('user',get_current_user_id(), $post_id);
                    update_field('form_id',$form_id, $post_id);
                    foreach ($data as $key => $value):
                        if (strpos($key, 'reponse_') === 0) {
                            // Effectuer l'update_field
                            update_field($key, $value, $post_id);
                            // Vérifier la valeur de la réponse
                            if (isset($counters[$value])) {
                                $counters[$value]++; // Incrémenter le compteur correspondant
                            }
                        }
                    endforeach;
                    update_field('score',json_encode($counters), $post_id);
                   $lettreProfil = '';
                   $maxSomme = 0;
                 
                   foreach ($counters as $lettre => $somme) {
                       if ($somme > $maxSomme) {
                           $maxSomme = $somme;
                           $lettreProfil = $lettre;
                       }
                   }
       
                   switch ($lettreProfil) {
                       case ('A'):
                         $profil_id = 16575;
                         break;
                       case ('B'):
                         $profil_id = 16576;
                         break;
                         case ('C'):
                           $profil_id = 16577;
                           break;
                   
                   }

                   if(!empty($profil_id)){
                        update_field('profil', $profil_id, $post_id);
                   }
                   $points_cat =Ng1SondagePlugin::calculateScoreByCategory($data);
                   update_field('score_by_cat', json_encode($points_cat ),$post_id);
                   $svg = Ng1SondagePlugin::generateSpiderChart($points_cat);

                   $update = array(
                    'ID' => $post_id,
                    'post_title'    => date('d M Y H:i:s').' - Resultat', // Titre de la publication
                    'post_status'   => 'publish', // Statut de publication $this->creer_tableau_textarea_to_array_reponses($data)
                    'post_content' => base64_encode($svg),
                );
            
              // Update the post into the database
                wp_update_post( $update );
                }
            }
        }
    }
    
    public function register_post_type_formulaire() {
        $labels = array(
            'name' => 'Formulaires',
            'singular_name' => 'Formulaire',
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'rewrite' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            // Ajoutez d'autres arguments selon vos besoins
        );

        register_post_type('formulaire', $args);
    }
    public function register_post_type_resultat() {
        $labels = array(
            'name' => 'Resultat',
            'singular_name' => 'Resultat',
        );

        $args = array(
            'labels' => $labels,
            'public' => false,
            'rewrite' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true
            // Ajoutez d'autres arguments selon vos besoins
        );

        register_post_type('resultat', $args);
    }
    function create_custom_taxonomy() {
        $taxonomy = 'categorie_question'; // Remplacez "categorie_question" par le slug souhaité pour votre taxonomie
    
        $labels = array(
            'name'              => _x('Catégories de questions', 'taxonomy general name'),
            'singular_name'     => _x('Catégorie de question', 'taxonomy singular name'),
            'search_items'      => __('Rechercher des catégories'),
            'all_items'         => __('Toutes les catégories'),
            'parent_item'       => __('Catégorie parente'),
            'parent_item_colon' => __('Catégorie parente :'),
            'edit_item'         => __('Modifier la catégorie'),
            'update_item'       => __('Mettre à jour la catégorie'),
            'add_new_item'      => __('Ajouter une nouvelle catégorie'),
            'new_item_name'     => __('Nom de la nouvelle catégorie'),
            'menu_name'         => __('Catégories de questions'),
        );
    
        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => $taxonomy), // Remplacez "taxonomy" par le slug souhaité pour l'URL de la taxonomie
        );
    
        register_taxonomy($taxonomy, array(), $args);
    }

    public function register_post_type_reponse() {
        $labels = array(
            'name' => 'Réponses',
            'singular_name' => 'Reponse',
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'rewrite' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'rewrite' => array('slug' => 'reponse'),
           'show_in_rest' =>true,
           
            // Ajoutez d'autres arguments selon vos besoins
        );

        register_post_type('reponse', $args);
    }

    function create_validation_page() {
        // Vérifiez si la page existe déjà
        $page = get_page_by_path('validation');
    
        // Si la page n'existe pas, créez-la
        if (!$page) {
            $page_args = array(
                'post_title'   => 'Validation',
                'post_content' => 'Contenu de ma page personnalisée',
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_name'    => 'validation'
            );
    
            // Insérer la page dans la base de données
            $page_id = wp_insert_post($page_args);
        }
    }
    function modify_validation_content($content) {
        // Vérifiez si c'est la page que vous souhaitez modifier en fonction de son ID, de son slug ou d'autres critères
        if (is_page('validation')) {
            ob_start();
            /* ?>
            <pre><?php var_dump($_POST); ?></pre>

            <?php */
            include_once "validation.php";
            // Modifiez le contenu de la page ici
            $modified_content =ob_get_clean();
    
            // Retournez le contenu modifié
            return $modified_content;
        }
    
        // Si ce n'est pas la page que vous souhaitez modifier, retournez le contenu tel quel
        return $content;
    }
    function show_response($json){
        
    }
            // Fonction pour convertir une valeur en coordonnées polaires
    public static function polarToCartesian($angle, $radius, $centerX, $centerY) {
        $angleRad = deg2rad($angle);
        $x = $centerX + ($radius * cos($angleRad));
        $y = $centerY + ($radius * sin($angleRad));
        return array('x' => $x, 'y' => $y);
    }
    public static function generateSpiderChart($data) {
    
        // Paramètres du graphique
        $chartRadius = 100; // Rayon du graphique
        $chartCenterX = 300; // Coordonnée X du centre du graphique
        $chartCenterY = 250; // Coordonnée Y du centre du graphique
    
        // Calcul de l'angle entre chaque catégorie
        $angleStep = 360 / count($data);
    

    
    // Génération du code SVG
    $svg = '<svg width="600" height="500" xmlns="http://www.w3.org/2000/svg">';

    // Récupération des clés du tableau
    $keys = array_keys($data);

    // Dessin des lignes du graphique
    foreach ($keys as $index => $key) {
        $angle = $index * $angleStep;
        $startCoords = Ng1SondagePlugin::polarToCartesian($angle, 0, $chartCenterX, $chartCenterY); // Point de départ à l'intérieur du graphique
        //$endCoords = polarToCartesian($angle, $data[$key] * $chartRadius . , $chartCenterX, $chartCenterY); // Point d'arrivée en fonction de la valeur
        $endCoords = Ng1SondagePlugin::polarToCartesian($angle, $chartRadius + 100, $chartCenterX, $chartCenterY); // Point d'arrivée en fonction de la valeur
        $svg .= '<line x1="' . $startCoords['x'] . '" y1="' . $startCoords['y'] . '" x2="' . $endCoords['x'] . '" y2="' . $endCoords['y'] . '" style="stroke: rgba(55,55,55,.3); stroke-width: 1;" />';

        // Positionnement du texte de catégorie
        $textCoords = Ng1SondagePlugin::polarToCartesian($angle, $chartRadius + 100, $chartCenterX + 20, $chartCenterY + 20);
        $svg .= '<text x="' . $textCoords['x'] . '" y="' . $textCoords['y'] . '" text-anchor="middle">' . $key . '</text>';

        // Dessin de l'échelle
    //$scaleCoords = polarToCartesian($angle, $chartRadius + 10, $chartCenterX, $chartCenterY);
    //$scaleEndCoords = polarToCartesian($angle, $chartRadius + 30, $chartCenterX, $chartCenterY);
    //$svg .= '<line x1="' . $scaleCoords['x'] . '" y1="' . $scaleCoords['y'] . '" x2="' . $scaleEndCoords['x'] . '" y2="' . $scaleEndCoords['y'] . '" style="stroke: rgba(55,55,55,.3); stroke-width: 1;" />';
    }
    // Dessin des polygones représentant les valeurs
    $points = '';
    foreach ($keys as $index => $key) {
        $angle = $index * $angleStep;
        $radius = ($data[$key] / 10) * $chartRadius; // Normalisation de la valeur entre 0 et 1
        $coords = Ng1SondagePlugin::polarToCartesian($angle, $radius, $chartCenterX, $chartCenterY);
        $points .= $coords['x'] . ',' . $coords['y'] . ' ';
    }
    $svg .= '<polygon points="' . $points . '" style="fill: rgba(0, 0, 255, 0.5);" />';

    $svg .= '</svg>';

    // Retourner le code SVG
    return $svg;
    }
   
}