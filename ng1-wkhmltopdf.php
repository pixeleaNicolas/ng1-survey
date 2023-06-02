<?php
/**
 * Classe Ng1Wkhtmltopdf pour la génération de fichiers PDF à partir d'URL, de chaînes HTML ou de fichiers HTML.
 */
class Ng1Wkhtmltopdf {
    /**
     * @var string L'entrée (URL, chaîne HTML ou fichier HTML).
     */
    private $input;

    /**
     * @var string Le chemin de sortie du fichier PDF.
     */
    private $output;

    /**
     * @var string Le chemin vers l'exécutable de wkhtmltopdf.
     */
    private $wkhtmltopdfPath;

    /**
     * Constructeur de la classe Ng1Wkhtmltopdf.
     *
     * @param string $input L'entrée (URL, chaîne HTML ou fichier HTML).
     * @param string $output Le chemin de sortie du fichier PDF.
     * @param string|null $wkhtmltopdfPath Le chemin vers l'exécutable de wkhtmltopdf (optionnel, par défaut : '/usr/local/bin/wkhtmltopdf').
     */
    public function __construct($input, $output='temp/temp.pdf', $wkhtmltopdfPath = '/usr/local/bin/wkhtmltopdf') {
        $this->input = $input;
        $this->output = $output;
        $this->wkhtmltopdfPath = $wkhtmltopdfPath;

        if (!file_exists($output)) {
            $this->generatePDF();
        }
    }

    /**
     * Génère le fichier PDF en utilisant wkhtmltopdf.
     *
     * @return bool Retourne true si la génération du PDF est réussie, false sinon.
     */
    private function generatePDF() {
        $command = '';

        if (filter_var($this->input, FILTER_VALIDATE_URL)) {
            $command = "{$this->wkhtmltopdfPath} {$this->input} {$this->output}";
        } elseif (is_string($this->input)) {
            $tempFile = tempnam(sys_get_temp_dir(), 'html');
            file_put_contents($tempFile, $this->input);
            $command = "{$this->wkhtmltopdfPath} {$tempFile} {$this->output}";
        } elseif (is_file($this->input)) {
            $command = "{$this->wkhtmltopdfPath} {$this->input} {$this->output}";
        } else {
            return false;
        }

        exec($command, $output, $returnVar);

        if ($returnVar === 0) {
            return true;
        } else {
            return false;
        }
    }
}


