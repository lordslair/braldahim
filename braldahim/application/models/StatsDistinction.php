<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: StatsDistinction.php 2618 2010-05-08 14:25:37Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2010-05-08 16:25:37 +0200 (Sam, 08 mai 2010) $
 * $LastChangedRevision: 2618 $
 * $LastChangedBy: yvonnickesnault $
 */
class StatsDistinction extends Zend_Db_Table {
	protected $_name = 'stats_distinction';
	protected $_primary = array('id_stats_distinction');

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stats_distinction', 'count(*) as nombre, points_stats_distinction as points')
		->where('niveau_braldun_stats_distinction = '.$data["niveau_braldun_stats_distinction"].' AND id_fk_braldun_stats_distinction = '.$data["id_fk_braldun_stats_distinction"]. ' AND mois_stats_distinction = \''.$data["mois_stats_distinction"].'\'')
		->group(array('points'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (!isset($data["points_stats_distinction"])) {
			$data["points_stats_distinction"] = 0;
		}

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$points = $resultat[0]["points"];
			$dataUpdate['points_stats_distinction'] = $points;
				
			$dataUpdate['points_stats_distinction'] =  $data["points_stats_distinction"];
			if ($dataUpdate['points_stats_distinction'] < 0) {
				$dataUpdate['points_stats_distinction'] = 0;
			}
				
			$where = 'niveau_braldun_stats_distinction = '.$data["niveau_braldun_stats_distinction"].' AND id_fk_braldun_stats_distinction = '.$data["id_fk_braldun_stats_distinction"]. ' AND mois_stats_distinction = \''.$data["mois_stats_distinction"].'\'';
			$this->update($dataUpdate, $where);
		}
	}
}