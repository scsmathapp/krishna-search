<?php
if (!defined('TRANSLITERATOR_ANY_LATIN_ASCII')) {
	define('TRANSLITERATOR_ANY_LATIN_ASCII', ('Any-Latin; Latin-ASCII;'));
}
//------------------------------------------------------------------------------------------------------------------------
// Remove diacritics
// 		from Latin script
//
// - remove from letters by each letter from a string:
//		preg_match_all('/\\p{L}\\p{M}*/u', $word, $matches);
//		$word_lettersA = $matches[0];
//		foreach ($word_lettersA as $letter) {
//------------------------------------------------------------------------------------------------------------------------
class Diacritics extends ToolsBase {
	
	private 
		$diacritic_char_lists,
		$diacritic_rules_patterns_regular, $diacritic_rules_replacements_regular,
		$diacritic_rules_patterns_regex, $diacritic_rules_replacements_regex,
		$diacritic_charsubstitutions_patterns, $diacritic_charsubstitutions_replacements,
		$diacritic_charsubstitutions_pattern_replacements_all;
	
	public function __construct() {
		parent::Setup_ToolsBase(['Cache']);

		$this->tools_Cache->set_cache_used('Remove_Diacritics_by_Rules_by_Substitutions', TRUE);

		$this->Setup_Diacritics();
	}

	public function set_cache_used($cache_part, $value) {
		$this->tools_Cache->set_cache_used($cache_part, $value);
	}
	
	public function get_diacritic_char_lists() {
		return $this->diacritic_char_lists;
	}

	public function get_diacritic_char_list_joined() {
		$str = '';
		foreach ($this->diacritic_char_lists as $char_list) {
			$str .= $char_list;
		}
		return $str;
	}

	public function get_diacritic_charsubstitutions_pattern_replacements_all() {
		return $this->diacritic_charsubstitutions_pattern_replacements_all;
	}

	public function Remove_Diacritics_by_Rules_by_Substitutions_result_text_by_rules($text, $by_rules = TRUE, $by_substitutions = TRUE, $do_transliterate_to_latin_ascii = TRUE) {
		$retA = $this->Remove_Diacritics_by_Rules_by_Substitutions($text, $by_rules, $by_substitutions, $do_transliterate_to_latin_ascii);
		return $retA['text_by_rules'];
	}
	
	public function Remove_Diacritics_by_Rules_by_Substitutions_result_text_by_substitutions($text, $by_rules = TRUE, $by_substitutions = TRUE, $do_transliterate_to_latin_ascii = TRUE) {
		$retA = $this->Remove_Diacritics_by_Rules_by_Substitutions($text, $by_rules, $by_substitutions, $do_transliterate_to_latin_ascii);
		return $retA['text_by_substitutions'];
	}

	public function Remove_Diacritics_by_Substitutions_result_text_by_substitutions($text, $do_transliterate_to_latin_ascii = TRUE) {
		$retA = $this->Remove_Diacritics_by_Rules_by_Substitutions($text, FALSE, TRUE, $do_transliterate_to_latin_ascii);
		return $retA['text_by_substitutions'];
	}

	public function Remove_Diacritics_by_Rules_by_Substitutions($text, $by_rules = TRUE, $by_substitutions = TRUE, $do_transliterate_to_latin_ascii = TRUE) {
		
		if (is_array($text)) {
			echo_str('Error Remove_Diacritics_by_Rules_by_Substitutions. Text is array.');
			echo_preA($text);
			exit;
		}
		$query_id = $text.'-'.($by_rules ? '1' : '0').'-'.($by_substitutions ? '1' : '0').'-'.($do_transliterate_to_latin_ascii ? '1' : '0');

		if ($this->tools_Cache->is_cache_used_and_cache_set('Remove_Diacritics_by_Rules_by_Substitutions', $query_id)) {
			$ret_wordsA = $this->tools_Cache->get_cache('Remove_Diacritics_by_Rules_by_Substitutions', $query_id);

		} else {

			// from Latin script

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
															$this->diacritic_rules_patterns_regular, 
															$this->diacritic_rules_replacements_regular, 
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
				
				// U+0310		̐			cc 90		COMBINING CANDRABINDU
				//$textA[$process_part] = preg_replace('/\xCC\x90/u', '', $textA[$process_part]);
				
				// remove all combining diacritical marks
				//$textA[$process_part] = preg_replace('/[\\x{0300}-\\x{036F}]+/u', '', $textA[$process_part]);

				// 3. remove all combining marks
				$textA[$process_part] = preg_replace('/\\p{M}+/u', '', $textA[$process_part]);

				// 4. character substitutions
				$textA[$process_part] = str_replace(
																		$this->diacritic_charsubstitutions_patterns, 
																		$this->diacritic_charsubstitutions_replacements, 
																		$textA[$process_part]);

				// 5. to latin
				if ($do_transliterate_to_latin_ascii) {
					$textA[$process_part] = transliterator_transliterate(TRANSLITERATOR_ANY_LATIN_ASCII, $textA[$process_part]);
				}
			}

			if ($is_copy_by_rules_to_by_substitutions) {
				$textA['by_substitutions'] = $textA['by_rules'];
			}

			// result
			$ret_wordsA = array('text_by_rules' 					=> $textA['by_rules'], 
													'text_by_substitutions' 	=> $textA['by_substitutions']);
			
			if ($this->tools_Cache->is_cache_used_and_set_cache('Remove_Diacritics_by_Rules_by_Substitutions', $query_id, $ret_wordsA));
		}

