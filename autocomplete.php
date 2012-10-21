<?php
// Returns data for autocompletion
//
// webtrees: Web based Family History software
// Copyright (C) 2012 webtrees development team.
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
//
// $Id: autocomplete.php 14388 2012-10-04 08:32:27Z greg $

define('WT_SCRIPT_NAME', 'autocomplete.php');
require './includes/session.php';

header('Content-Type: text/plain; charset=UTF-8');

// We have finished writing session data, so release the lock
Zend_Session::writeClose();

$term=safe_GET('term', WT_REGEX_UNSAFE); // we can search on '"><& etc.
$type=safe_GET('field');

switch ($type) {
case 'ASSO': // Associates of an individuals, whose name contains the search terms
	$data=array();
	// Fetch all data, regardless of privacy
	$rows=
		WT_DB::prepare(
			"SELECT 'INDI' AS type, i_id AS xref, i_file AS ged_id, i_gedcom AS gedrec, n_full".
			" FROM `##individuals`".
			" JOIN `##name` ON (i_id=n_id AND i_file=n_file)".
			" WHERE (n_full LIKE CONCAT('%', REPLACE(?, ' ', '%'), '%') OR n_surn LIKE CONCAT('%', REPLACE(?, ' ', '%'), '%')) AND i_file=? ORDER BY n_full"
		)
		->execute(array($term, $term, WT_GED_ID))
		->fetchAll(PDO::FETCH_ASSOC);
	// Filter for privacy - and whether they could be alive at the right time
	$pid=safe_GET_xref('pid');
	$event_date=safe_GET('event_date');
	$record=WT_GedcomRecord::getInstance($pid); // INDI or FAM
	$record=WT_GedcomRecord::getInstance($pid); // INDI or FAM
	$tmp=new WT_Date($event_date);
	$event_jd=$tmp->JD();
	// INDI
	$indi_birth_jd = 0;
	if ($record && $record->getType()=="INDI") {
		$indi_birth_jd=$record->getEstimatedBirthDate()->minJD();
	}
	// HUSB & WIFE
	$husb_birth_jd = 0;
	$wife_birth_jd = 0;
	if ($record && $record->getType()=="FAM") {
		$husb=$record->getHusband();
		if ($husb) {
			$husb_birth_jd = $husb->getEstimatedBirthDate()->minJD();
		}
		$wife=$record->getWife();
		if ($wife) {
			$wife_birth_jd = $wife->getEstimatedBirthDate()->minJD();
		}
	}
	foreach ($rows as $row) {
		$person=WT_Person::getInstance($row);
		if ($person->canDisplayName()) {
			// filter ASSOciate
			if ($event_jd) {
				// no self-ASSOciate
				if ($pid && $person->getXref()==$pid) {
					continue;
				}
				// filter by birth date
				$person_birth_jd=$person->getEstimatedBirthDate()->minJD();
				if ($person_birth_jd) {
					// born after event or not a contemporary
					if ($event_jd && $person_birth_jd>$event_jd) {
						continue;
					} elseif ($indi_birth_jd && abs($indi_birth_jd-$person_birth_jd)>$MAX_ALIVE_AGE*365) {
						continue;
					} elseif ($husb_birth_jd && $wife_birth_jd && abs($husb_birth_jd-$person_birth_jd)>$MAX_ALIVE_AGE*365 && abs($wife_birth_jd-$person_birth_jd)>$MAX_ALIVE_AGE*365) {
						continue;
					} elseif ($husb_birth_jd && abs($husb_birth_jd-$person_birth_jd)>$MAX_ALIVE_AGE*365) {
						continue;
					} elseif ($wife_birth_jd && abs($wife_birth_jd-$person_birth_jd)>$MAX_ALIVE_AGE*365) {
						continue;
					}
				}
				// filter by death date
				$person_death_jd=$person->getEstimatedDeathDate()->MaxJD();
				if ($person_death_jd) {
					// dead before event or not a contemporary
					if ($event_jd && $person_death_jd<$event_jd) {
						continue;
					} elseif ($indi_birth_jd && $person_death_jd<$indi_birth_jd) {
						continue;
					} elseif ($husb_birth_jd && $wife_birth_jd && $person_death_jd<$husb_birth_jd && $person_death_jd<$wife_birth_jd) {
						continue;
					} elseif ($husb_birth_jd && $person_death_jd<$husb_birth_jd) {
						continue;
					} elseif ($wife_birth_jd && $person_death_jd<$wife_birth_jd) {
						continue;
					}
				}
			}
			// display
			$label=str_replace(array('@N.N.', '@P.N.'), array($UNKNOWN_NN, $UNKNOWN_PN), $row['n_full']);
			if ($event_jd && $person->getBirthDate()->isOK()) {
				$label.=", <span class=\"age\">(".WT_I18N::translate('Age')." ".$person->getBirthDate()->MinDate()->getAge(false, $event_jd).")</span>";
			} else {
				$label.=', <i>'.$person->getLifeSpan().'</i>';
			}
			$data[]=array('value'=>$row['xref'], 'label'=>$label);
		}
	}
	echo json_encode($data);
	exit;

case 'CEME': // Cemetery fields, that contain the search term
	$data=array();
	// Fetch all data, regardless of privacy
	$rows=
		WT_DB::prepare(
			"SELECT SQL_CACHE 'INDI' AS type, i_id AS xref, i_file AS ged_id, i_gedcom AS gedrec".
			" FROM `##individuals`".
			" WHERE i_gedcom LIKE '%\n2 CEME %' AND i_file=?".
			" ORDER BY SUBSTRING_INDEX(i_gedcom, '\n2 CEME ', -1)"
		)
		->execute(array(WT_GED_ID))
		->fetchAll(PDO::FETCH_ASSOC);
	// Filter for privacy
	foreach ($rows as $row) {
		$person=WT_Person::getInstance($row);
		if (preg_match('/\n2 CEME (.*'.preg_quote($term, '/').'.*)/i', $person->getGedcomRecord(), $match)) {
			$data[]=$match[1];
		}
	}	
	echo json_encode($data);
	exit;

case 'FAM': // Families, whose name contains the search terms
	$data=array();
	// Fetch all data, regardless of privacy
	$rows=get_FAM_rows($term);
	// Filter for privacy
	foreach ($rows as $row) {
		$family=WT_Family::getInstance($row);
		if ($family->canDisplayName()) {
			$marriage_year=$family->getMarriageYear();
			if ($marriage_year) {
				$data[]=array('value'=>$family->getXref(), 'label'=>$family->getFullName().', <i>'.$marriage_year.'</i>');
			} else {
				$data[]=array('value'=>$family->getXref(), 'label'=>$family->getFullName());
			}
		}
	}	
	echo json_encode($data);
	exit;

case 'GIVN': // Given names, that start with the search term
	// Do not filter by privacy.  Given names on their own do not identify individuals.
	echo json_encode(
		WT_DB::prepare(
			"SELECT SQL_CACHE DISTINCT n_givn".
			" FROM `##name`".
			" WHERE n_givn LIKE CONCAT(?, '%') AND n_file=?".
			" ORDER BY n_givn"
		)
		->execute(array($term, WT_GED_ID))
		->fetchOneColumn()
	);
	exit;

case 'INDI': // Individuals, whose name contains the search terms
	$data=array();
	// Fetch all data, regardless of privacy
	$rows=
		WT_DB::prepare(
			"SELECT 'INDI' AS type, i_id AS xref, i_file AS ged_id, i_gedcom AS gedrec, n_full".
			" FROM `##individuals`".
			" JOIN `##name` ON (i_id=n_id AND i_file=n_file)".
			" WHERE (n_full LIKE CONCAT('%', REPLACE(?, ' ', '%'), '%') OR n_surn LIKE CONCAT('%', REPLACE(?, ' ', '%'), '%')) AND i_file=? ORDER BY n_full"
		)
		->execute(array($term, $term, WT_GED_ID))
		->fetchAll(PDO::FETCH_ASSOC);
	// Filter for privacy
	foreach ($rows as $row) {
		$person=WT_Person::getInstance($row);
		if ($person->canDisplayName()) {
			$data[]=array('value'=>$row['xref'], 'label'=>str_replace(array('@N.N.', '@P.N.'), array($UNKNOWN_NN, $UNKNOWN_PN), $row['n_full']).', <i>'.$person->getLifeSpan().'</i>');
		}
	}	
	echo json_encode($data);
	exit;

case 'NOTE': // Notes which contain the search terms
	$data=array();
	// Fetch all data, regardless of privacy
	$rows=get_NOTE_rows($term);
	// Filter for privacy
	foreach ($rows as $row) {
		$note=WT_Note::getInstance($row);
		if ($note->canDisplayName()) {
			$data[]=array('value'=>$row['xref'], 'label'=>$note->getFullName());
		}
	}	
	echo json_encode($data);
	exit;

case 'OBJE':
	$data=array();
	// Fetch all data, regardless of privacy
	$rows=get_OBJE_rows($term);
	// Filter for privacy
	foreach ($rows as $row) {
		$media=WT_Media::getInstance($row);
		if ($media->canDisplayName()) {
			$data[]=array('value'=>$row['xref'], 'label'=>'<img src="'.$media->getThumbnail().'" width="25"> '.$media->getFullName());
		}
	}	
	echo json_encode($data);
	exit;

case 'PLAC': // Place names (with hierarchy), that include the search term
	// Do not filter by privacy.  Place names on their own do not identify individuals.
	$data=array();
	foreach (WT_Place::findPlaces($term, WT_GED_ID) as $place) {
		$data[]=$place->getGedcomName();
	}
	if (!$data && get_gedcom_setting(WT_GED_ID, 'USE_GEONAMES')) {
		// No place found?  Use an external gazetteer
		$url=
			"http://ws.geonames.org/searchJSON".
			"?name_startsWith=".urlencode($term).
			"&lang=".WT_LOCALE.
			"&fcode=CMTY&fcode=ADM4&fcode=PPL&fcode=PPLA&fcode=PPLC".
			"&style=full";
		// try to use curl when file_get_contents not allowed
		if (ini_get('allow_url_fopen')) {
			$json = file_get_contents($url);
		} elseif (function_exists('curl_init')) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$json = curl_exec($ch);
			curl_close($ch);
		} else {
			return $data;
		}
		$places = json_decode($json, true);
		if ($places["geonames"]) {
			foreach ($places["geonames"] as $k => $place) {
				$data[] = $place["name"].", ".
									$place["adminName2"].", ".
									$place["adminName1"].", ".
									$place["countryName"];
			}
		}
	}
	echo json_encode($data);
	exit;
	
