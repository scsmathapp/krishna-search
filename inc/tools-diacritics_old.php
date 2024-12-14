<?php
//------------------------------------------------------------------------------------------------------------------------
// Remove diacritics
//------------------------------------------------------------------------------------------------------------------------
class Diacritics {
	
	private 
		$diacritic_rules_patterns, 
		$diacritic_rules_replacements,
		$diacritic_rules_patterns_regex,
		$diacritic_rules_replacements_regex,
		$diacritic_charsubstitutions_patterns, 
		$diacritic_charsubstitutions_replacements,
		$diacritic_charsubstitutions_chandrabindu_patterns, 
		$diacritic_charsubstitutions_chandrabindu_replacements;
	
	public function Remove_Diacritics_by_Rules_by_Substitutions_result_text_by_rules($text, $by_rules = TRUE, $by_substitutions = TRUE) {
		$retA = $this->Remove_Diacritics_by_Rules_by_Substitutions($text, $by_rules, $by_substitutions);
		return $retA['text_by_rules'];
	}
	
	public function Remove_Diacritics_by_Rules_by_Substitutions_result_text_by_substitutions($text, $by_rules = TRUE, $by_substitutions = TRUE) {
		$retA = $this->Remove_Diacritics_by_Rules_by_Substitutions($text, $by_rules, $by_substitutions);
		return $retA['text_by_substitutions'];
	}

	public function Remove_Diacritics_by_Rules_by_Substitutions($text, $by_rules = TRUE, $by_substitutions = TRUE) {
		
		$textA['by_rules'] = $textA['by_substitutions'] = '';

		if ($by_rules) {

			$textA['by_rules'] = $text;

			// 1. rules: regex
			$textA['by_rules'] = preg_replace(
														$this->diacritic_rules_patterns_regex, 
														$this->diacritic_rules_replacements_regex, 
														$textA['by_rules']);

			// 2. rules: regular
			$textA['by_rules'] = str_replace(
														$this->diacritic_rules_patterns, 
														$this->diacritic_rules_replacements, 
														$textA['by_rules']);
		}

		$is_copy_by_rules_to_by_substitutions = FALSE;

		if ($by_rules) {
			if ($by_substitutions) {
				if ($textA['by_rules'] !== $text) {
					$textA['by_substitutions'] = $text;
					$process_partA = array('by_rules', 'by_substitutions');
				} else {
					$process_partA = array('by_rules');
					// copy 'by_rules' later to 'by_substitutions'
					$is_copy_by_rules_to_by_substitutions = TRUE;
				}
			} else {
				$process_partA = array('by_rules');
				$textA['by_substitutions'] = $text;
			}
		} else {
			if ($by_substitutions) {
				$textA['by_substitutions'] = $text;
				$process_partA = array('by_substitutions');
			} else {
				$process_partA = array();
			}
		}

		foreach ($process_partA as $process_part) {
			
			// 2. chandrabindu
			//$textA[$process_part] = str_replace(	$this->diacritic_charsubstitutions_chandrabindu_patterns, 
			//										$this->diacritic_charsubstitutions_chandrabindu_replacements, 
			//										$textA[$process_part]);
			
			// 3. chandrabindu on English characters
			//$textA[$process_part] = mb_ereg_replace('([a-zA-Z]+)̐', '\\1', $textA[$process_part]);
			//$textA[$process_part] = preg_replace('/[\x{0310}]/u', '', $textA[$process_part]);

			// remove all combining diacritical marks
			$textA[$process_part] = preg_replace('/[\x{0300}-\x{036f}]+/u', '', $textA[$process_part]);

			// 4. character substitutions
			$textA[$process_part] = str_replace(
																	$this->diacritic_charsubstitutions_patterns, 
																	$this->diacritic_charsubstitutions_replacements, 
																	$textA[$process_part]);
		}

		if ($is_copy_by_rules_to_by_substitutions) {
			$textA['by_substitutions'] = $textA['by_rules'];
		}

		return array(	'text_by_rules' 					=> $textA['by_rules'], 
									'text_by_substitutions' 	=> $textA['by_substitutions']);
	}
	
	