		return $ret_wordsA;
	}
	
	

	//------------------------------------
	// Setup
	//------------------------------------
	private function Setup_Diacritics() {

		//---------------------------------------------------------------------------------
		// 1. Diacritics => plain English (v1: by rules)
		//
		// $diacritic_rules_patterns_regular => $diacritic_rules_replacements_regular
		//---------------------------------------------------------------------------------
		$this->diacritic_rules_patterns_regular = $this->diacritic_rules_replacements_regular = array();
		$diacritic_rules_patterns_regular_raw 	= $diacritic_rules_replacements_regular_raw 	= array();
		
		$this->diacritic_rules_patterns_regex 			= array('/ś[rR][iIīĪ]/u', '/Ś[rR][iIīĪ]/u');
		$this->diacritic_rules_replacements_regex 	= array('sri', 						'Sri');

		$diacritic_rules_patterns_regular_raw[] 		= array('chā̐d',  'Chā̐d', 	'CHĀ̐D', 	'kṣ', 'Kṣ', 'KṢ', 'ṛ', 	'Ṛ', 	'ṣ', 	'Ṣ', 	'ś', 	'Ś');
		$diacritic_rules_replacements_regular_raw[] = array('chand', 'Chand', 'CHAND', 	'ks', 'Ks', 'KS', 'ri', 'Ri', 'sh', 'Sh',	'sh', 'Sh');
		
		// Build final diacritic patterns
		foreach ($diacritic_rules_patterns_regular_raw as $dataA) {
			foreach ($dataA as $item) {
				$this->diacritic_rules_patterns_regular[] = $item;
			}
		}
		foreach ($diacritic_rules_replacements_regular_raw as $dataA) {
			foreach ($dataA as $item) {
				$this->diacritic_rules_replacements_regular[] = $item;
			}
		}
		
		//---------------------------------------------------------------------------------
		// 2. Diacritics => plain English (v2: by character substitutions)
		//
		// $diacritic_charsubstitutions_patterns => $diacritic_charsubstitutions_replacements
		//---------------------------------------------------------------------------------
		$this->diacritic_charsubstitutions_patterns = $this->diacritic_charsubstitutions_replacements = array();
		$diacritic_charsubstitutions_patterns_raw = $diacritic_charsubstitutions_replacements_raw = array();
		
		// ĀāḌḍĪīḤḥḶḷḸḹḻṀṁṃṄṅṆṇÑñṚṛṜṝṢṣŚśṬṭŪū
		//$diacritic_charsubstitutions_patterns_raw[] 		= array('ā̐', 'Ā̐');
		//$diacritic_charsubstitutions_replacements_raw[] = array('a', 'A');
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
		// pattern-replacements all
		//---------------------------------------------------------------------------------
		$this->diacritic_charsubstitutions_pattern_replacements_all = array();
		
		// Build final diacritic pattern-replacements all
		foreach ($diacritic_charsubstitutions_patterns_raw as $i1 => $dataA) {
			foreach ($dataA as $i2 => $item) {
				$this->diacritic_charsubstitutions_pattern_replacements_all[$item] = $diacritic_charsubstitutions_replacements_raw[$i1][$i2];
			}
		}


		//---------------------------------------------------------------------------------
		// diacritic char list
		//---------------------------------------------------------------------------------
		$this->diacritic_char_lists = array(
			'Sanskrit_letters' 						=> 'ĀāḌḍĪīḤḥḶḷḸḹḻṀṁṃṄṅṆṇÑñṚṛṜṝṢṣŚśṬṭŪū',
			'letters_with_macron' 				=> 'ĀāĒēĪīŌōŪūǕǖǗǘǞǟǠǡǢǣǬǭȪȫȬȭȰȱȲȳӢӣӮḆḇḎḏḠḡḴḵḸḹḺḻṈṉṜṝṞṟṮṯẔẕẖᾱᾹῑῙῡῩ',
			'letters_with_dot' 						=> 'ĊċĖėĠġİıŻżǠǡȦȧȮȯḂḃḄḅḊḋḌḍḞḟḢḣḤḥḲḳḶḷṀṁṂṃṄṅṆṇṖṗṘṙṚṛṠṡṢṣṪṫṬṭṾṿẆẇẈẉẊẋẎẏẒẓẛẠạẬậẶặẸẹỊịỌọỢợỤụỰựỴỵ',
			'combining_diacritical_marks' => '\\x{0300}-\\x{036F}',
		);
	}
} // class Diacritics
?>