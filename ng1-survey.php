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
    // Inclure le fichier formulaire.php
    require_once plugin_dir_path(__FILE__) . 'ng1-formulaire-class.php';
    // Initialisation du plugin de sondage
    $sondage_plugin = new Ng1SondagePlugin();
    $sondage_plugin->init();
}

class Ng1SondagePlugin {
    private $formulaire;

    public function __construct() {
        $this->formulaire = new Ng1FormulaireClass();
    }
    
    public function init() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('init', array($this, 'register_post_type_formulaire'));
        add_action('init', array($this, 'register_post_type_reponse'));
        add_action('init',  array($this,'create_custom_taxonomy'));
        add_shortcode('ng1_survey', array($this, 'ng1_survey_shortcode'));
        add_action('acf/save_post', array($this,'acf_save_form_data'));
        register_activation_hook(__FILE__,  array($this,'create_custom_page'));
        add_filter('the_content', array($this,'modify_validation_content'));
    }

    public function enqueue_scripts() {
        // Enregistrer et charger les scripts et les styles nécessaires pour le sondage
        wp_register_script('sondage-script', plugins_url('assets/js/function.js', __FILE__), array('jquery'), '1.0', true);
        wp_enqueue_script('sondage-script');

        wp_register_style('sondage-style', plugins_url('style.css', __FILE__));
        wp_enqueue_style('sondage-style');
    }
   public static function convertTextAreaToRadioButtons($textareaValue,$name,$val='') {
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
                echo '<input type="radio" name="'.$name.'" value="' . $value . '" '.$check.'>' . $label . '<br>';
            }
        }
    }
    public function ng1_survey_shortcode($atts) {
     // Récupérer l'ID du formulaire à partir des attributs
     $atts = shortcode_atts(array(
        'id' => '',
    ), $atts);
    $form_id = $atts['id'];

    // Vérifier si l'ID du formulaire est spécifié
    if (empty($form_id)) {
        return ''; // Retourner une chaîne vide si l'ID du formulaire n'est pas spécifié
    }


    // Code pour afficher et traiter le formulaire de sondage avec l'ID spécifié
    $form_fields= get_fields($form_id);
    $i=0;
    ob_start();
    ?>
<form id='survey-form' action="/validation" method="post">

    <?php foreach($form_fields['questions'] as  $item): 
    $i++;
        extract($item )?>

       <h2> <?php echo $question; ?></h2>
  <?php echo  $this->convertTextAreaToRadioButtons($reponse,'reponse_'.$i); ?>
  
        
    <?php endforeach ;?>
    <input type="text" name ='user_id' value='<?php echo get_current_user_id() ?>'>
    <input type="text" name ='form_id' value='<?php echo $form_id; ?>'>
    <input type="text" name ='form_data' value ='<?php echo  json_encode($form_fields) ?>'>
    <button type="submit">Valider</button>
</form>
    <?php var_dump($form_fields['questions']); ?>
<?php
    return ob_get_clean();
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

    function create_custom_page() {
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
            ob_start();?>
            <pre><?php var_dump($_POST); ?></pre>

            <?php
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
   
}
