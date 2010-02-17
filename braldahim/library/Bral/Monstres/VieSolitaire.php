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
class Bral_Monstres_VieSolitaire {

	public function __construct($view, $villes) {
		$this->config = Zend_Registry::get('config');
		$this->view = $view;
		$this->villes = $villes;
	}

	public function action() {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - vieSolitairesAction - enter");
		try {
			// recuperation des monstres a jouer
			$monstreTable = new Monstre();
			$monstres = $monstreTable->findMonstresAJouerSansGroupe(true, $this->config->game->monstre->nombre_groupe_a_jouer, false);
			$this->traiteSolitaires($monstres, true);
			$monstres = $monstreTable->findMonstresAJouerSansGroupe(false, $this->config->game->monstre->nombre_groupe_a_jouer, false);
			$this->traiteSolitaires($monstres, false);
		} catch (Exception $e) {
			Bral_Util_Log::erreur()->err(get_class($this)." - vieSolitairesAction - Erreur:".$e->getTraceAsString());
			throw new Zend_Exception($e);
		}
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - vieSolitairesAction - exit");
	}

	private function traiteSolitaires($solitaires, $aleatoire1D2) {
		foreach($solitaires as $s) {
			if ($aleatoire1D2 == false || ($aleatoire1D2 == true && Bral_Util_De::get_1d2() == 1)) {
				$this->vieSolitaireAction($s);
			}
		}
	}

	private function vieSolitaireAction(&$monstre) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - vieSolitaireAction - enter (id=".$monstre["id_monstre"].")");

