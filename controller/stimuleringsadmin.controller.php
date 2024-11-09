<?php

require_once('UKM/vendor/autoload.php');
require_once(__DIR__ . '/../class/UKMstimuladmin.class.php');
use UKMNorge\Nettverk\Administrator;


$soknader = new UkmStimulAdmin();
$runde = $soknader->getGjeldendeRunde();
$visfylke = $soknader->getVisFylke($runde['soknadsrunde_id']);
$fylkermedsoknad = $soknader->hvilkeFylker($runde['soknadsrunde_id']);
$alleids = $soknader->getAllIDs($runde['soknadsrunde_id']);
$fylkestats = $soknader->countFylke($runde['soknadsrunde_id']);


/**
 * Hent antall søknader totalt og total søknadssum
 */
$antallsoknader['antall'] = count($alleids);
$soktom = 0;
foreach($fylkestats as $fylke) {
    $soktom += $fylke['totaltsokt'];
}
$antallsoknader['soktom'] = $soktom;


// Sjekk om pålogget fylke har søknader som skal vurderes
$bruker = new Administrator(get_current_user_id());
$fylker = [];
foreach($bruker->getOmrader('fylke') as $omrade) {
    $fylker[] = $omrade->getFylke()->getNavn();
}

$harsoknader= [];
$antallsoknaderfylke = 0;
foreach($fylker as $fylke) {
    if ($fylke == 'Trøndelag - Trööndelage') {
        $fylke = 'Trøndelag';
    }
    if ($fylke == 'Troms - Romsa - Tromssa') {
        $fylke = 'Troms';
    }
    if ($fylke == 'Finnmark - Finnmárku - Finmarkku') {
        $fylke = 'Finnmark';
    }
    if ($fylke == 'Nordland - Nordlánnda') {
        $fylke = 'Nordland';
    }
    $harsoknader = $soknader->harSoknader($runde['soknadsrunde_id'], $fylke);
    $antallsoknaderfylke = $antallsoknaderfylke + count($harsoknader);
}
/**
 * Sjekk om kommentarer eksisterer og legg til i fylkerinfo
 */
$fylkerinfo = $fylkestats;
foreach($fylkerinfo as $key => $value) {
    $fylkerinfo[$key]['antallkommentarer'] = count($soknader->harKommentert($runde['soknadsrunde_id'], $fylkerinfo[$key]['fylke']));
}


/**
 * Sjekker om det skal hentes ny data fra Jotform for en gitt runde
 */
if( isset( $_POST['fetchdata'] ) ) {
    $jotformAPI = new JotForm(UKM_JOTFORM_API_KEY);
    $submissions = $jotformAPI->getFormSubmissions($runde['soknadsrunde_id'],0,500);
    foreach($submissions as $submission) {
        $soknadid = preg_replace('/[^0-9]/', '', $submission['answers'][168]['answer']);
        $sql_values =   array(
            "jotformID" => $submission['id'],
            "soknadID" => preg_replace('/[^0-9]/', '', $submission['answers'][168]['answer']),
            "innlevert" => $submission['created_at'],
            "belop" => $submission['answers'][189]['answer'],
            "organisasjonsnavn" => $submission['answers'][19]['answer'],
            "gateadresse" => $submission['answers'][21]['answer'],
            "postnummer" => $submission['answers'][23]['answer'],
            "poststed" => $submission['answers'][24]['answer'],
            "fylke" => $submission['answers'][26]['answer'],
            "nettside" => $submission['answers'][28]['answer'],
            "prosjektansvarlig" => $submission['answers'][32]['answer'],
            "stilling" => $submission['answers'][34]['answer'],
            "mobilnr" => $submission['answers'][162]['prettyFormat'],
            "epost" => $submission['answers'][37]['answer'],
            "tidligere" => $submission['answers'][48]['answer'],
            "tidligere_hva" => $submission['answers'][159]['answer'],
            "prosjekt_navn" => $submission['answers'][56]['answer'],
            "prosjekt_beskrivelse_kort" => $submission['answers'][188]['answer'],
            "samarbeidspartnere" => $submission['answers'][60]['answer'],
            "malgruppe" => $submission['answers'][62]['answer'],
            "malgruppe_alder" => $submission['answers'][187]['answer'],
            "antall_deltakere" => $submission['answers'][66]['answer'],
            "oppstartsdato" => $submission['answers'][172]['answer']['datetime'],
            "rapportdato" => $submission['answers'][171]['answer']['datetime'],
            "prosjekt_oppnaelse" => $submission['answers'][82]['answer'],
            "prosjekt_beskrivelse_lang" => $submission['answers'][80]['answer'],
            "prosjekt_nytte" => $submission['answers'][84]['answer'],
            "tall_ukmnorge" => $submission['answers'][140]['answer'],
            "tall_egenandel" => $submission['answers'][141]['answer'],
            "tall_fylkeskommune" => $submission['answers'][142]['answer'],
            "tall_annen1" => $submission['answers'][143]['answer'],
            "tall_annen1_forklar" => $submission['answers'][103]['answer'],
            "tall_annen2" => $submission['answers'][144]['answer'],
            "tall_annen2_forklar" => $submission['answers'][106]['answer'],
            "tall_totale_inntekter" => $submission['answers'][145]['answer'],
            "tall_kontroll" => $submission['answers'][148]['answer'],
            "tall_prosjekt_administrasjon" => $submission['answers'][150]['answer'],
            "tall_honorarer" => $submission['answers'][151]['answer'],
            "tall_leiekostnader" => $submission['answers'][152]['answer'],
            "tall_utstyr_rekvisita" => $submission['answers'][153]['answer'],
            "tall_reise_overnatting" => $submission['answers'][154]['answer'],
            "tall_bespisning" => $submission['answers'][155]['answer'],
            "tall_annet" => $submission['answers'][156]['answer'],
            "tall_annet_forklar" => $submission['answers'][123]['answer'],
            "tall_totale_utgifter" => $submission['answers'][157]['answer'],
            "kommentar" => $submission['answers'][130]['answer'],
            "vedlegg_filnavn" => $submission['answers'][132]['answer'][0],
            "vedlegg_prettyname" => $submission['answers'][132]['prettyFormat'],
            "vedlegg_beskrivelse" => $submission['answers'][134]['answer'],
            "soknadsrunde" => $runde['soknadsrunde_id']
            );
        if (!in_array($soknadid, $alleids)) {
            $soknader->addSoknad($sql_values);
        }
    }
    $uploads = $jotformAPI->getFormFiles($runde['soknadsrunde_id']);
    foreach($uploads as $upload) {
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
            "skjematype" => 'soknad'
        );
        $soknader->addUpload($sql_values);
    }
    echo "<script>location.replace('admin.php?page=UKMstimuleringsadmin');</script>";
}




UKMstimuleringsadmin::addViewData('datafylker', $fylkerinfo);
UKMstimuleringsadmin::addViewData('antallsoknader', $antallsoknader);
UKMstimuleringsadmin::addViewData('aktivrunde', $runde);
UKMstimuleringsadmin::addViewData('visfylke', $visfylke);
UKMstimuleringsadmin::addViewData('hvilkefylker', $fylkermedsoknad);
UKMstimuleringsadmin::addViewData('soknaderfylke', $antallsoknaderfylke);
UKMstimuleringsadmin::addViewData('currentuserid', get_current_user_id() );
