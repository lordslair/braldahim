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
class Bral_Boutique_Acheterminerais extends Bral_Boutique_Boutique {
	
	function getNomInterne() {
		return "box_action";
	}
	
	function getTitreAction() {
		return "Acheter du minerai";
	}
	
	function prepareCommun() {
		Zend_Loader::loadClass('Bral_Util_BoutiqueMinerais');
		Zend_Loader::loadClass('Region');
		Zend_Loader::loadClass('StockMinerai');
		Zend_Loader::loadClass('BoutiqueMinerai');
		
		$this->view->acheterPossible = true;
		$this->view->minerais = Bral_Util_BoutiqueMinerais::construireTabPrix(true, $this->idRegion);
		$this->view->poidsRestant = $this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit;
	}

	function prepareFormulaire() {
		// rien ici
	}

	function prepareResultat() {
		if ($this->view->assezDePa !== true) {
			throw new Zend_Exception(get_class($this)."::pas assez de PA");
		}
		
		for ($i = 1; $i <= count($this->view->minerais); $i++) {
			if (((int)$this->request->get("valeur_".$i).""!=$this->request->get("valeur_".$i)."")) {
				throw new Zend_Exception("Bral_Boutique_Acheterminerais :: Nombre invalide (".$i.") : ".$this->request->get("valeur_".$i));
			}
		}
		
		$this->transfert();
	}
	
	private function transfert() {
		Zend_Loader::loadClass("LabanMinerai");
		$this->view->coutCastars = 0;
		
		$this->view->elementsAchetes = "";
		$this->view->manquePlace = false;
		$this->view->manqueCastars = false;
		
		foreach($this->view->minerais as $m) {
			$quantite = (int)$this->request->get($m["id_champ"]);
			
			$idTypeMinerai = $m["id_type_minerai"];
			$nomTypeMinerai = $m["type"];
			$idStock = $m["idStock"];
			
			$prixUnitaire = $m["prixUnitaireVente"];
			
			if ($quantite > $m["nbStockRestant"]) {
				$quantite = $m["nbStockRestant"];
			}
			
			$this->transfertElement($quantite, $prixUnitaire, $idTypeMinerai, $nomTypeMinerai, $idStock);
		}
		$this->view->user->castars_hobbit = $this->view->user->castars_hobbit - $this->view->coutCastars;
		
		if ($this->view->elementsAchetes != "") {
			$this->view->elementsAchetes = mb_substr($this->view->elementsAchetes, 0, -2);
		} else { // rien n'a pu etre achete
			$this->view->nb_pa = 0;
		}
	}
	
	private function transfertElement($quantite, $prixUnitaire, $idTypeMinerai, $nomTypeMinerai, $idStock) {
		
		if ($this->view->poidsRestant < 0) $this->view->poidsRestant = 0;
		$nbPossible = floor($this->view->poidsRestant / Bral_Util_Poids::POIDS_MINERAI);
		
		if ($quantite > $nbPossible) {
			$quantite = $nbPossible;
			$this->view->manquePlace = true;
		}
		
		$prixTotal = $prixUnitaire * $quantite;
		$castarsRestants = $this->view->user->castars_hobbit - $this->view->coutCastars;
		if ($prixTotal > $castarsRestants) {
			$quantite = floor($castarsRestants / $prixUnitaire);
			$prixTotal = floor($prixUnitaire * $quantite);
			$this->view->manqueCastars = true;
		}
		
		if ($quantite >= 1) {
			$this->view->coutCastars += $prixTotal;
			$this->view->poidsRestant = floor($this->view->poidsRestant - ($quantite * Bral_Util_Poids::POIDS_MINERAI));
			$this->view->poidsRestant = $this->view->poidsRestant + ($prixTotal * Bral_Util_Poids::POIDS_CASTARS); // on enleve le poids des castars enleves
			
			$this->transfertEnBase($quantite, $idTypeMinerai, $prixUnitaire, $idStock);
			
			if ($quantite > 1) {$s = 's';} else {$s = '';};
			$this->view->elementsAchetes .= "<br>".$quantite;
			$this->view->elementsAchetes .= " minerai".$s." ".$nomTypeMinerai;
			if ($prixTotal > 1) {$s = 's';} else {$s = '';};
			$this->view->elementsAchetes .= " pour ".$prixTotal." castar".$s.", ";
		}
	}
	
	private function transfertEnBase($quantite, $idTypeMinerai, $prixUnitaire, $idStock) {
		$data = array(
			"quantite_brut_laban_minerai" => $quantite,
			"id_fk_type_laban_minerai" => $idTypeMinerai,
			"id_fk_hobbit_laban_minerai" => $this->view->user->id_hobbit,
		);
		
		$labanMineraiTable = new LabanMinerai();
		$labanMineraiTable->insertOrUpdate($data);
		
		$data = array(
			"date_achat_boutique_minerai" => date("Y-m-d H:i:s"),
			"id_fk_type_boutique_minerai" => $idTypeMinerai,
			"id_fk_lieu_boutique_minerai" => $this->view->idBoutique,
			"id_fk_hobbit_boutique_minerai" => $this->view->user->id_hobbit,
			"quantite_brut_boutique_minerai" => $quantite,
			"prix_unitaire_boutique_minerai" => $prixUnitaire,
			"id_fk_region_boutique_minerai" => $this->idRegion,
			"action_boutique_minerai" => "vente",
		);
		$boutiqueMineraiTable = new BoutiqueMinerai();
		$boutiqueMineraiTable->insert($data);
		
		$data = array(
			"id_stock_minerai" => $idStock,
			"nb_brut_restant_stock_minerai" => -$quantite,
		);
		$stockMineraiTable = new StockMinerai();
		$stockMineraiTable->updateStock($data);
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_laban", "box_evenements", "box_bminerais");
	}
}