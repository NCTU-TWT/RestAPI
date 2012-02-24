<?php 

function referer ($page) {
	return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $page;
}