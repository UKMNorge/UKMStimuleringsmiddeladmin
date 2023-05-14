<?php

use UKMNorge\Database\SQL\Query;
use UKMNorge\Database\SQL\Insert;
use UKMNorge\Database\SQL\Update;


require_once('UKM/Autoloader.php');

/**
 * Henter og skriver fra databasen
 */
class UkmStimulAdmin {


	/**
	 * protecting  the input form the SQL injection and script injection.
	 * @param $data
	 * @return mixed
	 */
	public function sanitizer ($data)
	{
		if (!empty($data)) {
			foreach ($data as $key => $dataValue) {
				if (empty($dataValue)) {
					continue;
				}
				$dataValue = strip_tags($dataValue);
				$data[$key] = mysql_real_escape_string($dataValue, $this->link);
			}
		}

		return $data;
	}

	/**
	 * Henter alle id'er for en søknadsrunde
	 *
	 * @return void
	 */
	public function getAllIDs($runde) {
		$sql = new Query(
				"SELECT soknadID ".
				"FROM `ukm_stimuladmin_soknader`".
				"WHERE `soknadsrunde` = ".$runde."");
		$res = $sql->run();
		
		$ids = array();
		while($row = Query::fetch($res)) {
			$soknadid = new stdClass();
			$soknadid->id = $row['soknadID'];
			$ids[] = $soknadid;
		}
		return $ids;
	}

	public function getAllRapportIDs() {
		$sql = new Query(
				"SELECT jotformID FROM `ukm_stimuladmin_rapporter`");
		$res = $sql->run();
		
		$ids = array();
		while($row = Query::fetch($res)) {
			$soknadid = new stdClass();
			$soknadid->id = $row['jotformID'];
			$ids[] = $row['jotformID'];
		}
		return $ids;
	}

	/**
	 * Sjekker om fylket har søknader i gjeldende søknadsrunde
	 *
	 * @return void
	 */
	public function harSoknader($runde, $fylke) {
		$sql = new Query(
				"SELECT soknadID FROM `ukm_stimuladmin_soknader` WHERE `soknadsrunde` = $runde AND `fylke` = '$fylke'"
			);
		// var_dump($sql);
			$res = $sql->run();
		$fylker = [];
		while( $row = Query::fetch( $res ) ) {
			$fylker[] = $row;
		}		
		return $fylker;

	}

/**Sjekker hor mange søknader et fylke har levert inn kommentar på */
	public function harKommentert($runde, $fylke) {
		$sql = new Query(
				"SELECT fylke_kommentar FROM `ukm_stimuladmin_soknader` WHERE `soknadsrunde` = $runde AND `fylke` = '$fylke'"
			);
		// var_dump($sql);
			$res = $sql->run();
		$kommentarer = [];
		while( $row = Query::fetch( $res ) ) {
			if ($row[fylke_kommentar] != '') {
				$kommentarer[] = $row;
			}
		}		
		return $kommentarer;

	}

	/**
	 * Teller antall fylker som har sendt søknad
	 *
	 * @param [type] $runde
	 * @return void
	 */
	public function countFylke($runde) {
		$sql = new Query(
				"SELECT fylke AS fylke, count(*) AS antall, SUM(tall_ukmnorge) AS totaltsokt ".
				"FROM `ukm_stimuladmin_soknader` WHERE `soknadsrunde` = ".$runde." GROUP BY fylke"
			);
			$res = $sql->run();
		$fylkestats = [];
		while( $row = Query::fetch( $res ) ) {
			$fylkestats[] = $row;
		}		
		return $fylkestats;

	}
	/**
	 * Legger fylkene som har levert inn søknad i et array
	 *
	 * @param [type] $runde
	 * @return void
	 */
	public function hvilkeFylker($runde) {
		$sql = new Query(
				"SELECT fylke AS fylke, count(*) AS antall ".
				"FROM `ukm_stimuladmin_soknader` WHERE `soknadsrunde` = ".$runde." GROUP BY fylke"
			);
			$res = $sql->run();
		$fylker = [];
		while( $row = Query::fetch( $res ) ) {
			$fylker[] = $row;
		}		
		return $fylker;

	}
	/**
	 * Henter visning for fylke eller ei
	 *
	 * @return void
	 */
	public function getVisFylke($runde) {
		$sql = new Query(
				"SELECT visfylke ".
				"FROM `ukm_stimuladmin_config`".
				"WHERE `soknadsrunde_id` = ".$runde.""
		);
		$res = $sql->run();
		$row = Query::fetch( $res )	;
		return $row;
	}


	/**
	 * Legger til søknader fra JotForm i databasen
	 *
	 * @param [type] $sql_values
	 * @return void
	 */
	public function addSoknad($sql_values) {

		$sql = new Insert('ukm_stimuladmin_soknader');
		foreach ($sql_values as $key => $val) {
			$sql->add($key, $val);
		}

		try {
			$id = $sql->run();

		} catch( Exception $e ) {

		}
	}

	public function addUpload($sql_values) {

		$sql = new Insert('ukm_stimuladmin_uploads');
		foreach ($sql_values as $key => $val) {
			$sql->add($key, $val);
		}

		try {
			$id = $sql->run();

		} catch( Exception $e ) {

		}
	}

	public function addRapport($sql_values) {

		$sql = new Insert('ukm_stimuladmin_rapporter');
		foreach ($sql_values as $key => $val) {
			$sql->add($key, $val);
		}
var_dump('och');
		try {
			$id = $sql->run();

		} catch( Exception $e ) {

		}
	}

