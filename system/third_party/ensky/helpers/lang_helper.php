<?php
function get_lang () {
	$get_languages = function () {
		// get the languages
		$index = '';
		$complete = '';
		$found = false;// set to default value
		//prepare user language array
		$user_languages = array();

		//check to see if language is set
		if ( isset( $_SERVER["HTTP_ACCEPT_LANGUAGE"] ) ) {
			$languages = strtolower( $_SERVER["HTTP_ACCEPT_LANGUAGE"] );
			// $languages = ' fr-ch;q=0.3, da, en-us;q=0.8, en;q=0.5, fr;q=0.3';
			// need to remove spaces from strings to avoid error
			$languages = str_replace( ' ', '', $languages );
			$languages = explode( ",", $languages );
			//$languages = explode( ",", $test);// this is for testing purposes only

			foreach ( $languages as $language_list ) {
				// pull out the language, place languages into array of full and primary
				// string structure:
				$temp_array = array();
				// slice out the part before ; on first step, the part before - on second, place into array
				$temp_array[0] = substr( $language_list, 0, strcspn( $language_list, ';' ) );//full language
				$temp_array[1] = substr( $language_list, 0, 2 );// cut out primary language
				//place this array into main $user_languages language array
				$user_languages[] = $temp_array;
			}
		} else {
			// if no language found.
			$user_languages[0] = array( '','','','' ); //return blank array.
		}
		return $user_languages;
	};

	$lang_convert = function ($lang) {
		switch($lang) {
			case'zh':
				return 'zh_tw';
			case'zh-tw':
				return 'zh_tw';
			default:
				return $lang;
		}
	};

	$avail_lang = array(
		'en','zh_tw'
	);
	
	$lang = isset($_COOKIE['lang']) ? $_COOKIE['lang'] : False;
	if ( $lang !== False AND in_array($lang,$avail_lang) ) {
		return $lang;
	}
	
	$lang = 'zh_tw';
	
	if ( isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
		$langes = $get_languages();
		for ( $i = 0 ; $i < count($langes) ; $i++ ) {
			if ( in_array( $lang_convert($langes[$i][0]) ,$avail_lang ) ) {
				$lang = $lang_convert($langes[$i][0]);
				break;
			} else if ( in_array( $lang_convert($langes[$i][1]) ,$avail_lang ) ) {
				$lang = $lang_convert($langes[$i][1]);
				break;
			}
		}
	}
	setcookie('lang', $lang,time()+60*60*24, '/');
	return 'zh_tw';
}

function __ ($source_str, $index = 'zh_tw') {
	if ( get_lang() == $index ) {
		return $source_str;
	} else {
		$CI = & get_instance();
		$CI->load->config('lang');

		$conf = $CI->config->config['lang'];
		if ( isset($conf[$source_str]) ) {
			return $conf[$source_str];
		} else {
			return $source_str;
		}
	}
}