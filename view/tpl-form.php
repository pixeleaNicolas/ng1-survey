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
<?php if(!empty($_GET['index'])) $current = $_GET['index'];?>

<form id='survey-form' class="ng1-survey__form" action="<?php echo home_url("/validation"); ?>" method="post">

    <div class="ng1-survey__items formWitAutoNextJs" data-current='1'>
        <?php foreach($form_fields['questions'] as  $item): 
        $i++; extract($item )?>
       
    <div class="ng1-survey__item">
        <div class="ng1-survey__item__row ng1-survey__item__row_1 ng1-survey__item__cols">  
            <div class="ng1-survey__item__col ng1-survey__item__col_1">
                <div class="ng1-survey__item__category">  
                    <?php echo Ng1SondagePlugin::get_survey_category($categorie) ?>
                </div>
                <div class="ng1-survey__item__question">  
                    <?php echo $question; ?>
                    <div class="ng1-survey__item__label" >Choisissez parmi l’une des réponses suivantes </div>
                </div>
            </div>
            <div class="ng1-survey__item__col ng1-survey__item__col_2">
            <div class="btn goToQuestionJs" data-index="1">
                    <svg  xmlns="http://www.w3.org/2000/svg" width="30" height="28.176" viewBox="0 0 30 28.176"><path d="M168.021-774.824a2.02,2.02,0,0,1-1.527-.593,2.083,2.083,0,0,1-.57-1.5,2,2,0,0,1,.57-1.482,2.064,2.064,0,0,1,1.527-.57h11.763a7.124,7.124,0,0,0,4.97-1.915,6.163,6.163,0,0,0,2.1-4.742,6.163,6.163,0,0,0-2.1-4.742,7.124,7.124,0,0,0-4.97-1.915H168.067l3.009,2.964a2.229,2.229,0,0,1,.616,1.5,1.711,1.711,0,0,1-.616,1.413,1.913,1.913,0,0,1-1.459.638,1.983,1.983,0,0,1-1.459-.593l-6.52-6.565a1.768,1.768,0,0,1-.479-.638,2.027,2.027,0,0,1-.16-.821,2.077,2.077,0,0,1,.16-.8,1.723,1.723,0,0,1,.479-.661l6.52-6.565a1.983,1.983,0,0,1,1.459-.593,1.983,1.983,0,0,1,1.459.593,2.111,2.111,0,0,1,.593,1.482,1.956,1.956,0,0,1-.593,1.436l-3.009,3.055h11.672a11.218,11.218,0,0,1,7.91,3.123A10.09,10.09,0,0,1,191-785.629a10.111,10.111,0,0,1-3.351,7.659,11.174,11.174,0,0,1-7.91,3.146Z" transform="translate(191 -774.824) rotate(180)" fill="#ffffff"/></svg>
                    Recommencer
                </div> 
                <?php $pdf_url= wp_get_attachment_url('17298') ?>
                
                <a href='<?php echo $pdf_url;?>' class="btn" download>
                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="18.228" viewBox="0 0 30 18.228"><path d="M20.149,17.4a1.511,1.511,0,0,1-.494-1.161,1.767,1.767,0,0,1,.464-1.221L24.4,10.737H8.133A1.573,1.573,0,0,1,6.492,9.126a1.631,1.631,0,0,1,.45-1.154,1.553,1.553,0,0,1,1.191-.482H24.4L20.119,3.175a1.433,1.433,0,0,1-.482-1.134A1.6,1.6,0,0,1,20.155.892,1.538,1.538,0,0,1,21.266.428a1.752,1.752,0,0,1,1.172.464l7.1,7.1a2.5,2.5,0,0,1,.321.528A1.467,1.467,0,0,1,30,9.14a1.437,1.437,0,0,1-.143.616,1.694,1.694,0,0,1-.321.482l-7.1,7.1a1.57,1.57,0,0,1-1.169.535A1.5,1.5,0,0,1,20.149,17.4Zm-18.526.828A1.572,1.572,0,0,1,.47,17.761,1.544,1.544,0,0,1,0,16.623V1.641A1.585,1.585,0,0,1,.477.478,1.575,1.575,0,0,1,1.636,0,1.518,1.518,0,0,1,2.8.478a1.642,1.642,0,0,1,.446,1.163V16.623a1.576,1.576,0,0,1-.452,1.138A1.556,1.556,0,0,1,1.623,18.228Z" transform="translate(30 18.228) rotate(180)" fill="#ffffff"/></svg>
                    Version papier
                </a> 

            </div>

        </div>
 
        <div class="ng1-survey__item__row ng1-survey__item__row_2 ng1-survey__loader__container">
        <div class="ng1-survey__loader">
<svg class="ng1-survey__loader__svg" version="1.1" id="L2" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
  viewBox="0 0 100 100" enable-background="new 0 0 100 100" xml:space="preserve">
