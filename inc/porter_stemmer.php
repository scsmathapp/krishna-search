<?php
class Porter2Stemmer {

	static  $exceptions1 = array(
            'skis' 		=>'ski',
            'skies'     =>'sky',
            'dying'     =>'die',
            'lying'     =>'lie',
            'tying'     =>'tie',
            //'idly'      =>'idl',
            //'gently'    =>'gentl',
            //'ugly'      =>'ugli',
            //'early'     =>'earli',
            //'only'      =>'onli',
            //'singly'    =>'singl',
            'sky'		=>'sky',
            'news'		=>'news',
            'howe'		=>'howe',
            'atlas'		=>'atlas',
            'cosmos' 	=>'cosmos',
            'bias'		=>'bias',
            'andes'		=>'andes');

	//static $exceptions2 = array("inning", "outing", "canning", "herring", "earring", "proceed", "exceed", "succeed");
	static $exceptions2 = array("inning"=>1, "outing"=>1, "canning"=>1, "herring"=>1, "earring"=>1, "proceed"=>1, "exceed"=>1, "succeed"=>1);

	// Suffixes in steps
	// step0
	static $suffixes_step0 = Array("'s'", "'s", "'");
	static $suffixes_step0_strlen = Array();
	// step1
	static $suffixes_step1b = array(
			"eedly"		=> 	"ee",
			"eed" 		=> 	"ee",
	);
	static $suffixes_step1b_strlen = Array();
	static $suffixes_step1b2 = array(
			"ingly",
			"edly",
			"ing",
			"ed"
	);
	static $suffixes_step1b2_strlen = Array();
	// step2
	static $suffixes_step2 = array(
			"ization" 	=> 	"ize",
			"fulness" 	=> 	"ful",
			"ousness" 	=> 	"ous",
			"iveness" 	=> 	"ive",
			"ational" 	=> 	"ate",
			"biliti" 	=> 	"ble",
			"tional" 	=> 	"tion",
			"lessli"	=> 	"less",
			"ation" 	=> 	"ate",
			"alism"	 	=> 	"al",
			"aliti" 	=> 	"al",
			"ousli" 	=> 	"ous",
			"iviti" 	=> 	"ive",
			"fulli"		=> 	"ful",
			"entli" 	=> 	"ent",
			"enci"		=> 	"ence",
			"anci" 		=> 	"ance",
			"abli" 		=> 	"able",
			"izer" 		=> 	"ize",
			"ator"	 	=> 	"ate",
			"alli" 		=> 	"al",
			"bli" 		=> 	"ble",
			"ogi"		=> 	"og",
	);
	static $suffixes_step2_strlen = array();
	// step3
	static $suffixes_step3 = array(
			"ational"	=> 	"ate",
			"tional" 	=> 	"tion",
			"alize" 	=> 	"al",
			"icate" 	=> 	"ic",
			"ative" 	=> 	"",
			"iciti" 	=> 	"ic",
			"ical" 		=> 	"ic",
			"ness" 		=> 	"",
			"ful" 		=> 	"",
	);
	static $suffixes_step3_strlen = array();
	// step4
	static $suffixes_step4 = array(
			"iveness" 	=> 	"",
			"ement" 	=> 	"",
			"ance"		=> 	"",
			"ence" 		=> 	"",
			"able" 		=> 	"",
			"ible" 		=> 	"",
			"ant" 		=> 	"",
			"ment"	 	=> 	"",
			"ent"	 	=> 	"",
			"ism" 		=> 	"",
			"ate" 		=> 	"",
			"iti" 		=> 	"",
			"ous" 		=> 	"",
			"ive" 		=> 	"",
			"ize" 		=> 	"",
			"ion" 		=> 	"",
			"al" 		=> 	"",
			"er" 		=> 	"",
			"ic" 		=> 	""
	);
	static $suffixes_step4_strlen = array();
	static $precededBy_step4 = array(
			"ion"		=> "s,t"
	);

