<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
abstract class Bral_Filature_Filature
{

	function __construct($nomSystemeAction, $request, $view, $action, $idFilatureDefaut = null)
	{
		$this->view = $view;
		$this->request = $request;
		$this->action = $action;
		$this->nom_systeme = $nomSystemeAction;
		$this->idFilatureDefaut = $idFilatureDefaut;

		$this->calculNbPa();
		$this->prepareCommun();

		switch ($this->action) {
			case "ask" :
				$this->prepareFormulaire();
				break;
			case "do":
				$this->prepareResultat();
				break;
			default:
				throw new Zend_Exception(get_class($this) . "::action invalide :" . $this->action);
		}
	}

	abstract function prepareCommun();

	abstract function prepareFormulaire();

	abstract function prepareResultat();

	abstract function getListBoxRefresh();

	abstract function getNomInterne();

	abstract function getTitreAction();

	public function calculNbPa()
	{
		$this->view->assezDePa = true;
	}

	public function getIdEchoppeCourante()
	{
		return false;
	}

	public function getTablesHtmlTri()
	{
		return false;
	}

	public function getIdChampCourant()
	{
		return false;
	}

	public function render()
	{
		$this->view->titreAction = $this->getTitreAction();
		switch ($this->action) {
			case "ask":
				return $this->view->render("filature/" . $this->nom_systeme . "_formulaire.phtml");
				break;
			case "do":
				$texte = $this->view->render("filature/" . $this->nom_systeme . "_resultat.phtml");
				// suppression des espaces : on met un espace à la place de n espaces à suivre
				$this->view->texte = trim(preg_replace('/\s{2,}/', ' ', $texte));
				return $this->view->render("competences/commun_resultat.phtml");
				break;
			default:
				throw new Zend_Exception(get_class($this) . "::action invalide :" . $this->action);
		}
	}

}