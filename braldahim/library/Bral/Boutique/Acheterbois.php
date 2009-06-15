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
class Bral_Boutique_Acheterbois extends Bral_Boutique_Boutique {

	function getNomInterne() {
		return "box_action";
	}

	function getTitreAction() {
		return "Acheter du bois";
	}

	function prepareCommun() {
		Zend_Loader::loadClass("Charrette");
		Zend_Loader::loadClass("Bral_Util_BoutiqueBois");
		Zend_Loader::loadClass("StockBois");
		Zend_Loader::loadClass("BoutiqueBois");
		Zend_Loader::loadClass("Laban");

		$idDestinationCourante = $this->request->get("id_destination_courante");

		$selectedLaban = "";
		$selectedCharrette = "";
		if ($idDestinationCourante == "laban") {
			$selectedLaban = "selected";
		} else if ($idDestinationCourante == "charrette") {
			$selectedCharrette = "selected";
		}
		$tabDestinationTransfert[] = array("id_destination" => "laban", "texte" => "votre laban", "selected" => $selectedLaban);

		$charretteTable = new Charrette();
		$charrettes = $charretteTable->findByIdHobbit($this->view->user->id_hobbit);

		$charrette = null;
		if (count($charrettes) == 1) {
			$charrette = $charrettes[0];
			$tabDestinationTransfert[] = array("id_destination" => "charrette", "texte" => "votre charrette", "selected" => $selectedCharrette);
		}
		$this->view->destinationTransfertCourante = $idDestinationCourante;
		$this->view->destinationTransfert = $tabDestinationTransfert;
		$this->view->charrette = $charrette;

		$this->preparePrix();
	}

	function prepareFormulaire() {
		// rien ici
	}

	function prepareResultat() {
		if ($this->view->assezDePa !== true) {
			throw new Zend_Exception(get_class($this)."::pas assez de PA");
		}
		if ($this->view->possedeCharrette !== true) {
			throw new Zend_Exception(get_class($this)."::pas de charrette");
		}
		if ($this->view->acheterPossible !== true) {
			throw new Zend_Exception(get_class($this)."::achat impossible");
		}

		if (((int)$this->request->get("valeur_1").""!=$this->request->get("valeur_1")."")) {
			throw new Zend_Exception("Bral_Boutique_Acheterbois :: Nombre invalide : ".$this->request->get("valeur_1"));
		} else {
			$this->view->quantiteAchetee = (int)$this->request->get("valeur_1");
		}

		if ($this->view->quantiteAchetee > $this->view->nombreMaximum) {
			throw new Zend_Exception("Bral_Boutique_Acheterbois :: Nombre invalide : ".$nombre. " max:".$this->view->nombreMaximum);
		}

		if ($this->view->quantiteAchetee > $this->view->nbStockRestant) {
			throw new Zend_Exception("Bral_Boutique_Acheterbois :: Nombre invalide : ".$nombre. " max (stock):".$this->view->nombreMaximum);
		}

		if ($this->view->quantiteAchetee == 0) {
			throw new Zend_Exception("Bral_Boutique_Acheterbois :: Nombre invalide 0");
		}

		$idDestination = $this->request->get("valeur_2");

		if ($this->request->get("id_destination_courante") != $idDestination) {
			throw new Zend_Exception(get_class($this)." destination invalide 1");
		}

		if ($this->view->charrette == null && $this->request->get("id_destination_courante") == "charrette") {
			throw new Zend_Exception(get_class($this)." destination invalide 2");
		}

		// on regarde si l'on connait la destination
		$flag = false;
		$destination = null;
		foreach($this->view->destinationTransfert as $d) {
			if ($d["id_destination"] == $idDestination) {
				$destination = $d;
				$flag = true;
				break;
			}
		}

		if ($flag == false) {
			throw new Zend_Exception(get_class($this)." destination inconnue=".$destination);
		}

		$this->transfert($idDestination);
		$this->view->destination = $destination;
	}

