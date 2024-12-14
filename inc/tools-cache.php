<?php

class Cache {

	private $cache_used;
	private $cache;
	private $cache_item_num			= 0;
	//private $permanent_cache_item_num;
	private $cache_max_item_num	= 100000000;
	private $cache_file_used;

	function __construct() {
		$this->clear_cache();
	}

	//--------------------------------------------------------------
	// temporary cache
	//--------------------------------------------------------------
	// cache used
	public function set_cache_file_used($cache_part) {
		$file = CACHE_FILES_FOLDER.'/'.CACHE_FILES_PREFIX.$cache_part.CACHE_FILES_POSTFIX;
		$this->set_cache_used($cache_part, TRUE);
		$this->load_cache_file($cache_part, $file);
	}
	public function set_cache_used($cache_part, $value) {
		$this->cache_used[$cache_part] = $value;
	}
	public function is_cache_used($cache_part) {
		return isset($this->cache_used[$cache_part]) ? $this->cache_used[$cache_part] : FALSE;
	}

	// cache
	public function set_cache_root($value) {
		$this->cache = $value;
	}
	public function get_cache_root() {
		return $this->cache;
	}
	public function set_cache($cache_part, $key, $value) {
		//if ($this->cache_item_num < $this->cache_max_item_num) {
			$this->cache[$cache_part][$key] = $value;
			//$this->cache_item_num++;
		//}
	}
	public function get_cache($cache_part, $key) {
		return $this->cache[$cache_part][$key];
	}
	public function is_cache_set($cache_part, $key) {
		return isset($this->cache[$cache_part][$key]);
	}
	public function set_cache_2($cache_part, $key, $key_2, $value) {
		$this->cache[$cache_part][$key][$key_2] = $value;
	}
	public function get_cache_2($cache_part, $key, $key_2) {
		return $this->cache[$cache_part][$key][$key_2];
	}
	public function is_cache_set_2($cache_part, $key, $key_2) {
		return isset($this->cache[$cache_part][$key][$key_2]);
	}
	public function set_cache_3($cache_part, $key, $key_2, $key_3, $value) {
		$this->cache[$cache_part][$key][$key_2][$key_3] = $value;
	}
	public function get_cache_3($cache_part, $key, $key_2, $key_3) {
		return $this->cache[$cache_part][$key][$key_2][$key_3];
	}
	public function is_cache_set_3($cache_part, $key, $key_2, $key_3) {
		return isset($this->cache[$cache_part][$key][$key_2][$key_3]);
	}

	// cache used, cache
	public function is_cache_used_and_cache_set($cache_part, $key) {
		return ($this->is_cache_used($cache_part) && $this->is_cache_set($cache_part, $key));
	}
	/*public function is_cache_used_and_cache_set_3($key_2, $key_3, $cache_part, $key) {
		return ($this->is_cache_used($cache_part) && $this->is_cache_set_3($key_2, $key_3, $cache_part, $key));
	}*/
	public function is_cache_used_and_set_cache($cache_part, $key, $value) {
		if ($this->is_cache_used($cache_part)) {
			$this->set_cache($cache_part, $key, $value);
		}
	}
	/*public function is_cache_used_and_set_cache_3($key_2, $key_3, $cache_part, $key, $value) {
		if ($this->is_cache_used($cache_part)) {
			$this->set_cache_3($key_2, $key_3, $cache_part, $key, $value);
		}
	}*/

	// clear
	public function clear_cache() {
		$this->cache = array();
		$this->cache_item_num = 0;
		$this->cache_file_used = array();
	}
	public function clear_cache_part($part) {
		$this->cache[$part] = array();
	}

	// item num
	public function set_cache_max_item_num($cache_max_item_num) {
		$this->cache_max_item_num = $cache_max_item_num;
	}

	public function get_cache_item_num() {
		$cache_item_num = 0;
		if (!empty($this->cache)) {
			$keysA = array_keys($this->cache);
			foreach ($keysA as $key) {
				$cache_item_num += count($this->cache[$key]);
			}
		}
		return $cache_item_num;
	}
	/*public function get_cache_item_num_2($cache_part) {
		$cache_item_num = 0;
		if (!empty($this->cache[$cache_part])) {
			$keysA = array_keys($this->cache[$cache_part]);
			foreach ($keysA as $key) {
				$cache_item_num += count($this->cache[$cache_part][$key]);
			}
		}
		return $cache_item_num;
	}
	public function get_cache_item_num_3($cache_part, $key) {
		$cache_item_num = 0;
		$keysA = array_keys($this->cache[$cache_part][$key]);
		foreach ($keysA as $key_2) {
			$cache_item_num += count($this->cache[$cache_part][$key][$key_2]);
		}
		return $cache_item_num;
	}*/

	//--------------------------------------------------------------
	// permanent cache: written to file
	//--------------------------------------------------------------
	public function load_cache_file($cache_part, $file) {
		if (file_exists($file)) {
			$this->cache[$cache_part] = read_json_file($file);
		} else {
			$this->cache[$cache_part] = array();
		}
		$this->cache_file_used[$cache_part] = $file;
	}

	public function save_cache_files() {
		//echo_preA($this->cache_file_used);
		foreach ($this->cache_file_used as $cache_part => $file) {
			$this->save_cache_file($cache_part, $file);
		}
	}

	public function save_cache_file($cache_part, $file) {
		//echo_str($cache_part.' : '.count($this->cache[$cache_part]));
		file_put_contents_json_encoded_atomically($file, $this->cache[$cache_part], JSON_FLAGS_FOR_PRETTY_UNICODE);
	}
}