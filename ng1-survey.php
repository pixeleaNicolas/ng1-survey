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
    $sondage_plugin = new SondagePlugin();
    $sondage_plugin->init();
}

class SondagePlugin {

    
    public function init() {

        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    public function enqueue_scripts() {
        // Enregistrer et charger les scripts et les styles nécessaires pour le sondage
        wp_register_script('sondage-script', plugins_url('assets/js/function.js', __FILE__), array('jquery'), '1.0', true);
        wp_enqueue_script('sondage-script');

        wp_register_style('sondage-style', plugins_url('style.css', __FILE__));
        wp_enqueue_style('sondage-style');
    }


}
