
<form id='survey-form' action="/validation" method="post">
    <div class="ng1-survey__items formWitAutoNextJs" data-current='1'>
        <?php foreach($form_fields['questions'] as  $item): 
        $i++; extract($item )?>
    <div class="ng1-survey__item">  
        <h4> <?php echo $question; ?></h4>
        <?php echo  $this->convertTextAreaToRadioButtons($reponse,'reponse_'.$i,$i,$categorie[0]); ?>
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
    <input type="text" id='reponses' name='reponses' value=''>
    <input type="text" id='nbpoints' name='nbpoints' value=''>
    <button type="submit">Valider</button>
</form>