		$estFuite = $this->calculFuiteSolitaire(&$monstre);
		if ($estFuite) {
			$this->deplacementSolitaire(&$monstre, true);
		} else {
			$cible = self::reperageCible($monstre);
			if ($cible != null) { // si une cible est trouvee, on attaque
				$this->attaqueSolitaire($monstre, $cible);
			} else {
				$this->deplacementSolitaire($monstre);
			}
		}
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - vieSolitaireAction - exit");
	}

	private function reperageCible(&$monstre) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - reperageCible - enter");
		$cible = null;
			
		// on regarde s'il y a une cible en cours
		if ($monstre["id_fk_hobbit_cible_monstre"] != null) {
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - cible en cours");
			$hobbitTable = new Hobbit();
			$vue = $monstre["vue_monstre"] + $monstre["vue_malus_monstre"];
			if ($vue < 0) {
				$vue = 0;
			}
				
			$cible = $hobbitTable->findHobbitAvecRayon($monstre["x_monstre"], $monstre["y_monstre"], $vue, $monstre["id_fk_hobbit_cible_monstre"], false);
			if (count($cible) > 0) {
				$cible = $cible[0];
				$monstre["x_direction_monstre"] = $cible["x_hobbit"];
				$monstre["y_direction_monstre"] = $cible["y_hobbit"];
				Bral_Util_Log::viemonstres()->debug(get_class($this)." - cible trouvee:".$cible["id_hobbit"]. " x=".$monstre["x_direction_monstre"]. " y=".$monstre["y_direction_monstre"]);
			} else {
				$monstre["id_fk_hobbit_cible_monstre"] = null;
				Bral_Util_Log::viemonstres()->debug(get_class($this)." - cible non trouvee x=".$monstre["x_direction_monstre"]. " y=".$monstre["y_direction_monstre"]);
			}
		} else { // pas de cible en cours
			$cible = null;
		}

		// si la cible n'est pas dans la vue, on en recherche une autre ou l'on se deplace
		if ($cible == null) {
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - pas de cible en cours");
			$monstre["id_fk_hobbit_cible_monstre"] = null;
			$cible = $this->rechercheNouvelleCible($monstre);
		}
			
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - reperageCible - exit");
		return $cible;
	}

	private function rechercheNouvelleCible(&$monstre) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - rechercheNouvelleCible - enter");
		$hobbitTable = new Hobbit();
		$vue = $monstre["vue_monstre"] + $monstre["vue_malus_monstre"];
		if ($vue < 0) {
			$vue = 0;
		}
			
		$cibles = $hobbitTable->findLesPlusProches($monstre["x_monstre"], $monstre["y_monstre"], $monstre["z_monstre"], $vue, 1, $monstre["id_fk_type_monstre"], false);
		if ($cibles != null) {
			$cible = $cibles[0];
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - nouvelle cible trouvee:".$cible["id_hobbit"]);
			$monstre["id_fk_hobbit_cible_monstre"] = $cible["id_hobbit"];
			$monstre["x_direction_monstre"] = $cible["x_hobbit"];
			$monstre["y_direction_monstre"] = $cible["y_hobbit"];
		} else {
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - aucune cible trouvee x=".$monstre["x_monstre"]." y=".$monstre["y_monstre"]." vue=".$monstre["vue_monstre"]);
			$cible = null;
		}
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - rechercheNouvelleCible - exit");
		return $cible;
	}

	/**
	 * Attaque de la cible.
	 */
	protected function attaqueSolitaire(&$monstre, &$cible) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - attaqueSolitaire - enter");

		$vieMonstre = Bral_Monstres_VieMonstre::getInstance();

		if ($cible != null) {
			$vieMonstre->setMonstre($monstre);
			$koCible = false;
			// on regarde si la cible demandée est bien la cible du monstre
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - attaqueSolitaire - cible du monstre (".$monstre["id_monstre"].") : ".$cible["id_hobbit"]);
			$koCible = $vieMonstre->attaqueCible($cible, $this->view);

			if ($koCible == null) { // null => cible hors vue
				$vieMonstre->deplacementMonstre($monstre["x_direction_monstre"], $monstre["y_direction_monstre"]);
			} else if ($koCible === true) {
				$monstre["id_fk_hobbit_cible_monstre"] = null;
				$cible = $this->rechercheNouvelleCible($monstre);
				Bral_Util_Log::viemonstres()->trace(get_class($this)." - attaqueSolitaire - nouvelle cible du monstre (".$monstre["id_monstre"].") : ".$cible["id_hobbit"]);
				$vieMonstre->attaqueCible($cible, $this->view); // seconde attaque, utilise pour souffle de feu par exemple, si la cible principale est tuée par le souffle et qu'il reste 4 PA pour l'attaque
			}
		} else {
			$vieMonstre->deplacementMonstre($monstre["x_direction_monstre"], $monstre["y_direction_monstre"]);
		}
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - attaqueSolitaire - exit");
	}

	/**
	 * Deplacement du solitaire.
	 */
	protected function deplacementSolitaire(&$monstre, $fuite = false) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - deplacementSolitaire - enter - (idm:".$monstre["id_monstre"].")");

		if ($fuite ||
		(($monstre["x_monstre"] == $monstre["x_direction_monstre"]) && //
		($monstre["y_monstre"] == $monstre["y_direction_monstre"]))) {

			if ($fuite) {
				$ajoutFuite = 10;
			} else {
				$ajoutFuite = 0;
			}

			$dx = Bral_Util_De::get_1d12() + $ajoutFuite;
			$dy = Bral_Util_De::get_1d12() + $ajoutFuite;

			$plusMoinsX = Bral_Util_De::get_1d2();
			$plusMoinsY = Bral_Util_De::get_1d2();

			if ($plusMoinsX == 1) {
				$monstre["x_direction_monstre"] = $monstre["x_direction_monstre"] - $dx;
			} else {
				$monstre["x_direction_monstre"] = $monstre["x_direction_monstre"] + $dx;
			}

			if ($plusMoinsY == 1) {
				$monstre["y_direction_monstre"] = $monstre["y_direction_monstre"] - $dy;
			} else {
				$monstre["y_direction_monstre"] = $monstre["y_direction_monstre"] + $dy;
			}

			$tab = Bral_Monstres_VieMonstre::getTabXYRayon($monstre["id_fk_zone_nid_monstre"], $monstre["niveau_monstre"], $monstre["x_direction_monstre"], $monstre["y_direction_monstre"], $monstre["x_min_monstre"], $monstre["x_max_monstre"], $monstre["y_min_monstre"], $monstre["y_max_monstre"], $monstre["id_monstre"]);

			$monstre["x_direction_monstre"] = $tab["x_direction"];
			$monstre["y_direction_monstre"] = $tab["y_direction"];

			Bral_Util_Log::viemonstres()->debug(get_class($this)." monstre (".$monstre["id_monstre"].")- calcul nouvelle valeur direction x=".$monstre["x_direction_monstre"]." y=".$monstre["y_direction_monstre"]." ");
		}

		$vieMonstre = Bral_Monstres_VieMonstre::getInstance();
		$vieMonstre->setMonstre($monstre);
		$vieMonstre->deplacementMonstre($monstre["x_direction_monstre"], $monstre["y_direction_monstre"]);
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - deplacementSolitaire - exit - (idm:".$monstre["id_monstre"].")");
	}

	/*
	 * Recherche competence de fuite.
	 */
	private function calculFuiteSolitaire(&$monstre) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculFuiteSolitaire - enter");
		$vieMonstre = Bral_Monstres_VieMonstre::getInstance();
		$vieMonstre->setMonstre($monstre);
		$estFuite = $vieMonstre->calculFuite($this->view);
		$monstre = $vieMonstre->getMonstre();
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculFuiteSolitaire - exit");
		return $estFuite;
	}
}