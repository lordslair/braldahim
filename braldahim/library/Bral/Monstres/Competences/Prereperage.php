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
abstract class Bral_Monstres_Competences_Prereperage extends Bral_Monstres_Competences_Competence {
	
	const SUITE_REPERAGE_STANDARD = "standard";
	const SUITE_REPERAGE_CASE = "reperagecase";
	const SUITE_DEPLACEMENT = "deplacement";
	
	abstract function enchainerAvecReperageStandard();
	
}