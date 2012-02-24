<?php
function toBig5 ($fname) {
	return @iconv('UTF-8','BIG5//TRANSLIT//IGNORE',$fname);
}

function toUTF8 ($fname) {
	return @iconv('BIG5','UTF-8',$fname);
}