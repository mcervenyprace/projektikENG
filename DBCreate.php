<?php

/*
Script for DB creation. Expects inclusion of constants from module "constants.php" before calling.
Displays information about progress on the screen.
Created DB includes 2 tabules.
tabUzivatele
tabObrazky (contains FK directing to tabUzivatele)
for diagram see "schema-db.bmp"
*/



$servername = servername;
$username = username;
$password = password;
	
$pripojeniBezDB = new mysqli($servername, $username, $password);



if ($pripojeniBezDB->connect_error) {
    die("Connection failed: " . $conn->connect_error);
	echo("<br>");
} 

$databaseName =databasename;

$queryDBCReate = "CREATE DATABASE " . $databaseName;

if ($pripojeniBezDB->query($queryDBCReate) === TRUE) {
    echo("Database created succesfully");
	echo("<br>");
} else {
    echo("Error during creation of database: " . $pripojeniBezDB->error);
	echo("<br>");
}



$pripojeniBezDB->close();


	
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
		echo("During creation of table tabUzivatele occured an error!" . mysqli_error($pripojeni) );
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
		echo("During creation of table tabObrazky occured an error!" . mysqli_error($pripojeni) );
		echo("<br>");		
	}		
	
	
	
	
	
	
	
	
	

?>