case 'PLAC2': // Place names (without hierarchy), that include the search term
	// Do not filter by privacy.  Place names on their own do not identify individuals.
	echo json_encode(
		WT_DB::prepare(
			"SELECT SQL_CACHE p_place".
			" FROM `##places`".
			" WHERE p_place LIKE CONCAT('%', ?, '%') AND p_file=?".
			" ORDER BY p_place"
		)
		->execute(array($term, WT_GED_ID))
		->fetchOneColumn()
	);
	exit;

case 'REPO': // Repositories, that include the search terms
	$data=array();
	// Fetch all data, regardless of privacy
	$rows=get_REPO_rows($term);
	// Filter for privacy
	foreach ($rows as $row) {
		$repository=WT_Repository::getInstance($row);
		if ($repository->canDisplayName()) {
			$data[]=array('value'=>$row['xref'], 'label'=>$row['n_full']);
		}
	}	
	echo json_encode($data);
	exit;

case 'REPO_NAME': // Repository names, that include the search terms
	$data=array();
	// Fetch all data, regardless of privacy
	$rows=get_REPO_rows($term);
	// Filter for privacy
	foreach ($rows as $row) {
		$repository=WT_Repository::getInstance($row);
		if ($repository->canDisplayName()) {
			$data[]=$row['n_full'];
		}
	}	
	echo json_encode($data);
	exit;