	//-----------
	
	static $vowel = '([aeiouy]){1}';

	static $consonant = '([bcdfghjklmnpqrstvwxzY]){1}';

	static $consonant_short = '([bcdfghjklmnpqrstvz]){1}';

	static $double = '((bb)|(dd)|(ff)|(gg)|(mm)|(nn)|(pp)|(rr)|(tt))';

	//region after the first non-vowel following a vowel,
	static $r1 = "(?<=([aeiouy]){1}([bcdfghjklmnpqrstvwxzY]){1})[a-zY']*\$";

	static $r1_exceptions = "((?<=^commun)|(?<=^gener)|(?<=^arsen))[a-zY']*\$";

	//region after the first non-vowel following a vowel in R1,
	static $r2 = "(?<=([aeiouy]){1}([bcdfghjklmnpqrstvwxzY]){1})[a-zY']*\$";

	var $R1 = "";

	var $R2 = "";

	function Porter2Stemmer() {
		// Suffixes in steps with strlen(x)
		// step0
		foreach (self::$suffixes_step0 as $suffix) {
		    self::$suffixes_step0_strlen[$suffix] = strlen($suffix);
		}
		// step1b
		foreach (self::$suffixes_step1b as $suffix => $replacement) {
		    self::$suffixes_step1b_strlen[$suffix] = strlen($suffix);
		}
		// step1b2
		foreach (self::$suffixes_step1b2 as $suffix) {
		    self::$suffixes_step1b2_strlen[$suffix] = strlen($suffix);
		}
		// step2
		foreach (self::$suffixes_step2 as $suffix => $replacement) {
		    self::$suffixes_step2_strlen[$suffix] = strlen($suffix);
		}
		// step3
		foreach (self::$suffixes_step3 as $suffix => $replacement) {
		    self::$suffixes_step3_strlen[$suffix] = strlen($suffix);
		}
		// step4
		foreach (self::$suffixes_step4 as $suffix => $replacement) {
		    self::$suffixes_step4_strlen[$suffix] = strlen($suffix);
		}
	}
	
	function Stem($word)
	{
		$word = strtolower($word);

		if (strlen($word) < 3) {
			return $word;

		//} else if (key_exists($word, self::$exceptions1)) {
		} else if (isset(self::$exceptions1[$word])) {
			return self::$exceptions1[$word];

		} else {
			$word = $this->markVowels($word);
			$word = $this->step0($word);
			$word = $this->step1($word);

			//if (!in_array($word, self::$exceptions2)) {
			if (!isset(self::$exceptions2[$word])) {
				$word = $this->step2($word);
				$word = $this->step3($word);
				$word = $this->step4($word);
				$word = $this->step5($word);

			}
			return strtolower($word);
		}
	}


	function endsWithShortSyllable($word)
	{
		$c = self::$consonant;
		$c2 = self::$consonant_short;
		$v = self::$vowel;

			if (strlen($word)<3 && preg_match("#$v#", $word{0})  && preg_match("#$c#", $word{1})){
				return true;
			} else{
				if (preg_match("#$c2#", substr($word,-1)) && (preg_match("#$v#", substr($word, -2, 1)) || substr($word, -2, 1) == 'y') && preg_match("#$c#", substr($word, -3, 1))){
					return true;
				}
			}
		return false;
	}


	/**
	 * Determines if word is short
	 *
	 * @param string $word String to stem
	 * @return boolean
	 */
	function isShort($word)
	{
		$this->updateR1R2($word);

		if ($this->R1 == "") {

			if (preg_match("#^".self::$vowel.self::$consonant."#", $word)) {
				return true;

			} else if (preg_match("#".self::$vowel.self::$consonant_short."\$#", $word)){
				return true;

			}
		}

		return false;
	}


