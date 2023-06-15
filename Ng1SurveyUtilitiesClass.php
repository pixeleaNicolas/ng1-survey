<?php
class Ng1SurveyUtilities {
    public static function svgToDraw($svg) {
        $draw = str_replace('var(--wp--preset--color--second)', '#dddddd', $svg);
        $draw = str_replace('<svg', '<draw', $svg);
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
    public static function ng1_check_attachment_file_exist($filename){
        if(!empty($filename)){
        $existing_attachment = get_posts(array(
            'post_type'      => 'attachment',
            'post_mime_type' => 'application/pdf',
            'post_status'    => 'inherit',
            'posts_per_page' => 1,
            'title'          => $filename,
            'fields'         => 'ids'
        ));
        if($existing_attachment){
            return $existing_attachment[0];
        }
    }
    return;
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
 * Encode the input value.
 *
 * @param mixed $input The input value to encode.
 * @return string The encoded input value.
 */
public static function inputEncode($input) {
    $encodedString = json_encode($input);
    $encodedString = mb_convert_encoding($encodedString, 'UTF-8');
    $encodedString = urlencode($encodedString);
    return $encodedString;
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
    public static function minimizeHTML($html) {
        $pattern = [
            '/ {2,}/',     // Supprimer les espaces multiples
            '/\t+/',       // Supprimer les tabulations
            '/\r?\n/'      // Supprimer les sauts de ligne
        ];
    
        $replacement = [' ', '', ''];
    
        $minimizedHTML = preg_replace($pattern, $replacement, $html);
    
        return $minimizedHTML;
    }
    public static function formatHTML($html) {
        return Ng1SurveyUtilities::minimizeHTML($html);
    }
    
}