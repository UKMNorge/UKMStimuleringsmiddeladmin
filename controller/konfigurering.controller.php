<?php

require_once('UKM/vendor/autoload.php');
require_once(__DIR__ . '/../class/UKMstimuladmin.class.php');
require_once(__DIR__ . '/../JotForm-api.php');


$soknader = new UkmStimulAdmin();
$runde = $soknader->getGjeldendeRunde();
$allerunder = $soknader->getAlleRunder();

// Hent alle skjemaer som starter med Tilskuddssoknad fra jotform
$jotformAPI = new JotForm(UKM_JOTFORM_API_KEY);
$skjemaer = $jotformAPI->getForms(0,100);
$tilskuddsskjemaer = array();
foreach($skjemaer as $skjema) {
    if (str_starts_with($skjema[title], 'Tilskuddssoknad')) {
        $tilskuddsskjemaer[] = $skjema;
    }   
}


// Sjekker om ny runde er valgt i konfigurering
if( isset( $_POST['skjemaid'] )) {
    if ($_POST['skjemaid'] == $runde[soknadsrunde_id] && $_POST['visfylke'] != $runde[visfylke]){
        $soknader->setVisFylke($runde[soknadsrunde_id], $soknader->sanitizer($_POST['visfylke']));
        echo "<script>location.replace('admin.php?page=UKMstimuleringsadmin_konfigurering');</script>";
    }
    if ($_POST['skjemaid'] == $runde[soknadsrunde_id]) {
        echo "<script>location.replace('admin.php?page=UKMstimuleringsadmin_konfigurering');</script>";
    }
    if ($_POST['skjemaid'] != $runde[soknadsrunde_id]) {
        if (array_search($_POST['skjemaid'], array_column($allerunder, 'soknadsrunde_id')) !== false) {
            $soknader->setGjeldendeRunde($runde[soknadsrunde_id], $soknader->sanitizer($_POST['skjemaid'])); 
            echo "<script>location.replace('admin.php?page=UKMstimuleringsadmin_konfigurering');</script>";
        }
        else {
            foreach($skjemaer as $skjema) {
                if ($skjema[id] == $_POST['skjemaid']) {
                    $soknader->addRunde($soknader->sanitizer($_POST['skjemaid']), $skjema[title], $runde[soknadsrunde_id]);
                }   
            }
            echo "<script>location.replace('admin.php?page=UKMstimuleringsadmin_konfigurering');</script>";
        }
    }
}



UKMstimuleringsadmin::addViewData('skjemaer', $tilskuddsskjemaer);
UKMstimuleringsadmin::addViewData('gjeldenderunde', $runde);