	function markVowels($word)
	{
		$c = self::$consonant;
		$v = self::$vowel;
		$strlen_word = strlen($word);
		for ($i=0; $i<$strlen_word; $i++) {
			$char = $word{$i};
			if ($char == 'y' AND ($i==0 OR ($i>0 AND preg_match("#$v#", $word{$i-1})))){
				$word{$i} = 'Y';
			}
		}
		$this->updateR1R2($word);
		return $word;
	}

	/**
	 * Updates R1 and R2
	 *
	 * @param string $word String to stem
	 */
	function updateR1R2($word)
	{
		preg_match("#".self::$r1_exceptions."#", $word, $matches, PREG_OFFSET_CAPTURE);
		if (sizeof($matches) == 0) {
			preg_match("#".self::$r1."#", $word, $matches, PREG_OFFSET_CAPTURE);
		}
		$this->R1 = (sizeof($matches) > 0 ? $matches[0][0] : "");

		preg_match("#".self::$r2."#", $this->R1, $matches, PREG_OFFSET_CAPTURE);
		$this->R2 = (sizeof($matches) > 0 ? $matches[0][0] : "");
	}


	/**
	 * Determines if $word ends with $suffix
	 *
	 * @param string $suffix
	 * @param string $word
	 * @return boolean
	 */
	function endsWith($suffix, $word)
	{
		if (substr($word, -strlen($suffix)) == $suffix){
			return true;
		}

		return false;
	}

	static function trimR(&$word, $n){
		$word = substr($word, 0, strlen($word)-$n);
		return $word;
	}

	/**
	 * Step 0
	 *
	 * @param string $word String to stem
	 * @return string
	 */
	function step0($word)
	{
		if (substr($word, 0, 1) == "'") {
			$word = substr($word, 1);

		}

		//$suffixes = Array("'s'", "'s", "'");

		//foreach ($suffixes as $suffix) {
		foreach (self::$suffixes_step0 as $suffix) {
			if ($this->endsWith($suffix, $word)) {
				//self::trimR($word, strlen($suffix));
				self::trimR($word, self::$suffixes_step0_strlen[$suffix]);

			}
		}

		return $word;
	}


	/**
	 * Step 1
	 *
	 * @param string $word String to stem
	 * @return string
	 */
	function step1($word)
	{
		$c = self::$consonant;
		$v = self::$vowel;

		//step 1a
		if ($this->endsWith("sses", $word)) {
			$word = substr($word, 0, strlen($word)-4)."ss";

		} else if (	$this->endsWith("ied", $word)
			OR $this->endsWith("ies", $word)) {

			$word = substr($word, 0, strlen($word)-3);

			if (strlen($word) > 1) {
				$word .= "i";

			} else {
				$word .= "ie";

			}
		} else if (	$this->endsWith("s", $word)
			AND substr($word, -2, 1) != "s"
			AND substr($word, -2, 1) != "u") {

			$part = substr($word, 0, strlen($word)-2);
			if (preg_match("#$v#", $part)) {
				self::trimR($word, 1);
			}
		}

		$found = false;

		//if (in_array($word, self::$exceptions2)) {
		if (isset(self::$exceptions2[$word])) {
			return $word;
		}

		//step 1b
		/*
		$suffixes = array(
			"eedly"		=> 	"ee",
			"eed" 		=> 	"ee",
		);*/
		
		//foreach ($suffixes as $suffix => $replacement) {
		foreach (self::$suffixes_step1b as $suffix => $replacement) {
			//if (substr($word, -strlen($suffix)) == $suffix){
			if (substr($word, -self::$suffixes_step1b_strlen[$suffix]) == $suffix){
				$found = true;
				if (strpos($this->R1, $suffix) > -1){
					//$word = self::trimR($word, strlen($suffix)).$replacement;
					$word = self::trimR($word, self::$suffixes_step1b_strlen[$suffix]).$replacement;
					break;
				}
			}
		}
		/*
		$suffixes = array(
			"ingly",
			"edly",
			"ing",
			"ed"
		);*/
		if (!$found){
			//foreach ($suffixes as $suffix) {
			foreach (self::$suffixes_step1b2 as $suffix) {
				//if (substr($word, -strlen($suffix)) == $suffix && preg_match("#$v#", substr($word, 0, strlen($word)- strlen($suffix)))){
				if (substr($word, -self::$suffixes_step1b2_strlen[$suffix]) == $suffix && preg_match("#$v#", substr($word, 0, strlen($word)- self::$suffixes_step1b2_strlen[$suffix]))){
					//$word = self::trimR($word, strlen($suffix));
					$word = self::trimR($word, self::$suffixes_step1b2_strlen[$suffix]);

					if (substr($word, -2) == "at" OR
						substr($word, -2) == "bl" OR
						substr($word, -2) == "iz") {

						$word .= "e";

					} else if (preg_match("#".self::$double."\$#", $word)) {
						$word = self::trimR($word, 1);

					} else if ($this->isShort($word)) {
						$word .= "e";
					}
					break;
				}

			}
		}

		//step 1c
		if ((substr($word, -1) == "y" OR substr($word, -1) == "Y")
			AND preg_match("#$c#", substr($word, -2, 1))
			AND strlen($word) > 2){

			$word = self::trimR($word, 1)."i";
		}

		return $word;
	}


