<?php

require_once('UKM/vendor/autoload.php');
require_once(__DIR__ . '/../class/UKMstimuladmin.class.php');
use UKMNorge\Nettverk\Administrator;

$soknader = new UkmStimulAdmin();

$allerapporter = $soknader->getAlleRapporter();

if( isset( $_GET['rapportid'] ) ) {
    $id = $_GET['rapportid'];
    $rapport = $soknader->getRapportFromID($id);
}
// foreach($rapporteralleids as $rapporteralleid) {
// echo '<pre>';
// echo ($rapporteralleid);
// echo '</pre>';
// }
// die;
if( isset( $_POST['fetchreports'] ) ) {
    $rapporteralleids = $soknader->getAllRapportIDs();
    $uploadsalleids = $soknader->getAllUploadIDs();
    $jotformAPI = new JotForm(UKM_JOTFORM_API_KEY);
    $RapportFormID = UKM_JOTFORM_RAPPORT_ID;
    $submissions = $jotformAPI->getFormSubmissions($RapportFormID,0,800);
    $uploads = $jotformAPI->getFormFiles($RapportFormID);
    // echo '<pre>';
    // var_dump($submissions);
    // echo '</pre>';
    // die;
    foreach($submissions as $submission) {
        $rapportdbinsert = '';
        $regnskapdbinsert = '';
        $videodb = '';
        $pressedb = '';
        $bildedb = '';
        $jotformid = $submission['id'];
        $bilder = $submission['answers'][137]['answer'];
        $presseklipp = $submission['answers'][139]['answer'];
        $videoer = $submission['answers'][141]['answer'];
        $rapporterdb = $submission['answers'][143]['answer'];
        $regnskap = $submission['answers'][145]['answer'];
        $sql_values =   array(
            "jotformID" => $submission['id'],
            "innlevert" => $submission['created_at'],
            "organisasjonsnavn" => $submission['answers'][23]['answer'],
            "fylke" => $submission['answers'][21]['answer'],
            "mobilnr" => $submission['answers'][31]['answer'],
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
            "tall_annen_finansiering" => $submission['answers'][107]['answer'],
            "tall_sum_inntekter" => $submission['answers'][110]['answer'],
            "tall_prosjekt_administrasjon" => $submission['answers'][116]['answer'],
            "tall_honorarer" => $submission['answers'][118]['answer'],
            "tall_leiekostnader" => $submission['answers'][120]['answer'],
            "tall_utstyr_rekvisita" => $submission['answers'][122]['answer'],
            "tall_reise_overnatting_besp" => $submission['answers'][124]['answer'],
            "tall_annet" => $submission['answers'][126]['answer'],
            "tall_annet_forklar" => $submission['answers'][127]['answer'],
            "tall_sum_utgifter" => $submission['answers'][129]['answer'],
            "upload_forklaring" => $submission['answers'][147]['answer'],
        );

            foreach($bilder as $bilde) {
                $bildedb .= $bilde . PHP_EOL;
            }
            $sql_values += ["upload_bilder" => $bildedb];


            foreach($presseklipp as $presse) {
                $pressedb .= $presse . PHP_EOL;
            }
            $sql_values += ["upload_presseklipp" => $pressedb];


            foreach($videoer as $video) {
                $videodb .= $video . PHP_EOL;
            }
            $sql_values += ["upload_video" => $videodb];


            foreach($rapporterdb as $rapportdb) {
                $rapportdbinsert .= $rapportdb . PHP_EOL;
            }
            $sql_values += ["upload_rapport" => $rapportdbinsert];


            foreach($regnskap as $regnskapdb) {
                $regnskapdbinsert .= $regnskapdb . PHP_EOL;
            }
            $sql_values += ["upload_regnskap" => $regnskapdbinsert];

    //     echo '<pre>';
    // var_dump($sql_values);
    // echo '</pre>';
    // die;
        if (!in_array($jotformid, $rapporteralleids)) {
            $soknader->addRapport($sql_values);
        }
    }
    // $uploads = $jotformAPI->getFormFiles($runde[soknadsrunde_id]);
    foreach($uploads as $upload) {
        $uploadid = $upload['id'];
        $sql_values =   array(
            "id" => $upload['id'],
            "name" => $upload['name'],
            "type" => $upload['type'],
            "size" => $upload['size'],
            "username" => $upload['username'],
            "form_id" => $upload['form_id'],
            "submission_id" => $upload['submission_id'],
            "uploaded" => $upload['uploaded'],
            "date" => $upload['date'],
            "url" => $upload['url'],
            "skjematype" => 'rapport'
        );
        if (!in_array($uploadid, $uploadsalleids)) {
            $soknader->addUpload($sql_values);
        }
    }
    echo "<script>location.replace('admin.php?page=UKMstimuleringsadmin_rapporter');</script>";
}
UKMstimuleringsadmin::addViewData('rapporter', $allerapporter );
UKMstimuleringsadmin::addViewData('rapport', $rapport );
UKMstimuleringsadmin::addViewData('reports', '' );
UKMstimuleringsadmin::addViewData('currentuserid', get_current_user_id() );