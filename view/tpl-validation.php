<div class="content-validation">
    <?php 
    extract($_POST);
    include 'tpl-response.php';
    ?>
    <form action="resultat" method="post">
        <?php $questions=json_encode($data['questions']); ?>
        <input type='hidden' name='all_questions' value='<?php echo Ng1SondagePlugin::inputEncode($questions); ?>'>
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
        <button class="btn_submit" type="submit">Valider les réponses</button>
    </form>
</div>