	/**
	 * Step 2
	 *
	 * @param string $word String to stem
	 * @return string
	 */
	function step2($word)
	{
		$this->updateR1R2($word);
/*
		$suffixes = array(
			"ization" 	=> 	"ize",
			"fulness" 	=> 	"ful",
			"ousness" 	=> 	"ous",
			"iveness" 	=> 	"ive",
			"ational" 	=> 	"ate",
			"biliti" 	=> 	"ble",
			"tional" 	=> 	"tion",
			"lessli"	=> 	"less",
			"ation" 	=> 	"ate",
			"alism"	 	=> 	"al",
			"aliti" 	=> 	"al",
			"ousli" 	=> 	"ous",
			"iviti" 	=> 	"ive",
			"fulli"		=> 	"ful",
			"entli" 	=> 	"ent",
			"enci"		=> 	"ence",
			"anci" 		=> 	"ance",
			"abli" 		=> 	"able",
			"izer" 		=> 	"ize",
			"ator"	 	=> 	"ate",
			"alli" 		=> 	"al",
			"bli" 		=> 	"ble",
			"ogi"		=> 	"og",
		);
*/

		$found = false;
		//foreach ($suffixes as $suffix => $newSuffix) {
		foreach (self::$suffixes_step2 as $suffix => $newSuffix) {
			if ($this->endsWith($suffix, $word)) {
				$found=true;
				if (strpos($this->R1, $suffix) > -1) {
					if ($suffix == 'ogi'){
						if (substr($word, -4, 1) == 'l') {
							//special ogi case
							//$word = self::trimR($word, strlen($suffix)).$newSuffix;
							$word = self::trimR($word, self::$suffixes_step2_strlen[$suffix]).$newSuffix;
						}
					} else {
						//$word = self::trimR($word, strlen($suffix)).$newSuffix;
						$word = self::trimR($word, self::$suffixes_step2_strlen[$suffix]).$newSuffix;
					}
				}
				break;
			}
		}

		if (!$found && strpos($this->R1, "li") > -1) {
			$word = preg_replace("#(?<=[cdeghkmnrt])li$#", "", $word);
		}

		return $word;
	}


