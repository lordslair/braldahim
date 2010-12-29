<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class LotMateriel extends Zend_Db_Table {
	protected $_name = 'lot_materiel';
	protected $_primary = array('id_lot_materiel');

	function findByIdLot($idLot) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lot_materiel', '*')
		->from('type_materiel', '*')
		->from('materiel', '*')
		->where('id_lot_materiel = id_materiel')
		->where('id_fk_type_materiel = id_type_materiel')
		->where('id_fk_lot_lot_materiel = ?', intval($idLot));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
