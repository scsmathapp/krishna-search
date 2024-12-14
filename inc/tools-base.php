<?php

class ToolsBase {

	protected $tools_Cache, $tools_Database, $tools_TranslationMemory, $tools_TransliterationMemory;

	function __construct() {
	}

	protected function Setup_ToolsBase($modul_list) {
		foreach ($modul_list as $modul) {
			switch ($modul) {
				case 'Cache':
					$this->tools_Cache		= new Cache();
					break;
				case 'Database':
					$this->tools_Database	= new Database();
					break;
				case 'TranslationMemory':
					$this->tools_TranslationMemory			= new Data_Pair_Memory($this->tools_Database);
					break;
				case 'TransliterationMemory':
					$this->tools_TransliterationMemory	= new Data_Pair_Memory($this->tools_Database);
					break;
			}
		}
	}

	public function set_cache_used($cache_part, $value) {
		$this->tools_Cache->set_cache_used($cache_part, $value);
	}
	public function set_cache_file_used($cache_part) {
		$this->tools_Cache->set_cache_file_used($cache_part);
	}
	public function get_cache_item_num() {
		return $this->tools_Cache->get_cache_item_num();
	}
	public function clear_cache() {
		$this->tools_Cache->clear_cache();
	}
	public function is_cache_used_and_cache_set($cache_part, $key) {
		return $this->tools_Cache->is_cache_used_and_cache_set($cache_part, $key);
	}
	public function get_cache($cache_part, $key) {
		return $this->tools_Cache->get_cache($cache_part, $key);
	}
	public function is_cache_used_and_set_cache($cache_part, $key, $value) {
		$this->tools_Cache->is_cache_used_and_set_cache($cache_part, $key, $value);
	}

	
	

}