	private function preparePrix() {

		$tabStockPrix = Bral_Util_BoutiqueBois::construireTabStockPrix($this->idRegion );
		if ($tabStockPrix == null || count($tabStockPrix) != 1) {
			Bral_Util_Log::erreur()->err("Bral_Box_Bbois - Erreur de prix dans la table stock_bois, id_region=".$this->idRegion );
			throw new Zend_Exception(get_class($this)."::Erreur de prix dans la table stock_bois, id_region=".$this->idRegion );
		}

		$this->view->tabStockPrix = $tabStockPrix[0];
		$this->view->nbStockRestant = intval($this->view->tabStockPrix["nb_rondin_restant_stock_bois"]);
		$this->idStock = $this->view->tabStockPrix["id_stock_bois"];

		$this->view->prixUnitaire = floor($this->view->tabStockPrix["prix_unitaire_vente_stock_bois"]);
		$this->view->nombreMaximum = floor($this->view->user->castars_hobbit / $this->view->tabStockPrix["prix_unitaire_vente_stock_bois"]);

		$this->view->placeDisponible = true;
		$this->view->stockPossible = true;

		if ($this->view->nbStockRestant < 1) {
			$this->view->stockPossible = false;
			$this->view->nombreMaximum = 0;
		}

		if ($this->view->nombreMaximum > $this->view->nbStockRestant) {
			$this->view->nombreMaximum = $this->view->nbStockRestant;
		}

		if ($this->view->nombreMaximum < 1 || $this->view->assezDePa == false) {
			$this->view->acheterPossible = false;
		} else {
			$this->view->acheterPossible = true;

			if ($this->view->destinationTransfertCourante != null) {
				$this->view->acheterPossible = true;
				$this->view->possedeCharrette = true;

				if ($this->view->destinationTransfertCourante == "laban") {
					$labanTable = new Laban();
					$labanRowset = $labanTable->findByIdHobbit($this->view->user->id_hobbit);
					if (count($labanRowset) == 1) {
						$laban = $labanRowset[0];
					} else {
						$laban["quantite_rondin_laban"] = 0;
					}
					$this->view->nbMaximumContenant = floor(($this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit) / Bral_Util_Poids::POIDS_RONDIN);
					$this->view->nbRondinsActuels = $laban["quantite_rondin_laban"];
				} else if ($this->view->destinationTransfertCourante == "charrette") {
					$tabPoidsCharrette = Bral_Util_Poids::calculPoidsCharrette($this->view->user->id_hobbit);
					$this->view->nbMaximumContenant = floor($tabPoidsCharrette["place_restante"] / Bral_Util_Poids::POIDS_RONDIN);
					$this->view->nbRondinsActuels = $this->view->charrette["quantite_rondin_charrette"];
				}

				if ($this->view->nombreMaximum > $this->view->nbMaximumContenant) {
					$this->view->nombreMaximum = $this->view->nbMaximumContenant;
					if ($this->view->nombreMaximum > $this->view->nbStockRestant) {
						$this->view->nombreMaximum = $this->view->nbStockRestant;
					}
					if ($this->view->nombreMaximum < 1) {
						$this->view->placeDisponible = false;
						$this->view->acheterPossible = false;
					}
				}
			} else {
				$this->view->acheterPossible = false;
				$this->view->possedeCharrette = false;
			}
		}
	}

	private function transfert($idDestination) {
		$this->view->coutCastars = floor($this->view->quantiteAchetee * $this->view->prixUnitaire);
		$this->view->user->castars_hobbit = $this->view->user->castars_hobbit - $this->view->coutCastars;

		if ($idDestination == "charrette") {
			$charretteTable = new Charrette();
			$data = array(
				'quantite_rondin_charrette' => $this->view->quantiteAchetee,
				'id_fk_hobbit_charrette' => $this->view->user->id_hobbit,
			);
			$charretteTable->updateCharrette($data);
			unset($charretteTable);
		} else { // laban
			$labanTable = new Laban();
			$data = array(
				'quantite_rondin_laban' => $this->view->quantiteAchetee,
				'id_fk_hobbit_laban' => $this->view->user->id_hobbit,
			);
			$labanTable->insertOrUpdate($data);
			unset($labanTable);
		}

		$data = array(
			"date_achat_boutique_bois" => date("Y-m-d H:i:s"),
			"id_fk_lieu_boutique_bois" => $this->view->idBoutique,
			"id_fk_hobbit_boutique_bois" => $this->view->user->id_hobbit,
			"quantite_rondin_boutique_bois" => $this->view->quantiteAchetee,
			"prix_unitaire_boutique_bois" => $this->view->prixUnitaire,
			"id_fk_region_boutique_bois" => $this->idRegion,
			"action_boutique_bois" => "vente",
		);
		$boutiqueBoisTable = new BoutiqueBois();
		$boutiqueBoisTable->insert($data);

		$data = array(
			"id_stock_bois" => $this->idStock,
			"nb_rondin_restant_stock_bois" => -$this->view->quantiteAchetee,
		);
		$stockBoisTable = new StockBois();
		$stockBoisTable->updateStock($data);
		
		if ($idDestination == "charrette") {
			Bral_Util_Poids::calculPoidsCharrette($this->view->user->id_hobbit, true);
		}

	}

	function getListBoxRefresh() {
		if ($this->view->destination["id_destination"] == "charrette") {
			$boxToRefresh = "box_charrette";
		} else {
			$boxToRefresh = "box_laban";
		}
		return $this->constructListBoxRefresh(array($boxToRefresh, "box_bbois"));
	}
}