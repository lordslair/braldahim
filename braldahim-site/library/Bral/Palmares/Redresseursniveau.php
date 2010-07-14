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
class Bral_Palmares_Redresseursniveau extends Bral_Palmares_Box {

	function getTitreOnglet() {
		return "Niveaux";
	}
	
	function getNomInterne() {
		return "box_onglet_redresseursniveau";		
	}
	
	function getNomClasse() {
		return "redresseursniveau";		
	}
	
	function setDisplay($display) {
		$this->view->display = $display;
	}
	
	function render() {
		$this->view->nom_interne = $this->getNomInterne();
		$this->view->nom_systeme = $this->getNomClasse();
		$this->prepare();
		return $this->view->render("palmares/redresseurs_niveau.phtml");
	}
	
	private function prepare() {
		Zend_Loader::loadClass("StatsReputation");
		$mdate = $this->getTabDateFiltre();
		$statsReputationTable = new StatsReputation();
		$rowset = $statsReputationTable->findByNiveau($mdate["dateDebut"], $mdate["dateFin"], "redresseur");
		$this->view->niveaux = $rowset;
	}
}