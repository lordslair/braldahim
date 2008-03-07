<?php

class Bral_Competences_Rechercherplante extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass('Bral_Util_Commun');
		$commun = new Bral_Util_Commun();
		$this->view->rayon_max = $this->view->config->game->competence->rechercherplante->rayon_max;
		$this->view->rayon_precis =  $commun->getVueBase($this->view->user->x_hobbit, $this->view->user->y_hobbit) * 2;
	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {
		$go = $this->request->get("valeur_1");

		if ($go != "go") {
			throw new Zend_Exception(get_class($this)." Rechercher Plante. Action invalide");
		}

		$this->calculJets();

		if ($this->view->okJet1 === true) {
			Zend_Loader::loadClass('Plante');
			$planteTable = new Plante();
			$planteRow = $planteTable->findLaPlusProche($this->view->user->x_hobbit, $this->view->user->y_hobbit, $this->view->rayon_max);

			if (!empty($planteRow)) {
				$plante = array('categorie' => $planteRow["categorie_type_plante"],'x_plante' => $planteRow["x_plante"], 'y_plante' => $planteRow["y_plante"]);
				$this->view->trouve = true;
				$this->view->plante = $plante;
				if ($planteRow["distance"] <= $this->view->rayon_precis) {
					$this->view->proche = true;
				} else {
					$this->view->proche = false;
				}
			} else {
				$this->view->trouve= false;
			}
			
			$this->majEvenementsStandard();
		}

		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}

	function getListBoxRefresh() {
		return array("box_profil", "box_evenements");
	}
}