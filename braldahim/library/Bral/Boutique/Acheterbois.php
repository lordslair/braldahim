<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id:$
 * $Author:$
 * $LastChangedDate:$
 * $LastChangedRevision:$
 * $LastChangedBy:$
 */
class Bral_Boutique_Acheterbois extends Bral_Boutique_Boutique {
	
	private $potion = null;
	private $idBoutique = null;

	function getNomInterne() {
		return "box_action";
	}
	
	function getTitreAction() {
		return "Acheter des rondins";
	}
	
	function prepareCommun() {
		Zend_Loader::loadClass("Charrette");
		
	}

	function prepareFormulaire() {
		// rien ici
	}

	function prepareResultat() {
		if ($this->view->assezDePa !== true) {
			throw new Zend_Exception(get_class($this)."::pas assez de PA");
		}
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_laban", "box_charrette", "box_evenements");
	}
}