	/**
	 * Step 3
	 *
	 * @param string $word String to stem
	 * @return string
	 */
	function step3($word)
	{
		$this->updateR1R2($word);
/*
		$suffixes = array(
			"ational"	=> 	"ate",
			"tional" 	=> 	"tion",
			"alize" 	=> 	"al",
			"icate" 	=> 	"ic",
			"ative" 	=> 	"",
			"iciti" 	=> 	"ic",
			"ical" 		=> 	"ic",
			"ness" 		=> 	"",
			"ful" 		=> 	"",
		);
*/
		//foreach ($suffixes as $suffix => $newSuffix) {
		foreach (self::$suffixes_step3 as $suffix => $newSuffix) {
			if ($this->endsWith($suffix, $word)) {
				if (strpos($this->R1, $suffix) > -1) {
					if ($suffix == 'ative') {
						if (strpos($this->R2, $suffix) > -1) {
							//special 'active' case
							//$word = self::trimR($word, strlen($suffix)).$newSuffix;
							$word = self::trimR($word, self::$suffixes_step3_strlen[$suffix]).$newSuffix;

						}
					} else{
						//$word = self::trimR($word, strlen($suffix)).$newSuffix;
						$word = self::trimR($word, self::$suffixes_step3_strlen[$suffix]).$newSuffix;
					}
				}
				break;
			}
		}
		return $word;
	}


	/**
	 * Step 4
	 *
	 * @param string $word String to stem
	 * @return string
	 */
	function step4($word)
	{
		$this->updateR1R2($word);
		/*
		$suffixes = array(
			"iveness" 	=> 	"",
			"ement" 	=> 	"",
			"ance"		=> 	"",
			"ence" 		=> 	"",
			"able" 		=> 	"",
			"ible" 		=> 	"",
			"ant" 		=> 	"",
			"ment"	 	=> 	"",
			"ent"	 	=> 	"",
			"ism" 		=> 	"",
			"ate" 		=> 	"",
			"iti" 		=> 	"",
			"ous" 		=> 	"",
			"ive" 		=> 	"",
			"ize" 		=> 	"",
			"ion" 		=> 	"",
			"al" 		=> 	"",
			"er" 		=> 	"",
			"ic" 		=> 	""
		);
		*/
		/*
		$precededBy = array(
			"ion"		=> "s,t"
		);
		*/
		$found = false;
		//foreach ($suffixes as $suffix => $newSuffix) {
		foreach (self::$suffixes_step4 as $suffix => $newSuffix) {
			if ($this->endsWith($suffix, $word) && !$found) {

				if (strpos($this->R2, $suffix) > -1) {

					//if (key_exists($suffix, $precededBy)) {
					if (isset(self::$precededBy_step4[$suffix])) {

						//$parts = split(',', $precededBy[$suffix]);
						$parts = split(',', self::$precededBy_step4[$suffix]);
						foreach ($parts as $part) {

							//if (substr($word, -(strlen($suffix)+1), strlen($part)) == $part){
							if (substr($word, -(self::$suffixes_step4_strlen[$suffix]+1), strlen($part)) == $part){
								//$word = self::trimR($word, strlen($suffix)).$newSuffix;
								$word = self::trimR($word, self::$suffixes_step4_strlen[$suffix]).$newSuffix;
								break;
							}
						}
					} else{
						//$word = self::trimR($word, strlen($suffix)).$newSuffix;
						$word = self::trimR($word, self::$suffixes_step4_strlen[$suffix]).$newSuffix;
					}
				}
				break;
			}
		}
		return $word;
	}


	/**
	 * Step 5
	 *
	 * @param string $word String to stem
	 * @return string
	 */
	function step5($word)
	{
		$this->updateR1R2($word);
		if (($this->endsWith("e", $word) && strpos($this->R2, "e") > -1)
			OR ($this->endsWith("e", $word)
				AND strpos($this->R1, "e") > -1
				AND !$this->endsWithShortSyllable(substr($word, 0, strlen($word)-1)))){
				self::trimR($word, 1);
		} else if (($this->endsWith("l", $word)
			AND strpos($this->R2, "l") > -1 AND substr($word, -2, 1) == "l" )){
			self::trimR($word, 1);
		}
		return $word;
	}
}

?>