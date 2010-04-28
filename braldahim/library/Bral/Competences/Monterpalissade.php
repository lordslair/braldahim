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
class Bral_Competences_Monterpalissade extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass('Charrette');
		Zend_Loader::loadClass('Echoppe');
		Zend_Loader::loadClass('Lieu');
		Zend_Loader::loadClass('Monstre');
		Zend_Loader::loadClass('Palissade');
		Zend_Loader::loadClass('Route');
		Zend_Loader::loadClass('Nid');
		Zend_Loader::loadClass('Bral_Util_Quete');

		$this->view->monterPalissadeOk = false;
		$this->view->monterPalissadeCharretteOk = false;

		/*
		 * On verifie qu'il y a au moins 2 rondins
		 */
		$charretteTable = new Charrette();
		$charrette = $charretteTable->findByIdHobbit($this->view->user->id_hobbit);

		if (!isset($charrette)) {
			return;
		}

		$this->view->nRondins = 0;
		foreach ($charrette as $c) {
			$this->view->nRondins = $c["quantite_rondin_charrette"];
			$this->view->monterPalissadeCharretteOk = true;
			break;
		}

		//(niveau du Hobbit/10 + 1)*2
		$niveau = $this->view->user->niveau_hobbit;
		$this->view->nRondinsNecessaires = ((floor($niveau / 10) + 1)) * 2;
		$this->view->nRondinsSuffisants = false;

		if ($this->view->nRondins >= $this->view->nRondinsNecessaires) {
			$this->view->nRondinsSuffisants = true;
		}

		$this->distance = 1;
		$this->view->x_min = $this->view->user->x_hobbit - $this->distance;
		$this->view->x_max = $this->view->user->x_hobbit + $this->distance;
		$this->view->y_min = $this->view->user->y_hobbit - $this->distance;
		$this->view->y_max = $this->view->user->y_hobbit + $this->distance;

		$lieuxTable = new Lieu();
		$lieux = $lieuxTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->user->z_hobbit);
		$echoppeTable = new Echoppe();
		$echoppes = $echoppeTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->user->z_hobbit);
		$monstreTable = new Monstre();
		$monstres = $monstreTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->user->z_hobbit);
		$palissadeTable = new Palissade();
		$palissades = $palissadeTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->user->z_hobbit);
		$hobbitTable = new Hobbit();
		$hobbits = $hobbitTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->user->z_hobbit);

		$routeTable = new Route();
		$routes = $routeTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->user->z_hobbit);

		$nidTable = new Nid();
		$nids = $nidTable->selectVue($this->view->x_min, $this->view->y_min, $this->view->x_max, $this->view->y_max, $this->view->user->z_hobbit);

		$defautChecked = false;

		for ($j = $this->distance; $j >= -$this->distance; $j --) {
			$change_level = true;
			for ($i = -$this->distance; $i <= $this->distance; $i ++) {
				$x = $this->view->user->x_hobbit + $i;
				$y = $this->view->user->y_hobbit + $j;
				$z = $this->view->user->z_hobbit;
					
				$display = $x;
				$display .= " ; ";
				$display .= $y;
					
				if (($j == 0 && $i == 0) == false) { // on n'affiche pas de boutons dans la case du milieu
					$valid = true;
				} else {
					$valid = false;
				}
					
				if ($x < $this->view->config->game->x_min || $x > $this->view->config->game->x_max
				|| $y < $this->view->config->game->y_min || $y > $this->view->config->game->y_max ) { // on n'affiche pas de boutons dans la case du milieu
					$valid = false;
				}
					
				foreach($echoppes as $e) {
					if ($x == $e["x_echoppe"] && $y == $e["y_echoppe"] && $z == $e["z_echoppe"]) {
						$valid = false;
						break;
					}
				}

				foreach($lieux as $l) {
					if ($x == $l["x_lieu"] && $y == $l["y_lieu"] && $z == $l["z_lieu"]) {
						$valid = false;
						break;
					}
				}
					
				foreach($hobbits as $h) {
					if ($x == $h["x_hobbit"] && $y == $h["y_hobbit"] && $z == $h["z_hobbit"]) {
						$valid = false;
						break;
					}
				}

				foreach($monstres as $m) {
					if ($x == $m["x_monstre"] && $y == $m["y_monstre"] && $z == $m["z_monstre"]) {
						$valid = false;
						break;
					}
				}

				foreach($palissades as $p) {
					if ($x == $p["x_palissade"] && $y == $p["y_palissade"] && $z == $p["z_palissade"]) {
						$valid = false;
						break;
					}
				}

				foreach($routes as $r) {
					if ($x == $r["x_route"] && $y == $r["y_route"] && $z == $r["z_route"]) {
						$valid = false;
						break;
					}
				}

				foreach($nids as $n) {
					if ($x == $n["x_route"] && $y == $n["y_nid"] && $z == $n["z_nid"]) {
						$valid = false;
						break;
					}
				}

				if ($valid === true && $defautChecked == false) {
					$default = "checked";
					$defautChecked = true;
					$this->view->monterPalissadeOk = true;
				} else {
					$default = "";
				}

				$tab[] = array ("x_offset" => $i,
				 	"y_offset" => $j,
				 	"default" => $default,
				 	"display" => $display,
				 	"change_level" => $change_level, // nouvelle ligne dans le tableau
					"valid" => $valid
				);

				$tabValidation[$i][$j] = $valid;
					
				if ($change_level) {
					$change_level = false;
				}
			}
		}
		$this->view->tableau = $tab;
		$this->tableauValidation = $tabValidation;
	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {
		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_hobbit);
		}

		if ($this->view->monterPalissadeOk == false) {
			throw new Zend_Exception(get_class($this)." Monter Palissade interdit");
		}

		if ($this->view->nRondinsSuffisants == false) {
			throw new Zend_Exception(get_class($this)." Monter Palissade interdit : rondins insuffisants");
		}

		if ($this->view->monterPalissadeCharretteOk == false) {
			throw new Zend_Exception(get_class($this)." Monter Palissade interdit : pas de charrette");
		}

		// on verifie que l'on peut monter une palissade sur la case
		$x_y = $this->request->get("valeur_1");
		list ($offset_x, $offset_y) = split("h", $x_y);
		if ($offset_x < -$this->distance || $offset_x > $this->distance) {
			throw new Zend_Exception(get_class($this)." MonterPalissade X impossible : ".$offset_x);
		}

		if ($offset_y < -$this->distance || $offset_y > $this->distance) {
			throw new Zend_Exception(get_class($this)." MonterPalissade Y impossible : ".$offset_y);
		}

		if ($this->tableauValidation[$offset_x][$offset_y] !== true) {
			throw new Zend_Exception(get_class($this)." MonterPalissade XY impossible : ".$offset_x.$offset_y);
		}

		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->calculMonterPalissade($this->view->user->x_hobbit + $offset_x, $this->view->user->y_hobbit + $offset_y);
			$this->view->estQueteEvenement = Bral_Util_Quete::etapeConstuire($this->view->user, $this->nom_systeme);
		}

		$this->calculPx();
		$this->calculPoids();
		Bral_Util_Poids::calculPoidsCharrette($this->view->user->id_hobbit, true);
		$this->calculBalanceFaim();
		$this->majHobbit();
	}

	private function calculMonterPalissade($x, $y) {

		$charretteTable = new Charrette();
		$data = array(
			'quantite_rondin_charrette' => -$this->view->nRondinsNecessaires,
			'id_fk_hobbit_charrette' => $this->view->user->id_hobbit,
		);
		$charretteTable->updateCharrette($data);
		unset($charretteTable);

		$date_creation = date("Y-m-d H:00:00");
		$nb_jours = Bral_Util_De::getLanceDe6($this->view->config->base_sagesse + $this->view->user->sagesse_base_hobbit) + $this->view->user->sagesse_bm_hobbit + $this->view->user->sagesse_bbdf_hobbit;
		if ($nb_jours < 2) {
			$nb_jours = 2;
		}
		$date_fin = Bral_Util_ConvertDate::get_date_add_day_to_date($date_creation, $nb_jours);

		$data = array(
			"x_palissade"  => $x,
			"y_palissade" => $y,
			"z_palissade" => $this->view->user->z_hobbit,
			"agilite_palissade" => 0,
			"armure_naturelle_palissade" => $this->view->user->armure_naturelle_hobbit * 4,
			"pv_restant_palissade" => $this->view->user->pv_restant_hobbit,
			"pv_max_palissade" => $this->view->user->pv_restant_hobbit,
			"date_creation_palissade" => $date_creation,
			"date_fin_palissade" => $date_fin,
		);

		$palissadeTable = new Palissade();
		$palissadeTable->insert($data);
		unset($palissadeTable);

		Zend_Loader::loadClass("StatsFabricants");
		$statsFabricants = new StatsFabricants();
		$moisEnCours  = mktime(0, 0, 0, date("m"), 2, date("Y"));
		$dataFabricants["niveau_hobbit_stats_fabricants"] = $this->view->user->niveau_hobbit;
		$dataFabricants["id_fk_hobbit_stats_fabricants"] = $this->view->user->id_hobbit;
		$dataFabricants["mois_stats_fabricants"] = date("Y-m-d", $moisEnCours);
		$dataFabricants["nb_piece_stats_fabricants"] = 1;
		$dataFabricants["id_fk_metier_stats_fabricants"] = $this->view->config->game->metier->bucheron->id;
		$statsFabricants->insertOrUpdate($dataFabricants);

		$this->view->palissade = $data;
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_competences_metiers", "box_vue", "box_laban", "box_charrette"));
	}
}
