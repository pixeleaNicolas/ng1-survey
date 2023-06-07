<?php
 $nonce =  Ng1SondagePlugin::generateUniqueToken();
 $post_id=false;
 $current=1;

if(!empty($_GET['id'])):?>
    <?php  $post_id=$_GET['id'];
    $identifier= get_field('identifier',$post_id);
    if(empty($identifier)){
        $nonce =  Ng1SondagePlugin::generateUniqueToken();
    }else{
        $nonce =$identifier;
        $current = get_field('current',$post_id);
    }
    ?>
<?php endif; ?>

<form id='survey-form' action="/validation" method="post">
    <div class="ng1-survey__items formWitAutoNextJs" data-current='1'>
        <?php foreach($form_fields['questions'] as  $item): 
        $i++; extract($item )?>
    <div class="ng1-survey__item">  
        <h4> <?php echo $question; ?></h4>
        <?php echo  Ng1SondagePlugin::convertTextAreaToRadioButtons($reponse,'reponse_'.$i,$i,$categorie[0],$val='',$readonly=false,$post_id); ?>
        <div class='ng1-survey__item__counter'><?php echo $i." sur ".count($form_fields['questions']); ?>
        </div>
    </div>  
        <?php endforeach ;?>
    </div>
    <div class="formWitAutoNextPreviousJs">Previous</div>
    <div class="formWitAutoNextNextJs">Next</div>
    <input type="hidden" name ='user_id' value='<?php echo get_current_user_id() ?>'>
    <input type="hidden" name ='form_id' value='<?php echo $form_id; ?>'>
    <input type="hidden" name ='form_data' value ='<?php echo   urlencode(json_encode($form_fields)) ?>'>
    <input type="hidden" id='reponses' name='reponses' value=''>
    <input type="hidden" id='current' name='current' value='<?php echo $current; ?>'>
    <input type="hidden" id='nbpoints' name='nbpoints' value=''>
    <input type="hidden" name="identifier" value="<?php echo esc_attr( $nonce ); ?>">
    <button  class="form-action" name="submitAction" data-action="/validation">Valider</button>

    <div id="saveSurvey">Sauvegarde</div>        
</form>
