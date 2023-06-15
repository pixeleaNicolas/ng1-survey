<?php 
$fields= get_fields();
if(!empty($fields)) extract($fields);

$response_acf_group_key = 'group_647edab2e7863'; // Remplacez par la clé de votre groupe de champs
$reponse_id = "reponse_1"; // Remplacez par la réponse sélectionnée
$all_responses =Ng1SondagePlugin::get_only_reponse_array($fields);
foreach ($all_responses as $key => $value) {
    Ng1SondagePlugin::show_form_response($response_acf_group_key, $key,$value);
}
