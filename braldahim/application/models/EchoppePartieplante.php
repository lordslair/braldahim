<?php

class EchoppePartieplante extends Zend_Db_Table {
	protected $_name = 'echoppe_partieplante';
	protected $_primary = array('id_fk_type_echoppe_partieplante', 'id_echoppe_echoppe_partieplantefk_fk_');
	
    function findByIdEchoppe($id_echoppe) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe_partieplante', '*')
		->from('type_partieplante', '*')
		->where('id_fk_echoppe_echoppe_partieplante = '.intval($id_echoppe))
		->where('echoppe_partieplante.id_fk_type_echoppe_partieplante = type_partieplante.id_type_partieplante');
		$sql = $select->__toString();

		return $db->fetchAll($sql);
    }
    
	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe_partieplante', 
		'count(*) as nombre, quantite_caisse_echoppe_partieplante as quantiteCaisse,'
		.' quantite_arriere_echoppe_minerai as quantiteArriere'
		.' quantite_preparees_echoppe_minerai as quantitePreparee')
		->where('id_fk_type_echoppe_partieplante = ?',$data["id_fk_type_echoppe_partieplante"])
		->where('id_fk_echoppe_echoppe_partieplantefk_fk_ = ?',$data["id_fk_echoppe_echoppe_partieplante"])
		->group('quantite');
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantiteCaisse = $resultat[0]["quantiteCaisse"];
			$quantiteArriere = $resultat[0]["quantiteArriere"];
			$quantitePreparee = $resultat[0]["quantitePreparee"];
			
			
			$dataUpdate = array(
			'quantite_caisse_echoppe_partieplante' => $quantiteCaisse + $data["quantite_caisse_echoppe_partieplante"],
			'quantite_arriere_echoppe_partieplante' => $quantiteArriere + $data["quantite_arriere_echoppe_partieplante"],
			'quantite_preparee_echoppe_partieplante' => $quantitePreparee + $data["quantite_preparee_echoppe_partieplante"],
			);
			$where = ' id_fk_type_echoppe_partieplante = '.$data["id_fk_type_echoppe_partieplante"];
			$where .= ' AND id_fk_echoppe_echoppe_partieplante = '.$data["id_fk_echoppe_echoppe_partieplante"];
			$this->update($dataUpdate, $where);
		}
	}
}
