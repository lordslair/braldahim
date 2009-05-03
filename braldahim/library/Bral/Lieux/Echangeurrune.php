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
class Bral_Lieux_Echangeurrune extends Bral_Lieux_Lieu {

	function prepareCommun() {
		$this->view->achatPossibleCastars = false;
		$this->view->tabAAfficher = false;

		Zend_Loader::loadClass("LabanRune");
		$tabLabanRune = null;
		$labanRuneTable = new LabanRune();
		$labanRunes = $labanRuneTable->findByIdHobbit($this->view->user->id_hobbit, "oui");

		foreach($labanRunes as $l) {
			if ($l["niveau_type_rune"] == "d" || $l["niveau_type_rune"] == "c") {
				$tabLabanRune[$l["id_type_rune"]]["id_type_rune"] = $l["id_type_rune"];
				$tabLabanRune[$l["id_type_rune"]]["a_afficher"] = false;
				$tabLabanRune[$l["id_type_rune"]]["cout_castars"] = 1000;
				$tabLabanRune[$l["id_type_rune"]]["niveau_type_rune"] = $l["niveau_type_rune"];
				$tabLabanRune[$l["id_type_rune"]]["nom_type_rune"] = $l["nom_type_rune"];
				$tabLabanRune[$l["id_type_rune"]]["image_type_rune"] = $l["image_type_rune"];

				$tabLabanRune[$l["id_type_rune"]]["runes"][] = array(
						"id_rune_laban_rune" => $l["id_rune_laban_rune"],
						"id_fk_type_rune_laban_rune" => $l["id_fk_type_laban_rune"],
						"nom_type_rune" => $l["nom_type_rune"],
						"image_type_rune" => $l["image_type_rune"],
						"effet_type_rune" => $l["effet_type_rune"],
						"id_rune_laban_rune" => $l["id_rune_laban_rune"],
				);
				if (count($tabLabanRune[$l["id_type_rune"]]["runes"]) >= 3) {
					$tabLabanRune[$l["id_type_rune"]]["a_afficher"] = true;
					$tabLabanRune[$l["id_type_rune"]]["achat_possible"] = false;
					$this->view->tabAAfficher = true;

					if ($l["niveau_type_rune"] == "d") {
						$tabLabanRune[$l["id_type_rune"]]["cout_castars"] = 15;
					} else if ($l["niveau_type_rune"] == "c") {
						$tabLabanRune[$l["id_type_rune"]]["cout_castars"] = 21;
					}

					if ($this->view->user->castars_hobbit >= $tabLabanRune[$l["id_type_rune"]]["cout_castars"]) {
						$tabLabanRune[$l["id_type_rune"]]["achat_possible"] = true;
						$this->view->achatPossibleCastars = true;
					}
				}
			}
		}
		$this->view->nbLabanRune = count($tabLabanRune);
		$this->view->labanRunes = $tabLabanRune;
	}

	function prepareFormulaire() {

	}

	function prepareResultat() {

		// verification qu'il a assez de PA
		if ($this->view->utilisationPaPossible == false) {
			throw new Zend_Exception(get_class($this)." Utilisation impossible : PA:".$this->view->user->pa_hobbit);
		}

		// verification que la valeur recue est bien numerique
		if (((int)$this->request->get("valeur_1").""!=$this->request->get("valeur_1")."")) {
			throw new Zend_Exception(get_class($this)." Valeur invalide : val=".$this->request->get("valeur_1"));
		} else {
			$idTypeRune = (int)$this->request->get("valeur_1");
		}

		if (!array_key_exists($idTypeRune, $this->view->labanRunes)) {
			throw new Zend_Exception(get_class($this)." idTypeRune interdit A=".$idTypeRune);
		}

		if ($this->view->labanRunes[$idTypeRune]["achat_possible"] !== true || $this->view->labanRunes[$idTypeRune]["cout_castars"] > $this->view->user->castars_hobbit) {
			throw new Zend_Exception(get_class($this)." Achat impossible");
		}

		$this->echange($idTypeRune);
	}

	private function echange($idTypeRune) {

		$this->view->cout = $this->view->labanRunes[$idTypeRune]["cout_castars"];
		$this->view->user->castars_hobbit = $this->view->user->castars_hobbit - $this->view->cout;

		$texte = "";
		$labanRuneTable = new LabanRune();
		for ($i = 0; $i < 3; $i++) {
			$where = "id_rune_laban_rune = ".(int)$this->view->labanRunes[$idTypeRune]["runes"][$i]["id_rune_laban_rune"];
	//		$labanRuneTable->delete($where);
			$texte .= " n°".$this->view->labanRunes[$idTypeRune]["runes"][$i]["id_rune_laban_rune"];
			if ($i < 2) {
				$texte .= ",";
			}
		}

		$niveauRune = "c";
		if ($this->view->labanRunes[$idTypeRune]["niveau_type_rune"] == "c") {
			$niveauRune = "b";
		}

		Zend_Loader::loadClass("TypeRune");
		$typeRuneTable = new TypeRune();
		$typeRuneRowset = $typeRuneTable->findByNiveau($niveauRune);

		if (!isset($typeRuneRowset) || count($typeRuneRowset) == 0) {
			throw new Zend_Exception(get_class($this)." niveauRune invalide:".$niveauRune);
		}

		$nbType = count($typeRuneRowset);
		$numeroRune = Bral_Util_De::get_de_specifique(0, $nbType-1);

		$typeRune = $typeRuneRowset[$numeroRune];

		$dateCreation = date("Y-m-d H:i:s");
		$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateCreation, 10);

		Zend_Loader::loadClass("ElementRune");
		$elementRuneTable = new ElementRune();
		$data = array(
			"x_element_rune"  => $this->view->user->x_hobbit,
			"y_element_rune" => $this->view->user->y_hobbit,
			"id_fk_type_element_rune" => $typeRune["id_type_rune"],
			"date_depot_element_rune" => $dateCreation,
			"date_fin_element_rune" => $dateFin,
		);

		$idRune = $elementRuneTable->insert($data);

		$where = "id_element_rune=".$idRune;
		$elementRuneTable->delete($where);

		$labanRuneTable = new LabanRune();
		$data = array (
			"id_rune_laban_rune" => $idRune,
			"id_fk_type_laban_rune" => $typeRune["id_type_rune"],
			"id_fk_hobbit_laban_rune" => $this->view->user->id_hobbit,
			"est_identifiee_rune" => "non",
		);
		$labanRuneTable->insert($data);

		$this->view->texte = $texte;
	}

	function getListBoxRefresh() {
		$tab = array("box_laban");
		return $this->constructListBoxRefresh($tab);
	}

}