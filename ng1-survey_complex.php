<?php
/*
Plugin Name: Plugin  de sondage Pixelea
Description: Un plugin de sondage nécessitant le plugin ACF.
Version: 1.0
Author: GEHIN Nicolas
License: GPLv2 or later
*/
use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;

require_once 'Ng1SurveyUtilitiesClass.php';
// Vérifier si le plugin ACF est actif avant d'initialiser le plugin de sondage
if (class_exists('ACF')) {
    add_action('plugins_loaded', 'sondage_plugin_init');
}

function sondage_plugin_init() {

    // Initialisation du plugin de sondage
    $sondage_plugin = new Ng1SondagePlugin();
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
        add_action('wp_ajax_save_form', array($this,'save_form_ajax'));
        add_action('wp_ajax_nopriv_save_form', array($this,'save_form_ajax'));
        add_action('save_post', array($this,'generate_pdf'),10,3);
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
        if (is_user_logged_in()) {
            // Utilisateur connecté, afficher le lien de déconnexion
            $deconnexion_url = wp_logout_url();
            echo '<a href="' . $deconnexion_url . '">Se déconnecter</a>';
        }
        if (!is_user_logged_in()) {
            ob_start();
            // Inclure le contenu du fichier du formulaire
            include_once 'view/tpl-connexion.php';

            return ob_get_clean();
        }else if (! empty($form_id) && $view='form') {
             // Code pour afficher et traiter le formulaire de sondage avec l'ID spécifié
             $form_fields= get_fields($form_id);
             $i=0;
             ob_start();
             // Inclure le contenu du fichier du formulaire
             include_once 'view/tpl-form.php';
          
             return ob_get_clean();
        }else if($view == 'validation'){
            ob_start();
            include_once 'view/tpl-validation.php';
            return ob_get_clean();
        }else if($view == 'myQuiz'){
            ob_start();
            include_once 'view/tpl-user-quiz.php';
            return ob_get_clean();
        }else if($view == 'resultat'){
            ob_start();
            include_once 'view/tpl-resultat.php';
            return ob_get_clean();
        }else if($view == 'single-resultat'){
            ob_start();
            include_once 'view/tpl-single-resultat.php';
            return ob_get_clean();
        }else if($view == 'reponse'){
            ob_start();
            include_once 'view/tpl-reponse-user.php';
            return ob_get_clean();
        }
        return;
    }
    
    
    public function generate_pdf($post_id, $post, $update) {

        // Vérifier si le post est de type 'reponse' et s'il ne s'agit pas d'une sauvegarde automatique
        if (get_post_type($post_id) === 'reponse' && !wp_is_post_autosave($post_id) && !wp_is_post_revision($post_id)) {
            // Création de l'instance Html2Pdf
            $html2pdf = new HTML2PDF('P', 'A4', 'fr', true, 'UTF-8', array(20, 20, 20,20));
            $html2pdf->pdf->SetAutoPageBreak(true, 15);
            // ob_end_clean();
            ob_start();
        

             include_once('view/tpl-pdf.php') ;
       
            $html = ob_get_clean();
    
            $html2pdf->writeHTML($html);

            // Génération du nom de fichier unique
            $filename = 'resultat_' . $post_id . '.pdf';
            
            // ID de l'utilisateur
            $user_id = get_current_user_id();
            
            // Répertoire d'upload de WordPress
            $upload_dir = wp_upload_dir();
            $sub_folder = 'user_' . $user_id;
            $upload_path = $upload_dir['path'] . '/'. $sub_folder ."_". $filename;
            // Chemin complet du fichier
          // $upload_path = $upload_dir['path'] . '/' . $sub_folder . '/' . $filename;
          // 
          // // Créer le sous-dossier s'il n'existe pas déjà
          // if (!file_exists($upload_dir['basedir'] . '/' . $sub_folder)) {
          //     wp_mkdir_p($upload_dir['basedir'] . '/' . $sub_folder);
          //     chmod($upload_dir['basedir'] . '/' . $sub_folder, 0755); // Définir les permissions du dossier
          // }
            
            // Enregistrer le fichier dans le dossier spécifié
            $html2pdf->output($upload_path, 'F');
    
        // // Vérification si le fichier existe déjà dans la médiathèque
        $existing_attachment = Ng1SondagePlugin::ng1_check_attachment_file_exist($filename);
    
    
        if (!empty($existing_attachment)) {
            // Mettre à jour le fichier existant dans la médiathèque
            $attachment_id = $existing_attachment;
        
        } else {
               // Créer un nouvel attachment dans la médiathèque
    
           $attachment_id = wp_insert_attachment(
               array(
                   'guid'           => $upload_dir['url']  . '/'. $sub_folder ."_". $filename,
                   'post_mime_type' => 'application/pdf',
                   'post_title'     => $filename,
                   'post_content'   => '',
                   'post_status'    => 'inherit'
               ),
               $upload_path,
               $post_id
           );
          }
    
            // Mettre à jour le champ ACF PDF avec l'ID de l'attachment
           update_field('resultat_pdf', $attachment_id, $post_id);
        }
    }
    public static function ng1_check_attachment_file_exist($filename){
        return Ng1SurveyUtilities::ng1_check_attachment_file_exist($filename);
    }
    
    public static function sommeTableau($tableau) {
        return  Ng1SurveyUtilities::sommeTableau($tableau);
    }

    public static function inputDecode($input) {
        return Ng1SurveyUtilities::inputDecode($input);
    }
    public static function inputEncode($input) {
        return Ng1SurveyUtilities::inputEncode($input);
    }
    public static function getLettreMaxSomme($tableau) {
        return Ng1SurveyUtilities::getLettreMaxSomme($tableau);
    }
    public static function enleverAvantTiret($chaine) {
        return Ng1SurveyUtilities::enleverAvantTiret($chaine);
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
        return Ng1SurveyUtilities::generateUniqueToken();
    }
    
    public static function isTokenExists($token, $tokenFile=WP_PLUGIN_DIR . '/ng1-survey/temp/tokens.txt') {
       return Ng1SurveyUtilities::isTokenExists($token, $tokenFile);
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
    return;
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
                <div class='ng1-survey__radio-group   <?php if(!empty($check)):?>checked<?php endif; ?>'>
                    <label class="ng1-survey__label ng1-survey__radio-group__wrapper" for="<?php echo $name . '_' . $value; ?>">
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
                        <input  class="btn_submit" type="hidden"  value='<?php echo $cat; ?>' name="<?php echo 'cat_'.$name; ?>" >
                        <?php echo $label; ?>
                        <?php endif; ?>
                    </label>
                </div>
                <?php
            }
        }
    }
 
    public static function convertTextAreaToReponses($textareaValue, $name, $question_nb = "1", $cat = '', $val = '', $readonly = false, $post_id = false) {
        $lines = explode("\n", $textareaValue);
    
        foreach ($lines as $index => $line) {
            $line = trim($line);
    
            if ($line !== '') {
                $parts = explode(':', $line);
                $value = trim($parts[0]);
                $label = trim($parts[1]);
                if (!empty($post_id)) {
                    $val = get_field($name, $post_id);
                }
    
                $class = '';
                $style="";
                if ($val == $value) {
                    $class = 'selected';
                    $style='';
                }
                ?>
                <p class="ng1-survey__paragraph <?php echo $class; ?>" <?php echo $style; ?>><?php echo $label; ?></p>
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
    public static function acf_save_survey_reponse($post_id=false,$data=array()) {
        if(!empty($data)){
            return;
        }
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
/**
 * Récupère la catégorie d'un sondage.
 *
 * @param array|string $categorie_array_or_id Tableau d'ID de catégories ou ID de catégorie.
 * @return string|null Nom de la catégorie du sondage ou null si la catégorie n'est pas trouvée.
 */
public static function get_survey_category($categorie_array_or_id) {
    if (empty($categorie_array_or_id)) {
        return null;
    }

    if (is_array($categorie_array_or_id)) {
        $category_id = $categorie_array_or_id[0];
        $category = get_term_by('id', $category_id, 'categorie_question');
    } else if (is_string($categorie_array_or_id)) {
        $category = get_term_by('id', $categorie_array_or_id, 'categorie_question');
    }

    if ($category) {
        return $category->name;
    }

    return null;
}


    public function register_post_type_reponse() {
        $labels = array(
            'name' => 'Réponses',
            'singular_name' => 'Reponse',
        );
        
        $capabilities = array(
            'edit_post' => 'edit_reponse',
            'read_post' => 'read_reponse',
            'delete_post' => 'delete_reponse',
            'edit_posts' => 'edit_reponses',
            'edit_others_posts' => 'edit_others_reponses',
            'publish_posts' => 'publish_reponses',
            'read_private_posts' => 'read_private_reponses',
            'delete_posts' => 'delete_reponses',
            'delete_private_posts' => 'delete_private_reponses',
            'delete_published_posts' => 'delete_published_reponses',
            'delete_others_posts' => 'delete_others_reponses',
            'edit_private_posts' => 'edit_private_reponses',
            'edit_published_posts' => 'edit_published_reponses',
            'create_posts' => 'create_reponses',
        );
        
        $args = array(
            'labels' => $labels,
            'capability_type' => 'reponse',
            'capabilities' => $capabilities,
            'public' => true,
            'rewrite' => array('slug' => 'reponse'),
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
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
    $svg = '<svg width="600" height="500"  viewBox="0 0 600 500" xmlns="http://www.w3.org/2000/svg">';

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
    $svg .= '<polygon points="' . $points . '" style="fill: var(--wp--preset--color--second);opacity:.3" />';

    $svg .= '</svg>';

    // Retourner le code SVG
    return $svg;
    }
    public static function generateSpiderChartDraw($data) {

        // Paramètres du graphique
        $chartRadius = 100; // Rayon du graphique
        $chartCenterX = 300; // Coordonnée X du centre du graphique
        $chartCenterY = 250; // Coordonnée Y du centre du graphique
    
        // Calcul de l'angle entre chaque catégorie
        $angleStep = 360 / count($data);
    
        // Génération du code Draw
        $draw = '<draw>';
      
        // Récupération des clés du tableau
        $keys = array_keys($data);
    
        // Dessin des lignes du graphique
        foreach ($keys as $index => $key) {
            $angle = $index * $angleStep;
            $startCoords = self::polarToCartesian($angle, 0, $chartCenterX, $chartCenterY); // Point de départ à l'intérieur du graphique
            //$endCoords = polarToCartesian($angle, $data[$key] * $chartRadius . , $chartCenterX, $chartCenterY); // Point d'arrivée en fonction de la valeur
            $endCoords = self::polarToCartesian($angle, $chartRadius + 100, $chartCenterX, $chartCenterY); // Point d'arrivée en fonction de la valeur
            $draw .= '<line x1="' . $startCoords['x'] . '" y1="' . $startCoords['y'] . '" x2="' . $endCoords['x'] . '" y2="' . $endCoords['y'] . '" stroke="rgba(55,55,55,.3)" stroke-width="1" />';
    
            // Positionnement du texte de catégorie
            $textCoords = self::polarToCartesian($angle, $chartRadius + 100, $chartCenterX + 20, $chartCenterY + 20);
            $draw .= '<text x="' . $textCoords['x'] . '" y="' . $textCoords['y'] . '" text-anchor="middle">' . $key . '</text>';
    
            // Dessin de l'échelle
            //$scaleCoords = polarToCartesian($angle, $chartRadius + 10, $chartCenterX, $chartCenterY);
            //$scaleEndCoords = polarToCartesian($angle, $chartRadius + 30, $chartCenterX, $chartCenterY);
            //$draw .= '<line x1="' . $scaleCoords['x'] . '" y1="' . $scaleCoords['y'] . '" x2="' . $scaleEndCoords['x'] . '" y2="' . $scaleEndCoords['y'] . '" style="stroke: rgba(55,55,55,.3); stroke-width: 1;" />';
        }
    
        // Dessin des polygones représentant les valeurs
        $points = '';
        foreach ($keys as $index => $key) {
            $angle = $index * $angleStep;
            $radius = ($data[$key] / 10) * $chartRadius; // Normalisation de la valeur entre 0 et 1
            $coords = self::polarToCartesian($angle, $radius, $chartCenterX, $chartCenterY);
            $points .= $coords['x'] . ',' . $coords['y'] . ' ';
        }
        $draw .= '<polygon points="' . $points . '" fill="var(--wp--preset--color--second)" opacity=".3" />';
    
        $draw .= '</draw>';
    
        // Retourner le code Draw
        return $draw;
    }
    public static function svgToDraw($svg) {
        // Remplacer la balise <svg> par <draw>
        $draw = str_replace('<svg', '<draw', $svg);
        // Remplacer les balises </svg> par </draw>
        $draw = str_replace('</svg>', '</draw>', $draw);
    
        // Remplacer les balises <line> par <path>
        $draw = str_replace('<line', '<path', $draw);
        // Supprimer l'attribut x1
        $draw = str_replace(' x1="', ' d="M', $draw);
        // Remplacer l'attribut y1 par L
        $draw = str_replace('" y1="', ' L', $draw);
        // Supprimer l'attribut x2
        $draw = str_replace('" x2="', ' ', $draw);
        // Remplacer l'attribut y2 par Z"
        $draw = str_replace('" y2="', ' Z"', $draw);
      
        // Remplacer les balises <text> par <label>
        $draw = str_replace('<text', '<label', $draw);
        // Supprimer l'attribut text-anchor
        $draw = preg_replace('/ text-anchor="[^"]+"/', '', $draw);
    
        // Remplacer les balises <polygon> par <path>
        $draw = str_replace('<polygon', '<path', $draw);
        // Supprimer l'attribut points
        $draw = preg_replace('/ points="([^"]+)"/', ' d="M$1 Z"', $draw);
        // Remplacer l'attribut fill par fill-color
        $draw = str_replace(' fill="', ' fill-color="', $draw);
    
        return $draw;
    }
}