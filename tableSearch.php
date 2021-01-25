<?php
/*
	PHP module for searching data in table tabObrazky, by string "textHledani" in $_GET. 
	uses 'constants.php' - to create connection, a nd'LoadTable.php' - to load the data
	returned DOMDocument from method 'LoadTable.php' is printed (print_r) in the end.
	
*/

	include 'constants.php';	
	
	$textHledani=$_GET["textHledani"];
	
	$pripojeni = new mysqli(servername, username, password, databasename);
	
	$naseptavacText = $textHledani;
		
	$dom = new DOMDocument();
	
	$tabulkaObrazku = include 'LoadTable.php';
	
	$dom->appendChild($tabulkaObrazku);
	
	print_r( $dom->saveHTML() );	

?>