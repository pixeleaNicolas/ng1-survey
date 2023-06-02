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
        add_shortcode('ng1_survey', array($this, 'ng1_survey_shortcode'));
        add_action('acf/save_post', array($this,'acf_save_form_data'));
        register_activation_hook(__FILE__,  array($this,'create_validation_page'));
        add_filter('the_content', array($this,'modify_validation_content'));
    }

    public function enqueue_scripts() {
        // Enregistrer et charger les scripts et les styles nécessaires pour le sondage
        wp_register_script('sondage-script', plugins_url('assets/js/function.js', __FILE__), array('jquery'), '1.0', true);
        wp_enqueue_script('sondage-script');

        wp_register_style('sondage-style', plugins_url('style.css', __FILE__));
        wp_enqueue_style('sondage-style');
    }
   public static function convertTextAreaToRadioButtons($textareaValue,$name,$question_nb="1", $cat='', $val='',$readonly=false) {
        $lines = explode("\n", $textareaValue);
    
        foreach ($lines as $index => $line) {
            $line = trim($line);
    
            if ($line !== '') {
                $parts = explode(':', $line);
                $value = trim($parts[0]);
                $label = trim($parts[1]);
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
                        <?php echo $label; ?>
                        <?php endif; ?>
                    </label>
                </div>
                <?php
            }
        }
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
            include_once 'view/tpl-result.php';
            return ob_get_clean();
        }
        return;
    }

    function acf_save_form_data($post_id) {
        // Vérifiez si les données du formulaire ont été soumises
        if (isset($_POST['mon_champ_texte'])) {
            // Récupérez la valeur du champ du formulaire
            $texte = $_POST['mon_champ_texte'];
    
            // Vérifiez si le champ ACF existe
            if (function_exists('update_field')) {
                // Utilisez update_field() pour enregistrer les données dans le champ ACF
                update_field('mon_champ_texte', $texte, $post_id);
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
    public static function generateSpiderChart($data) {
        // Paramètres du graphique
        $chartRadius = 100; // Rayon du graphique
        $chartCenterX = 300; // Coordonnée X du centre du graphique
        $chartCenterY = 250; // Coordonnée Y du centre du graphique
    
        // Calcul de l'angle entre chaque catégorie
        $angleStep = 360 / count($data);
    
        // Fonction pour convertir une valeur en coordonnées polaires
        function polarToCartesian($angle, $radius, $centerX, $centerY) {
            $angleRad = deg2rad($angle);
            $x = $centerX + ($radius * cos($angleRad));
            $y = $centerY + ($radius * sin($angleRad));
            return array('x' => $x, 'y' => $y);
        }
    
    // Génération du code SVG
    $svg = '<svg width="600" height="500" xmlns="http://www.w3.org/2000/svg">';

    // Récupération des clés du tableau
    $keys = array_keys($data);

// Dessin des lignes du graphique
foreach ($keys as $index => $key) {
    $angle = $index * $angleStep;
    $startCoords = polarToCartesian($angle, 0, $chartCenterX, $chartCenterY); // Point de départ à l'intérieur du graphique
    //$endCoords = polarToCartesian($angle, $data[$key] * $chartRadius . , $chartCenterX, $chartCenterY); // Point d'arrivée en fonction de la valeur
    $endCoords = polarToCartesian($angle, $chartRadius + 100, $chartCenterX, $chartCenterY); // Point d'arrivée en fonction de la valeur
    $svg .= '<line x1="' . $startCoords['x'] . '" y1="' . $startCoords['y'] . '" x2="' . $endCoords['x'] . '" y2="' . $endCoords['y'] . '" style="stroke: rgba(55,55,55,.3); stroke-width: 1;" />';

    // Positionnement du texte de catégorie
    $textCoords = polarToCartesian($angle, $chartRadius + 100, $chartCenterX + 20, $chartCenterY + 20);
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
        $coords = polarToCartesian($angle, $radius, $chartCenterX, $chartCenterY);
        $points .= $coords['x'] . ',' . $coords['y'] . ' ';
    }
    $svg .= '<polygon points="' . $points . '" style="fill: rgba(0, 0, 255, 0.5);" />';

    $svg .= '</svg>';

    // Retourner le code SVG
    return $svg;
    }
   
}