case 'SOUR': // Sources, that include the search terms
	$data=array();
	// Fetch all data, regardless of privacy
	$rows=get_SOUR_rows($term);
	// Filter for privacy
	foreach ($rows as $row) {
		$source=WT_Source::getInstance($row);
		if ($source->canDisplayName()) {
			$data[]=array('value'=>$row['xref'], 'label'=>$row['n_full']);
		}
	}	
	echo json_encode($data);
	exit;

case 'SOUR_PAGE': // Citation details, for a given source, that contain the search term
	$data=array();
	$sid=safe_GET_xref('sid');
	// Fetch all data, regardless of privacy
	$rows=
		WT_DB::prepare(
			"SELECT SQL_CACHE 'INDI' AS type, i_id AS xref, i_file AS ged_id, i_gedcom AS gedrec".
			" FROM `##individuals`".
			" WHERE i_gedcom LIKE CONCAT('%\n_ SOUR @', ?, '@%', REPLACE(?, ' ', '%'), '%') AND i_file=?"
		)
		->execute(array($sid, $term, WT_GED_ID))
		->fetchAll(PDO::FETCH_ASSOC);
	// Filter for privacy
	foreach ($rows as $row) {
		$person=WT_Person::getInstance($row);
		if (preg_match('/\n1 SOUR @'.$sid.'@(?:\n[2-9].*)*\n2 PAGE (.*'.str_replace(' ', '.+', preg_quote($term, '/')).'.*)/i', $person->getGedcomRecord(), $match)) {
			$data[]=$match[1];
		}
		if (preg_match('/\n2 SOUR @'.$sid.'@(?:\n[3-9].*)*\n3 PAGE (.*'.str_replace(' ', '.+', preg_quote($term, '/')).'.*)/i', $person->getGedcomRecord(), $match)) {
			$data[]=$match[1];
		}
	}
	// Fetch all data, regardless of privacy
	$rows=
		WT_DB::prepare(
			"SELECT SQL_CACHE 'FAM' AS type, f_id AS xref, f_file AS ged_id, f_gedcom AS gedrec".
			" FROM `##families`".
			" WHERE f_gedcom LIKE CONCAT('%\n_ SOUR @', ?, '@%', REPLACE(?, ' ', '%'), '%') AND f_file=?"
		)
		->execute(array($sid, $term, WT_GED_ID))
		->fetchAll(PDO::FETCH_ASSOC);
	// Filter for privacy
	foreach ($rows as $row) {
		$family=WT_Family::getInstance($row);
		if (preg_match('/\n1 SOUR @'.$sid.'@(?:\n[2-9].*)*\n2 PAGE (.*'.str_replace(' ', '.+', preg_quote($term, '/')).'.*)/i', $family->getGedcomRecord(), $match)) {
			$data[]=$match[1];
		}
		if (preg_match('/\n2 SOUR @'.$sid.'@(?:\n[3-9].*)*\n3 PAGE (.*'.str_replace(' ', '.+', preg_quote($term, '/')).'.*)/i', $family->getGedcomRecord(), $match)) {
			$data[]=$match[1];
		}
	}
	// array_unique() converts the keys from integer to string, which breaks
	// the JSON encoding - so need to call array_values() to convert them
	// back into integers.
	$data=array_values(array_unique($data));
	echo json_encode($data);
	exit;