	//------------------------------------
	// Constructor and settings
	//------------------------------------
	public function __construct() {
		
		//---------------------------------------------------------------------------------
		// 1. Diacritics => plain English (v1: by rules)
		//
		// $diacritic_rules_patterns => $diacritic_rules_replacements
		//---------------------------------------------------------------------------------
		$this->diacritic_rules_patterns = $this->diacritic_rules_replacements = array();
		$diacritic_rules_patterns_raw 	= $diacritic_rules_replacements_raw 	= array();
		
		$this->diacritic_rules_patterns_regex 			= array('/ś[rR][iIīĪ]/u', '/Ś[rR][iIīĪ]/u');
		$this->diacritic_rules_replacements_regex 	= array('sri', 						'Sri');

		$diacritic_rules_patterns_raw[] 		= array('ṛ', 	'Ṛ', 	'kṣ', 'Kṣ', 'KṢ', 'ṣ', 	'Ṣ', 	
																								//'śrī', 'śri', 'Śrī', 'Śri', 'ŚRĪ', 'ŚRI', 
																								'ś', 	'Ś',	'chā̐d', 	'CHĀ̐D');

		$diacritic_rules_replacements_raw[] = array('ri', 'Ri', 'ks', 'Ks', 'KS', 'sh', 'Sh', 
																								//'sri', 'sri', 'Sri', 'Sri', 'Sri', 'Sri', 
																								'sh', 'Sh', 'chand', 	'CHAND');
		
		// Build final diacritic patterns
		foreach ($diacritic_rules_patterns_raw as $dataA) {
			foreach ($dataA as $item) {
				$this->diacritic_rules_patterns[] = $item;
			}
		}
		foreach ($diacritic_rules_replacements_raw as $dataA) {
			foreach ($dataA as $item) {
				$this->diacritic_rules_replacements[] = $item;
			}
		}
		
		//---------------------------------------------------------------------------------
		// 2. Diacritics => plain English (v2: by character substitutions)
		//
		// $diacritic_charsubstitutions_patterns => $diacritic_charsubstitutions_replacements
		//---------------------------------------------------------------------------------
		$this->diacritic_charsubstitutions_patterns = $this->diacritic_charsubstitutions_replacements = array();
		$diacritic_charsubstitutions_patterns_raw = $diacritic_charsubstitutions_replacements_raw = array();
		
		// ĀāḌḍĪīḤḥḶḷḸḹḻṀṁṃṄṅṆṇṚṛṜṝṢṣŚśṬṭŪū
		$diacritic_charsubstitutions_patterns_raw[] 		= array('Ā', 'ā', 'Ḍ', 'ḍ', 'Ī', 'ī', 'Ḥ', 'ḥ', 'Ḷ', 'ḷ', 'Ḹ', 'ḹ', 'ḻ', 'Ṁ', 'ṁ', 'ṃ', 'Ṅ', 'ṅ', 'Ṇ', 'ṇ', 'Ñ', 'ñ', 'Ṛ', 'ṛ', 'Ṝ', 'ṝ', 'Ṣ', 'ṣ', 'Ś', 'ś', 'Ṭ', 'ṭ', 'Ū', 'ū');
		$diacritic_charsubstitutions_replacements_raw[] = array('A', 'a', 'D', 'd', 'I', 'i', 'H', 'h', 'L', 'l', 'L', 'l', 'l', 'M', 'm', 'm', 'N', 'n', 'N', 'n', 'N', 'n', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'T', 't', 'U', 'u');
		
		
		// Build final diacritic patterns
		foreach ($diacritic_charsubstitutions_patterns_raw as $dataA) {
			foreach ($dataA as $item) {
				$this->diacritic_charsubstitutions_patterns[] = $item;
			}
		}
		foreach ($diacritic_charsubstitutions_replacements_raw as $dataA) {
			foreach ($dataA as $item) {
				$this->diacritic_charsubstitutions_replacements[] = $item;
			}
		}
		
		//---------------------------------------------------------------------------------
		// 3. Diacritics => plain English (v3: by chandrabindu)
		//
		// $diacritic_charsubstitutions_chandrabindu_patterns => $diacritic_charsubstitutions_chandrabindu_replacements
		//---------------------------------------------------------------------------------
		$this->diacritic_charsubstitutions_chandrabindu_patterns 	= $this->diacritic_charsubstitutions_chandrabindu_replacements = array();
		$diacritic_charsubstitutions_chandrabindu_patterns_raw 		= $diacritic_charsubstitutions_chandrabindu_replacements_raw =array();
		
		// Ā̐ā̐Ḍ̐ḍ̐Ī̐ī̐Ḥ̐ḥ̐Ḷ̐ḷ̐Ṁ̐ṁ̐ṃ̐Ṅ̐ṅ̐Ṇ̐ṇ̐o̐Ṛ̐ṛ̐Ṝ̐ṝ̐Ṣ̐ṣ̐Ś̐ś̐Ṭ̐ṭ̐Ū̐ū̐
		$diacritic_charsubstitutions_chandrabindu_patterns_raw[] 			= array('Ā̐', 'ā̐', 'Ḍ̐', 'ḍ̐', 'Ī̐', 'ī̐', 'Ḥ̐', 'ḥ̐', 'Ḷ̐', 'ḷ̐', 'Ṁ̐', 'ṁ̐', 'ṃ̐', 'Ṅ̐', 'ṅ̐', 'Ṇ̐', 'ṇ̐', 'Ñ̐', 'ñ̐', 'o̐', 'Ṛ̐', 'ṛ̐', 'Ṝ̐', 'ṝ̐', 'Ṣ̐', 'ṣ̐', 'Ś̐', 'ś̐', 'Ṭ̐', 'ṭ̐', 'Ū̐', 'ū̐');
		$diacritic_charsubstitutions_chandrabindu_replacements_raw[] 	= array('A', 'a', 'D', 'd', 'I', 'i', 'H', 'h', 'L', 'l', 'M', 'm', 'm', 'N', 'n', 'N', 'n', 'N', 'n', 'o', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'T', 't', 'U', 'u');
		
		// Build final diacritic patterns
		foreach ($diacritic_charsubstitutions_chandrabindu_patterns_raw as $dataA) {
			foreach ($dataA as $item) {
				$this->diacritic_charsubstitutions_chandrabindu_patterns[] = $item;
			}
		}
		foreach ($diacritic_charsubstitutions_chandrabindu_replacements_raw as $dataA) {
			foreach ($dataA as $item) {
				$this->diacritic_charsubstitutions_chandrabindu_replacements[] = $item;
			}
		}
	}
} // class Diacritics
?>