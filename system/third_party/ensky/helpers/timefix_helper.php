<?php

function timeFix($time, $yearOff = true) {
	$t = new DateTime( $time );
	
	$diff = time() - $t->getTimestamp();

	if ( $diff < 20 ) {
		$result =  '數秒前';
	} else if ( $diff < 60 ) {
		$result =  $diff.'秒前';
	} else if ( $diff < 3600 ) {
		$result =  floor($diff/60).'分鐘前';
	} else if ( $diff < 86400 ) {
		$result =  floor($diff / 3600).'小時前';
	} else if ( $diff < 86400*7 ) {
		$result =  floor ($diff / (86400)).'天前';
    } else if ( date('Y') == $t->format("Y") ) {	//超過一天改顯示日期的版本
		$result =  $t->format( "m/d" );
	} else {
		$result =  $t->format( "Y/m/d" );
	}
	
	return strval( $result );
}