<circle fill="none" stroke="var(--wp--preset--color--first)" stroke-width="4" stroke-miterlimit="10" cx="50" cy="50" r="48"/>
<line fill="none" stroke-linecap="round" stroke="var(--wp--preset--color--first)" stroke-width="4" stroke-miterlimit="10" x1="50" y1="50" x2="85" y2="50.5">
  <animateTransform 
       attributeName="transform" 
       dur="2s"
       type="rotate"
       from="0 50 50"
       to="360 50 50"
       repeatCount="indefinite" />
</line>
<line fill="none" stroke-linecap="round" stroke="var(--wp--preset--color--first)" stroke-width="4" stroke-miterlimit="10" x1="50" y1="50" x2="49.5" y2="74">
  <animateTransform 
       attributeName="transform" 
       dur="15s"
       type="rotate"
       from="0 50 50"
       to="360 50 50"
       repeatCount="indefinite" />
</line>
</svg>

</div>
            <div class="ng1-survey__questions">
            <?php echo  Ng1SondagePlugin::convertTextAreaToRadioButtons($reponse,'reponse_'.$i,$i,$categorie[0],$val='',$readonly=false,$post_id,$random=true); ?>
            </div>
            <div class="ng1-survey__item__pagination">
            <div class="btn_small btn_prev ng1-survey__item__pagination__item formWitAutoNextPreviousJs">
            <svg class="ng1-survey__item__pagination__icon" xmlns="http://www.w3.org/2000/svg" width="30" height="29.085" viewBox="0 0 30 29.085"><path d="M13.052,28.428.657,16.081A1.774,1.774,0,0,1,.164,15.4,2.259,2.259,0,0,1,0,14.531a1.97,1.97,0,0,1,.164-.8,1.82,1.82,0,0,1,.493-.657L13.1.588A1.964,1.964,0,0,1,14.6,0a2.048,2.048,0,0,1,1.5.681,2.224,2.224,0,0,1,.587,1.5,1.964,1.964,0,0,1-.587,1.5L7.371,12.419H27.887A2.085,2.085,0,0,1,30,14.531a2.05,2.05,0,0,1-2.113,2.16H7.371L16.15,25.47A2.085,2.085,0,0,1,16.737,27a1.939,1.939,0,0,1-.634,1.432,2.119,2.119,0,0,1-1.549.657A1.97,1.97,0,0,1,13.052,28.428Z" transform="translate(30 29.085) rotate(180)" fill="#ffffff"/></svg>
                <span class="ng1-survey__item__pagination__txt">Précédent</span>
            </div>
            <div class='ng1-survey__item__counter'><span class="goToQuestionJs" data-index="<?php echo $i; ?>"> <?php echo $i; ?></span> sur <span class="goToQuestionJs" data-index="<?php echo count($form_fields['questions']); ?>"> <?php echo count($form_fields['questions']); ?></span>
            </div>
            <div class="btn_small btn_next ng1-survey__item__pagination__item  formWitAutoNextNextJs">
            <span class="ng1-survey__item__pagination__txt">Suivant</span>
                <svg class="ng1-survey__item__pagination__icon" xmlns="http://www.w3.org/2000/svg" width="30" height="29.085" viewBox="0 0 30 29.085"><path d="M13.052,28.428.657,16.081A1.774,1.774,0,0,1,.164,15.4,2.259,2.259,0,0,1,0,14.531a1.97,1.97,0,0,1,.164-.8,1.82,1.82,0,0,1,.493-.657L13.1.588A1.964,1.964,0,0,1,14.6,0a2.048,2.048,0,0,1,1.5.681,2.224,2.224,0,0,1,.587,1.5,1.964,1.964,0,0,1-.587,1.5L7.371,12.419H27.887A2.085,2.085,0,0,1,30,14.531a2.05,2.05,0,0,1-2.113,2.16H7.371L16.15,25.47A2.085,2.085,0,0,1,16.737,27a1.939,1.939,0,0,1-.634,1.432,2.119,2.119,0,0,1-1.549.657A1.97,1.97,0,0,1,13.052,28.428Z" transform="translate(30 29.085) rotate(180)" fill="#ffffff"/></svg>
            </div>
           
            </div>

        </div>
    </div>  
        <?php endforeach ;?>
    </div>

    <input type="hidden" name ='user_id' value='<?php echo get_current_user_id() ?>'>
    <input type="hidden" name ='form_id' value='<?php echo $form_id; ?>'>
    <input type="hidden" name ='form_data' value ='<?php echo   urlencode(json_encode($form_fields)) ?>'>
    <input type="hidden" id='reponses' name='reponses' value=''>
    <input type="hidden" id='current' name='current' value='<?php echo $current; ?>'>
    <input type="hidden" id='nbpoints' name='nbpoints' value=''>
    <input type="hidden" name="identifier" value="<?php echo esc_attr( $nonce ); ?>">
    <div class='ng1-survey__message'>
          
        <p>Merci d'avoir rempli le questionnaire QVT.</p>
        <p> Votre participation est précieuse pour évaluer votre profil et améliorer notre approche de la Qualité de Vie au Travail.</p>
        <button  class="btn_submit form-action" name="submitAction" data-action="/validation">Vérifier mes réponses</button>
 
        </div>

    


</form>