case 'SOUR_TITL': // Source titles, that include the search terms
	$data=array();
	// Fetch all data, regardless of privacy
	$rows=
		WT_DB::prepare(
			"SELECT 'SOUR' AS type, s_id AS xref, s_file AS ged_id, s_gedcom AS gedrec, n_full".
			" FROM `##sources`".
			" JOIN `##name` ON (s_id=n_id AND s_file=n_file)".
			" WHERE n_full LIKE CONCAT('%', REPLACE(?, ' ', '%'), '%') AND s_file=? ORDER BY n_full"
		)
		->execute(array($term, WT_GED_ID))
		->fetchAll(PDO::FETCH_ASSOC);
	// Filter for privacy
	foreach ($rows as $row) {
		$source=WT_Source::getInstance($row);
		if ($source->canDisplayName()) {
			$data[]=$row['n_full'];
		}
	}	
	echo json_encode($data);
	exit;

case 'SURN': // Surnames, that start with the search term
	// Do not filter by privacy.  Surnames on their own do not identify individuals.
	echo json_encode(
		WT_DB::prepare(
			"SELECT SQL_CACHE DISTINCT n_surname".
			" FROM `##name`".
			" WHERE n_surname LIKE CONCAT(?, '%') AND n_file=?".
			" ORDER BY n_surname"
		)
		->execute(array($term, WT_GED_ID))
		->fetchOneColumn()
	);
	exit;

