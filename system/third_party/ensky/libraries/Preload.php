<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Preload Model
 *
 * @author Ensky Lin (linhomeyeu@gmail.com)
 */
class Preload {
	var $css = array();
	var $js = array();
	
	function __construct () {
		if ( !defined('PUBLIC_PATH') ) {
			define('PUBLIC_PATH', FCPATH);
		}
	}
	
	function set_css ($css) {
		if ( is_array($css) ) {
			foreach ($css as $row) {
				$this->set_css($row);
			}
		} else {
			$this->css[] = $css;
		}
	}
	
	function set_js ($js) {
		if ( is_array($js) ) {
			foreach ($js as $row) {
				$this->set_js($row);
			}
		} else {
			$this->js[] = $js;
		}
	}
	
	function get_css () {
		foreach ($this->css AS $row) {
			$time = filemtime(PUBLIC_PATH .'style/'.$row.'.css');
			echo '<link rel="stylesheet" href="'. STATIC_URL .'style/'.$row.'.css?t=' . $time . '" />'."\n";
		}
	}
	
	function get_js () {
		foreach ($this->js AS $row) {
			$time = filemtime(PUBLIC_PATH .'js/'.$row.'.js');
			echo '<script src="'. STATIC_URL .'js/'.$row.'.js?t=' . $time . '"></script>'."\n";
		}
	}
}
