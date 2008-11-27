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
class Bral_Batchs_Palissades extends Bral_Batchs_Batch {
	
	public function calculBatchImpl() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Palissades - calculBatchImpl - enter -");
		Zend_Loader::loadClass('Palissade'); 
		
		$palissadeTable = new Palissade();
		$dateFin = date("Y-m-d H:i:s");
		$where = $palissadeTable->getAdapter()->quoteInto('date_fin_palissade <= ?', $dateFin);
		$nb = $palissadeTable->delete($where);
		unset($palissadeTable);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Palissades - nb:".$nb." - where:".$where);
		
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Palissades - purgeBatch - exit -");
		return "nb delete:".$nb. " date:".$dateFin;
	}
}