case 'IFSRO':
	$data=array();
	// Fetch all data, regardless of privacy
	$rows=get_INDI_rows($term);
	// Filter for privacy
	foreach ($rows as $row) {
		$person=WT_Person::getInstance($row);
		if ($person->canDisplayName()) {
			$data[]=array('value'=>$row['xref'], 'label'=>str_replace(array('@N.N.', '@P.N.'), array($UNKNOWN_NN, $UNKNOWN_PN), $row['n_full']).', <i>'.$person->getLifeSpan().'</i>');
		}
	}	
	// Fetch all data, regardless of privacy
	$rows=get_SOUR_rows($term);
	// Filter for privacy
	foreach ($rows as $row) {
		$source=WT_Source::getInstance($row);
		if ($source->canDisplayName()) {
			$data[]=array('value'=>$row['xref'], 'label'=>$row['n_full']);
		}
	}	
	// Fetch all data, regardless of privacy
	$rows=get_REPO_rows($term);
	// Filter for privacy
	foreach ($rows as $row) {
		$repository=WT_Repository::getInstance($row);
		if ($repository->canDisplayName()) {
			$data[]=array('value'=>$row['xref'], 'label'=>$row['n_full']);
		}
	}	
	// Fetch all data, regardless of privacy
	$rows=get_OBJE_rows($term);
	// Filter for privacy
	foreach ($rows as $row) {
		$media=WT_Media::getInstance($row);
		if ($media->canDisplayName()) {
			$data[]=array('value'=>$row['xref'], 'label'=>'<img src="'.$media->getThumbnail().'" width="25"> '.$media->getFullName());
		}
	}	
	// Fetch all data, regardless of privacy
	$rows=get_FAM_rows($term);
	// Filter for privacy
	foreach ($rows as $row) {
		$family=WT_Family::getInstance($row);
		if ($family->canDisplayName()) {
			$marriage_year=$family->getMarriageYear();
			if ($marriage_year) {
				$data[]=array('value'=>$family->getXref(), 'label'=>$family->getFullName().', <i>'.$marriage_year.'</i>');
			} else {
				$data[]=array('value'=>$family->getXref(), 'label'=>$family->getFullName());
			}
		}
	}	
	// Fetch all data, regardless of privacy
	$rows=get_NOTE_rows($term);
	// Filter for privacy
	foreach ($rows as $row) {
		$note=WT_Note::getInstance($row);
		if ($note->canDisplayName()) {
			$data[]=array('value'=>$row['xref'], 'label'=>$note->getFullName());
		}
	}	
	echo json_encode($data);
	exit;
}