	/**
	 * Henter alle data om en søknad basert på id
	 *
	 * @param [type] $soknad_id
	 * @return void
	 */
	public function getSoknadFromID($soknad_id) {
		$sql = new Query(
				"SELECT * ".
				"FROM `ukm_stimuladmin_soknader`".
				"WHERE soknadID = '#sok_id'",
			array("sok_id" => $soknad_id)
		);
		$res = $sql->run('array');
		
		return $this->getSoknadData( $res );
	}
	/**
	 * Henter utvalgte felter fra alle søknader i en runde
	 *
	 * @param [type] $runde
	 * @return void
	 */
	public function getAlleSoknader($runde) {
		$sql = new Query(
				"SELECT soknadID,fylke,organisasjonsnavn,prosjekt_navn,prosjektansvarlig,fylke_sist_kommentert, belop ".
				"FROM `ukm_stimuladmin_soknader`".
				"WHERE `soknadsrunde` = ".$runde." ORDER BY fylke"
		);
		$res = $sql->run();
		$soknader = [];
		while( $row = Query::fetch( $res ) ) {
			$soknader[] = $row;
		}		
		return $soknader;
	}

	public function getAlleUploads($runde) {
		$sql = new Query(
				"SELECT * ".
				"FROM `ukm_stimuladmin_uploads`".
				"WHERE `form_id` = ".$runde." "
		);
		$res = $sql->run();
		$uploads = [];
		while( $row = Query::fetch( $res ) ) {
			$uploads[] = $row;
		}		
		return $uploads;
	}

	/**
	 * Henter utvalgte felter fra alle søknader i en runde for et fylke
	 *
	 * @param  [type] $runde
	 * @param  [type] $fylke
	 * @return void
	 */
	public function getAlleSoknaderFylke($runde, $fylke) {
		$sql = new Query(
				"SELECT soknadID,fylke,organisasjonsnavn,prosjekt_navn,prosjektansvarlig,fylke_sist_kommentert, belop ".
				"FROM `ukm_stimuladmin_soknader`".
				"WHERE `soknadsrunde` = '$runde' AND `fylke` = '$fylke'"
		);
		$res = $sql->run();
		$soknader = [];
		while( $row = Query::fetch( $res ) ) {
			$soknader[] = $row;
		}		
		return $soknader;
	}
	/**
	 * Henter alle søknadsrunder som er i databasen
	 *
	 * @return void
	 */
	public function getAlleRunder() {
		$sql = new Query(
				"SELECT soknadsrunde_id, soknadsrunde_navn ".
				"FROM `ukm_stimuladmin_config`"
		);
		$res = $sql->run();
		$allerunder = [];
		while( $row = Query::fetch( $res ) ) {
			$allerunder[] = $row;
		}		
		return $allerunder;
	}
	/**
	 * Henter id og navn på søknadsrunde som er aktiv
	 *
	 * @return void
	 */
	public function getGjeldendeRunde(String $idonly = '') {
		$sql = new Query(
				"SELECT soknadsrunde_id, soknadsrunde_navn, visfylke ".
				"FROM `ukm_stimuladmin_config`".
				"WHERE `aktiv` = 1"
		);
		$res = $sql->run();
		$row = Query::fetch( $res )	;
		if ($idonly == 'id') {
			return $row[soknadsrunde_id];
		}
		else {
			return $row;
		}
	}
	/**
	 * Setter id og navn på søknadsrunde som er aktiv
	 *
	 * @return void
	 */
	public function addRunde($runde, $rundenavn, $oldround) {
		$sql = new Insert('ukm_stimuladmin_config');
		$sql->add("soknadsrunde_id", $runde);
		$sql->add("soknadsrunde_navn", $rundenavn);
		try {
			$res = $sql->run();

		} catch( Exception $e ) {

		}
		$sql = new Update('ukm_stimuladmin_config',array('soknadsrunde_id'=>$oldround));
		$sql->add("aktiv", '0');
		$sql->run();
		$sql = new Update('ukm_stimuladmin_config',array('soknadsrunde_id'=>$runde));
		$sql->add("aktiv", '1');
		$sql->run();
	}
	/**
	 * setter hvilken  søknadsrunde som er aktiv
	 *
	 * @return void
	 */
	public function setGjeldendeRunde($old, $new) {
		$sql = new Update('ukm_stimuladmin_config',array('soknadsrunde_id'=>$old));
		$sql->add("aktiv", '0');
		$sql->add("visfylke", 'off');
		$sql->run();
		$sql = new Update('ukm_stimuladmin_config',array('soknadsrunde_id'=>$new));
		$sql->add("aktiv", '1');
		$sql->run();
	}
	public function setVisFylke($runde, $visfylke) {
		if ($visfylke == '') {$visfylke = 'off';}
		$sql = new Update('ukm_stimuladmin_config',array('soknadsrunde_id'=>$runde));
		$sql->add("visfylke", $visfylke);
		$sql->run();
	}

	public function setFylkeKommentar($soknadid, $kommentar) {
		$sql = new Update('ukm_stimuladmin_soknader',array('soknadID'=>$soknadid));
		$sql->add("fylke_kommentar", $kommentar);
		$sql->add("fylke_sist_kommentert", time());
		$sql->run();
	}
	/**
	 * Hjelpefunksjon for getSoknadFromID
	 *
	 * @param [type] $res
	 * @return void
	 */
	public function getSoknadData( $res ) {
		$soknad = [];
		foreach ($res as $key => $val) {
			$soknad[$key] = $val;
		}
		return $soknad;
	}
}