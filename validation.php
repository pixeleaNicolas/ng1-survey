<?php 

extract($_POST);
include 'view/tpl-chart.php';?>
<?php
include 'view/tpl-response.php';
?>
<form action="resultat" method="post">
<?php
    foreach ($_POST as $key => $value) {
        if (is_array($value)) {
            foreach ($value as $subKey => $subValue) {
                echo "<input type='hidden' name='" . $key . "[" . $subKey . "]' value='" . $subValue . "'>";
            }
        } else {
            echo "<input type='hidden' name='" . $key . "' value='" . $value . "'>";
        }
    }
?>
<button type="submit">Valider les réponses</button>
</form>