function get_FAM_rows($term) {
	return
	$rows=
		WT_DB::prepare(
			"SELECT DISTINCT 'FAM' AS type, f_id AS xref, f_file AS ged_id, f_gedcom AS gedrec".
			" FROM `##families`".
			" JOIN `##name` AS husb_name ON (f_husb=husb_name.n_id AND f_file=husb_name.n_file)".
			" JOIN `##name` AS wife_name ON (f_wife=wife_name.n_id AND f_file=wife_name.n_file)".
			" WHERE CONCAT(husb_name.n_full, ' ', wife_name.n_full) LIKE CONCAT('%', REPLACE(?, ' ', '%'), '%') AND f_file=?".
			" AND husb_name.n_type<>'_MARNM' AND wife_name.n_type<>'_MARNM'".
			" ORDER BY husb_name.n_sort, wife_name.n_sort"
		)
		->execute(array($term, WT_GED_ID))
		->fetchAll(PDO::FETCH_ASSOC);
}

function get_INDI_rows($term) {
	return
		WT_DB::prepare(
			"SELECT 'INDI' AS type, i_id AS xref, i_file AS ged_id, i_gedcom AS gedrec, n_full".
			" FROM `##individuals`".
			" JOIN `##name` ON (i_id=n_id AND i_file=n_file)".
			" WHERE n_full LIKE CONCAT('%', REPLACE(?, ' ', '%'), '%') AND i_file=? ORDER BY n_full"
		)
		->execute(array($term, WT_GED_ID))
		->fetchAll(PDO::FETCH_ASSOC);
}

function get_NOTE_rows($term) {
	return
		WT_DB::prepare(
			"SELECT o_type AS type, o_id AS xref, o_file AS ged_id, o_gedcom AS gedrec, n_full".
			" FROM `##other`".
			" JOIN `##name` ON (o_id=n_id AND o_file=n_file)".
			" WHERE o_gedcom LIKE CONCAT('%', REPLACE(?, ' ', '%'), '%') AND o_file=? AND o_type='NOTE'".
			" ORDER BY n_full"
		)
		->execute(array($term, WT_GED_ID))
		->fetchAll(PDO::FETCH_ASSOC);
}

function get_OBJE_rows($term) {
	return
		WT_DB::prepare(
			"SELECT 'OBJE' AS type, m_media AS xref, m_gedfile AS ged_id, m_gedrec AS gedrec, m_titl, m_file".
			" FROM `##media`".
			" WHERE (m_titl LIKE CONCAT('%', REPLACE(?, ' ', '%'), '%') OR m_media LIKE CONCAT('%', REPLACE(?, ' ', '%'), '%')) AND m_gedfile=?"
		)
		->execute(array($term, $term, WT_GED_ID))
		->fetchAll(PDO::FETCH_ASSOC);
}

function get_REPO_rows($term) {
	return
		WT_DB::prepare(
			"SELECT o_type AS type, o_id AS xref, o_file AS ged_id, o_gedcom AS gedrec, n_full".
			" FROM `##other`".
			" JOIN `##name` ON (o_id=n_id AND o_file=n_file)".
			" WHERE n_full LIKE CONCAT('%', REPLACE(?, ' ', '%'), '%') AND o_file=? AND o_type='REPO'".
			" ORDER BY n_full"
		)
		->execute(array($term, WT_GED_ID))
		->fetchAll(PDO::FETCH_ASSOC);
}

function get_SOUR_rows($term) {
	return
		WT_DB::prepare(
			"SELECT 'SOUR' AS type, s_id AS xref, s_file AS ged_id, s_gedcom AS gedrec, n_full".
			" FROM `##sources`".
			" JOIN `##name` ON (s_id=n_id AND s_file=n_file)".
			" WHERE n_full LIKE CONCAT('%', REPLACE(?, ' ', '%'), '%') AND s_file=? ORDER BY n_full"
		)
		->execute(array($term, WT_GED_ID))
		->fetchAll(PDO::FETCH_ASSOC);
}
