<div class="message-inscription-qvt">
   
   <p>Pour accéder au questionnaire sur la QVT,<br> veuillez vous connecter ou vous inscrire si vous n'avez pas encore de compte.</p>


</div>
<div class='cols'>
    <div class='cols__col'>
        <H2>Connexion</H2>
<?php

wp_login_form();

        ?>


        </div>
        <div class='cols__col'>
        <H2>Inscription</H2>
        <?php echo do_shortcode('[inscription_form]');
         ?>
        
        </div>

    </div>