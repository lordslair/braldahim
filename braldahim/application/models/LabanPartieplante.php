<?php

class LabanPartieplante extends Zend_Db_Table {
	protected $_name = 'laban_partieplante';
	protected $_primary = array('id_fk_type_laban_partieplante', 'id_fk_hobbit_laban_partieplante');
	
    function findByIdHobbit($id_hobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('laban_partieplante', '*')
		->from('type_partieplante', '*')
		->from('type_plante', '*')
		->where('id_fk_hobbit_laban_partieplante = '.intval($id_hobbit))
		->where('laban_partieplante.id_fk_type_laban_partieplante = type_partieplante.id_type_partieplante')
		->where('laban_partieplante.id_fk_type_plante_laban_partieplante = type_plante.id_type_plante')
		->order(array('nom_type_plante', 'nom_type_partieplante'));
		$sql = $select->__toString();

		return $db->fetchAll($sql);
    }
    
	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('laban_partieplante', 'count(*) as nombre, quantite_laban_partieplante as quantiteBrute,  quantite_preparee_laban_partieplante as quantitePreparee')
		->where('id_fk_type_laban_partieplante = ?',$data["id_fk_type_laban_partieplante"])
		->where('id_fk_hobbit_laban_partieplante = ?',$data["id_fk_hobbit_laban_partieplante"])
		->where('id_fk_type_plante_laban_partieplante = ?',$data["id_fk_type_plante_laban_partieplante"])
		->group(array('quantiteBrute', 'quantitePreparee'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantiteBrute = $resultat[0]["quantiteBrute"];
			$quantitePreparee = $resultat[0]["quantitePreparee"];
			
			$dataUpdate['quantite_laban_partieplante']  = $quantiteBrute;
			$dataUpdate['quantite_preparee_laban_partieplante']  = $quantitePreparee;
			
			if (isset($data["quantite_laban_partieplante"])) {
				$dataUpdate = array('quantite_laban_partieplante' => $quantiteBrute + $data["quantite_laban_partieplante"]);
			};
			
			if (isset($data["quantite_preparee_laban_partieplante"])) {
				$dataUpdate = array('quantite_preparee_laban_partieplante' => $quantitePreparee + $data["quantite_preparee_laban_partieplante"]);
			};
			
			$where = ' id_fk_type_laban_partieplante = '.$data["id_fk_type_laban_partieplante"];
			$where .= ' AND id_fk_hobbit_laban_partieplante = '.$data["id_fk_hobbit_laban_partieplante"];
			$where .= ' AND id_fk_type_plante_laban_partieplante = '.$data["id_fk_type_plante_laban_partieplante"];
			$this->update($dataUpdate, $where);
		}
	}
}
