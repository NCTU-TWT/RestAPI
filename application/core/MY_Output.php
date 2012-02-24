<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Output extends CI_Output {
	
	protected $json;
	
	function __construct () {
		parent::__construct();
		$this->cleanup();
	}
	
	public function cleanup () {
		$this->json = array(
			'http' => array(
				'code' => 200,
				'msg'  => ''
			)
		);
		return $this;
	}
	
	function set_data ($key, $val=NULL) {
		if ( $val !== NULL ) {
			$this->json['data'][$key] = $val;
		} else {
			$this->json['data'] = $key;
		}
		return $this;
	}
	
	function http_code ($code = NULL) {
		if ( $code === NULL ) {
			return $this->json['http']['code'];
		} else {
			$this->json['http']['code'] = $code;
			return $this;
		}
	}
	
	function http_msg ($msg = NULL) {
		if ( $msg === NULL ) {
			return $this->json['http']['msg'];
		} else {
			$this->json['http']['msg'] = $msg;
			return $this;
		}
	}
	
	function obj () {
		return (object)$this->json;
	}
	
	function json ($code=NULL, $msg=NULL) {
		if ($code !== NULL) {
			$this->http_code($code);
		}
		if ($msg !== NULL) {
			$this->http_msg($msg);
		}
		if ( isset($this->json['data']) AND is_array($this->json['data']) ) {
			$this->json['count'] = count($this->json['data']);
		}

		$this->set_header('HTTP/1.1 '.$this->json['http']['code']);
		$this->set_content_type('application/json');
		$this->set_output(json_encode($this->json));
	}
	
	function error ($code=NULL, $msg=NULL) {
		$this->json($code, $msg);
		$this->_display();
		exit;
	}
}