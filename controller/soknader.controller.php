<?php

require_once('UKM/vendor/autoload.php');
require_once(__DIR__ . '/../class/UKMstimuladmin.class.php');
use UKMNorge\Nettverk\Administrator;

$soknader = new UkmStimulAdmin();
$runde = $soknader->getGjeldendeRunde();
$visfylke = $soknader->getVisFylke($runde['soknadsrunde_id']);
$fylkermedsoknad = $soknader->hvilkeFylker($runde['soknadsrunde_id']);
$fylkerinfo = $soknader->countFylke($runde['soknadsrunde_id']);
$uploads = $soknader->getAlleUploads($runde['soknadsrunde_id']);

/**Sjekk om kommentarer eksisterer og legg til i fylkerinfo */
foreach($fylkerinfo as $key => $value) {
    $fylkerinfo[$key]['antallkommentarer'] = count($soknader->harKommentert($runde['soknadsrunde_id'], $fylkerinfo[$key]['fylke']));
}

$bruker = new Administrator(get_current_user_id());
$fylker = [];
foreach($bruker->getOmrader('fylke') as $omrade) {
    $fylker[] = $omrade->getFylke()->getNavn();
}
if( isset( $_POST['fylkekommentar'] )) {

        $soknader->setFylkeKommentar($_POST['kommentarid'], $_POST['fylkekommentar']);
        echo "<script>location.replace('admin.php?page=UKMstimuleringsadmin_soknader&soknadid=" . $_POST['fylkekommentar'] ."');</script>";
}

if( isset( $_GET['soknadid'] ) ) {
    $id = $_GET['soknadid'];
    $soknad = $soknader->getSoknadFromID($id);
}
if (get_current_user_id() == 1) {
    if( isset( $_GET['fylke'] ) ) {
        $navnfylke = $_GET['fylke'];
        $allesoknader = $soknader->getAlleSoknaderFylke($runde['soknadsrunde_id'], $navnfylke);

    }
    else {
        $allesoknader = $soknader->getAlleSoknader($runde['soknadsrunde_id']);
    }
}
else {
    $allesoknader = [];
    foreach($fylker as $fylke) {
        // if ($fylke == 'Akershus' || $fylke == 'Buskerud' || $fylke == 'Østfold' ) {
        //     $fylke = 'Viken';
        // }
        // if ($fylke == 'Troms - Romsa - Tromssa' || $fylke == 'Finnmark - Finnmárku - Finmarkku') {
        //     $fylke = 'Troms og Finnmark';
        // }
        // if ($fylke == 'Vestfold' || $fylke == 'Telemark') {
        //     $fylke = 'Vestfold og Telemark';
        // }  
        if ($fylke == 'Troms - Romsa - Tromssa') {
            $fylke = 'Troms';
        }
        if ($fylke == 'Finnmark - Finnmárku - Finmarkku') {
            $fylke = 'Finnmark';
        }
        if ($fylke == 'Trøndelag - Trööndelage') {
            $fylke = 'Trøndelag';
        }
        if ($fylke == 'Nordland - Nordlánnda') {
            $fylke = 'Nordland';
        }
        $allesoknader = array_merge($allesoknader, $soknader->getAlleSoknaderFylke($runde['soknadsrunde_id'], $fylke));
    }
}


UKMstimuleringsadmin::addViewData('soknader', $allesoknader );
UKMstimuleringsadmin::addViewData('soknad', $soknad );
UKMstimuleringsadmin::addViewData('gjeldenderunde', $runde );
UKMstimuleringsadmin::addViewData('visfylke', $visfylke );
UKMstimuleringsadmin::addViewData('currentuserid', get_current_user_id() );
UKMstimuleringsadmin::addViewData('datafylker', $fylkerinfo);
UKMstimuleringsadmin::addViewData('uploads', $uploads);
UKMstimuleringsadmin::addViewData('fylke', $navnfylke);