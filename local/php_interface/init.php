<?php
    if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
     
    if (file_exists($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/include/constants.php"))
    	require_once($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/include/constants.php");
     
    if (file_exists($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/include/handlers.php"))
    	require_once($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/include/handlers.php");