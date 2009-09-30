<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
class Bral_Util_Crevasse {

	public static function calculCrevasse(&$hobbit) {
		Zend_Loader::loadClass("Crevasse");

		$estCrevasseEvenement = false;

		$crevasseTable = new Crevasse();
		$nbCrevasses = $crevasseTable->countByCase($hobbit->x_hobbit, $hobbit->y_hobbit, $hobbit->z_hobbit);

		if ($nbCrevasses > 0) {
			$estCrevasseEvenement = true;
			$hobbit->z_hobbit = $hobbit->z_hobbit - 1;
			$hobbit->pv_restant_hobbit = $hobbit->pv_restant_hobbit - floor($hobbit->pv_restant_hobbit / 2);
			if ($hobbit->pv_restant_hobbit < 1) {
				$hobbit->pv_restant_hobbit = 1;
			}
		}
		
		return $estCrevasseEvenement;
	}
}