<?php

require_once('UKM/vendor/autoload.php');
require_once(__DIR__ . '/../class/UKMstimuladmin.class.php');
use UKMNorge\Nettverk\Administrator;

$soknader = new UkmStimulAdmin();
$rapporteralleids = $soknader->getAllRapportIDs();
echo '<pre>';
var_dump($rapporteralleids);
echo '</pre>';


$jotformAPI = new JotForm(UKM_JOTFORM_API_KEY);
$RapportFormID = UKM_JOTFORM_RAPPORT_ID;
$submissions = $jotformAPI->getFormSubmissions($RapportFormID,0,7);
// echo '<pre>';
// var_dump($submissions);
// echo '</pre>';
foreach($submissions as $submission) {
    $jotformid = $submission['id'];
    $sql_values =   array(
        "jotformID" => $submission['id'],
        "innlevert" => $submission['created_at'],
        "organisasjonsnavn" => $submission['answers'][23]['answer'],
        "fylke" => $submission['answers'][21]['answer'],
        "mobilnr" => $submission['answers'][31]['prettyFormat'],
        "epost" => $submission['answers'][32]['answer'],
        "prosjekt_navn" => $submission['answers'][19]['answer'],
        "prosjekt_ansvarlig" => $submission['answers'][27]['answer'],
        "prosjekt_start" => $submission['answers'][36]['answer']['datetime'],
        "prosjekt_slutt" => $submission['answers'][38]['answer']['datetime'],
        "prosjekt_oppsummering" => $submission['answers'][45]['answer'],
        "prosjekt_maal" => $submission['answers'][51]['answer'],
        "prosjekt_oppfylte_maal" => $submission['answers'][53]['answer'],
        "prosjekt_aktiviteter" => $submission['answers'][59]['answer'],
        "prosjekt_aktiviteter_beskrivelse" => $submission['answers'][61]['answer'],
        "prosjekt_maalgrupper" => $submission['answers'][67]['answer'],
        "prosjekt_maalgruppe_beskrivelse" => $submission['answers'][69]['answer'],
        "prosjekt_deltakere" => $submission['answers'][75]['answer'],
        "prosjekt_deltakere_berort" => $submission['answers'][77]['answer'],
        "prosjekt_ungdom_innvolvert" => $submission['answers'][79]['answer'],
        "prosjekt_fornoydmed" => $submission['answers'][85]['answer'],
        "prosjekt_gjort_annerledes" => $submission['answers'][87]['answer'],
        "prosjekt_tips" => $submission['answers'][89]['answer'],
        "tall_egenandel_soker" => $submission['answers'][98]['answer'],
        "tall_innvilget_stotte" => $submission['answers'][100]['answer'],
        "tall_innvilget_fylke" => $submission['answers'][102]['answer'],
        "tall_innvilget_annen" => $submission['answers'][104]['answer'],
        "tall_innvilget_annen_forklar" => $submission['answers'][105]['answer'],
        "tall_annen_finansiering" => $submission['answers'][107]['answer'],
        "tall_annen_finansiering_forklar" => $submission['answers'][108]['answer'],
        "tall_sum_inntekter" => $submission['answers'][110]['answer'],
        "tall_prosjekt_administrasjon" => $submission['answers'][116]['answer'],
        "tall_honorarer" => $submission['answers'][118]['answer'],
        "tall_leiekostnader" => $submission['answers'][120]['answer'],
        "tall_utstyr_rekvisita" => $submission['answers'][122]['answer'],
        "tall_reise_overnatting_besp" => $submission['answers'][124]['answer'],
        "tall_annet" => $submission['answers'][126]['answer'],
        "tall_annet_forklar" => $submission['answers'][127]['answer'],
        "tall_sum_utgifter" => $submission['answers'][129]['answer'],
        );
echo '<pre>';
var_dump($jotformid);
echo '</pre>';
    if (!in_array($jotformid, $rapporteralleids)) {
        $soknader->addRapport($sql_values);
    }
}
// $uploads = $jotformAPI->getFormFiles($runde[soknadsrunde_id]);
// foreach($uploads as $upload) {
//     $sql_values =   array(
//         "id" => $upload['id'],
//         "name" => $upload['name'],
//         "type" => $upload['type'],
//         "size" => $upload['size'],
//         "username" => $upload['username'],
//         "form_id" => $upload['form_id'],
//         "submission_id" => $upload['submission_id'],
//         "uploaded" => $upload['uploaded'],
//         "date" => $upload['date'],
//         "url" => $upload['url']
//     );
//     $soknader->addUpload($sql_values);
// }