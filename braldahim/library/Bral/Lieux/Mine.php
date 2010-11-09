<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Lieux_Mine extends Bral_Lieux_Lieu {

	function prepareCommun() {
		if ($this->view->user->z_braldun != 0) {
			$tabNiveaux[0] = array('niveauText' => 'Niveau  0');
		}

		for($i = -10; $i >= -13; $i--) {
			if ($this->view->user->z_braldun != $i) {
				$tabNiveaux[$i] = array('niveauText' => 'Niveau '.$i);
			}
		}
		$this->view->niveaux = $tabNiveaux;
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
		$niveau = (int)$this->request->get("valeur_1");

		if (!array_key_exists($niveau, $this->view->niveaux)) {
			throw new Zend_Exception(get_class($this)." niveau invalide:".$niveau);
		}
		$this->view->user->z_braldun = $niveau;
		$this->majBraldun();

		Zend_Loader::loadClass("Bral_Util_Tracemail");
		Bral_Util_Tracemail::traite("Descente du Braldûn ".$this->view->user->prenom_braldun." ".$this->view->user->nom_braldun." (".$this->view->user->id_braldun.") dans une mine en ".$this->view->user->x_braldun. "/".$this->view->user->y_braldun."/".$this->view->user->z_braldun, $this->view, " Descente dans Mine");
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_vue", "box_lieu", "box_blabla"));
	}
}