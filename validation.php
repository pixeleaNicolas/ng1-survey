<?php 

echo "validation";
extract($_POST);
$data= json_decode(stripslashes($form_data),true);
$form =$data['questions']= json_decode(stripslashes($form_data),true);
$questions=$form['questions'];

$i=0;
foreach ($questions as  $question) {
    $i++;
   
   echo "Question : " . $question['question'] . "<br>";
   echo "Catégorie : " . implode(', ', $question['categorie']) . "<br>";
  
   Ng1SondagePlugin::convertTextAreaToRadioButtons( $question['reponse'],"reponse_".$i,${"reponse_".$i});

}