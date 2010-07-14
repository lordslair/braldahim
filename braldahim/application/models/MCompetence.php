<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id$
 * $Author$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
class MCompetence extends Zend_Db_Table {
	protected $_name = 'mcompetence';
	protected $_primary = 'id_mcompetence';

	public function findReperagecase() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('mcompetence', '*')
		->where('nom_systeme_mcompetence = ?', "reperagecase");
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
}