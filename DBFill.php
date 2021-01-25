<?php

/*
script for filling an existing DB with tables.  
CREATED FROM DBCreate by reverse-engineering because of my lack of knowledge of webhosting. Not passing the paramater and creating/not creating DB by state would be better solution.
Expects inclusion of constants from module "constants.php" before call.
Displays information about progress on the screen.
Created DB contains 2 tables
tabUzivatele
tabobrazky (contains FK towards tabUzivatele)
for diagram see "schema-db.bmp"
*/



$servername = servername;
$username = username;
$password = password;
$databaseName =databasename;



	
$pripojeni = mysqli_connect($servername, $username, $password,$databaseName);

$sqlDotaz = "CREATE TABLE 
tabUzivatele(
uzivatelID SMALLINT AUTO_INCREMENT,
uzivatelJmeno VARCHAR(20) NOT NULL,
uzivatelHeslo VARCHAR(20) NOT NULL,
PRIMARY KEY(uzivatelID)

);
";

	$prubehOK = $pripojeni->query($sqlDotaz);
	
	if($prubehOK === true){
		echo("Table tabUzivatele created!");
		echo("<br>");			
	}
	else{
		echo("During creation of table tabUzivatele an error occured!" . mysqli_error($pripojeni) );
		echo("<br>");		
	}	

	
$sqlDotaz = "CREATE TABLE 
tabObrazky(
obrazekID SMALLINT AUTO_INCREMENT,
filepath VARCHAR(60) NOT NULL,
uzivatelID SMALLINT,
PRIMARY KEY(obrazekID),
FOREIGN KEY (uzivatelID)
    REFERENCES tabUzivatele(uzivatelID)

);
";

	$prubehOK = $pripojeni->query($sqlDotaz);
	
	if($prubehOK === true){
		echo("Table tabObrazky created!");
		echo("<br>");			
	}
	else{
		echo("During creation of table tabObrazky an error occured!" . mysqli_error($pripojeni) );
		echo("<br>");		
	}		
	
	
	
	
	
	
	
	
	

?>