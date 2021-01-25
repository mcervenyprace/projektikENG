<?php
	/*
		Main php module. Uses 'constants.php' (server connection information) and reads all HTML files of the project. 
		Always calls runMain();. Expects non-null variable "switch". In its absence uses value 1 = default.		
		Meanings of value "switch":
		"switch" = 1 = unknown state of DB, by its state shows a message, creates DB or shows user creation/login.  See method urciStavDB().
		"switch" = 2 = state after an attempt to create first user, verifies input data. See pokusSeVytvoritUzivatele().	
		"switch" = 3 = state after an attempt to create nonfirst user, verifies input data. See pokusSePrihlasitUzivatele().
		"switch" = 4 = stater after successful creation of DB. Will move to page where first user will be created. See prejdiNaVytvoreniUzvivatele().
		"switch" = 5 = state after user logoff. Ends session and moves on to login.
		"switch" = 6 = state after  an attempt to delete an image, verifies data (active user), see zkusSmazatObrazek().
		"switch" = 7 = state after an attempt to upload an image see zkusNahratObrazek();
	*/

	
	include 'constants.php';	

		
	runMain();	

	function runMain(){
		/*
			Main function, see module description. Is always called during run of this module.  
		*/
	 
		$spinac = 1;
		
		if(isset($_POST["switch"])){	
			$spinac = $_POST["switch"];
		}
		
		$stavDB = urciStavDB();

		//echo($stavDB);

		//$pripojeni = = PripojSeDoProjektDB();
		//echo $spinac;
			

		if($spinac == 1){
			//case default page, state unknown
			if($stavDB == 0){
				
				echo ("Server connection error occured.");
			}
			if($stavDB == 1){
				echo ("DB does not exist. Creating DB.");
				echo("<br>");
				include 'DBCreate.php';
				echo("<br>");
				echo("If DB is successfuly created, do a refresh (F5).");
			}
			
			if($stavDB == 2){
				readfile("templatePRVNIuzivatel.html");	
			}
			
			if($stavDB == 3){
				readfile("templateDALSIuzivatel.html");	
				
			}
			
			if($stavDB == 4){
				echo ("DB is withouth the required structure. Creating required DB structure.");
				echo("<br>");
				include 'DBFill.php';
				echo("<br>");
				echo("If DB structure is created succesfuly, do a refresh (F5).");	
				
			}
			
			//note.: from default page it is impossible to directly connect to tabs
		}


		if($spinac == 2){
			//case after user creation
			pokusSeVytvoritUzivatele();
		}

		if($spinac == 3){

			pokusSePrihlasitUzivatele();
		}

		if($spinac == 4){
			//case after first user successful creation
			prejdiNaVytvoreniUzvivatele();
		}
		
		if($spinac == 5){
			//case logoff
			
			session_unset(); 
			session_destroy(); 
			
			
			//readfile("templateDALSIuzivatel.html");
			//this was wrong construction see problemChromeEdge.bmp
			readfile("index.html");	
		}
		
		if($spinac == 6){
			//case delete image
			zkusSmazatObrazek();	
		}

		if($spinac == 7){
			//case load image
			zkusNahratObrazek();	
		}
	
	
	}

	function urciStavDB(){
	/*
	Attempts to find out the state of DB on the server. 
	returns
	0 if no connection exists or a fatal error has occured
	1 if connects to the server (only)
	2 if connects to the DB but its empty (tables without any data)
	3 if connects to the DB and it is not empty (at least 1 user)
	4 if connects and the DB exists but required tables do not
	*/	
 
	$vratit = 0;

	try{
		error_reporting(0);
		$pripojeniBezDB = new mysqli(servername, username, password);
		error_reporting(1);
		if ($pripojeniBezDB->connect_error) {
		throw new Exception("Connection to server failed"); }
		
	$vratit = 1;
	} 	
	catch(Exception $e) {
		$vratit = 0;		
	} 

	
	try{
		$pripojeni = new mysqli(servername, username, password, databasename);
		if ($pripojeniBezDB->connect_error) {
		throw new Exception("Connection to server failed"); }
		
	$vratit = 2;
	} 	
	catch(Exception $e) {
		$vratit = 1;		
	} 
	
	$tabulkyExistuji = urciExistenciTabulekVDB();
	
	if($tabulkyExistuji == false){
		$vratit =4;	
		
	}
	
	
	
	
	
	$uzivatelExistuje = False;
	
	if($vratit == 2){
		$uzivatelExistuje = urciExistenciUzivateleVDB();
		if($uzivatelExistuje == true){
			$vratit = 3;
		} else {
			
		}
			
	}

	return $vratit;
		
	}
	
	function urciExistenciUzivateleVDB(){
	/*
	returns true if in table "uzivatele" is at least 1 entry, otherwise false
	*/
		$pripojeni = new mysqli(servername, username, password, databasename);
		
		$dotaz = "SELECT COUNT(*) FROM tabUzivatele;";	

		$vysledek = mysqli_query($pripojeni,$dotaz);
		
		$pocetUzivatelu = 0;
		
		if($vysledek == false){
			echo "Query for number of users over database failed = in DB is not the table required.";
		}
		else{
			try{			
				while($radek =$vysledek->fetch_array()){		
							$pocetUzivatelu = $radek["COUNT(*)"]; 
						}
			}
			
			catch(Exception $e){
				$message = $e->getMessage();
				echo "ERROR: $message";
								}
		    }
		$vratit = false;
		
		if($pocetUzivatelu > 0){
			$vratit = true;
		}
		
		return $vratit;
			

	}

	
	function urciExistenciTabulekVDB(){
	/*
		Returns true if in the DB exist tables "tabObrazky" and "tabUzivatele", otherwise false.
	*/
		$tabulkyOK = false;
		
		$pripojeni = new mysqli(servername, username, password, databasename);
		
		$dotaz = "SELECT * FROM tabUzivatele LIMIT 1;";	
		
		//$dotaz =  "SHOW TABLES LIKE 'tabUzivatele'"; 

		$tabUzivateleOK = mysqli_query($pripojeni,$dotaz);
		
		if($tabUzivateleOK == false){
			echo "ERROR: In DB does not exist table tabUzivatele.";
			echo "<br>";
		}	
		
		$dotaz = "SELECT 1 FROM tabObrazky LIMIT 1;";
		//$dotaz =  "SHOW TABLES LIKE 'tabObrazky'"; 
		
		$tabObrazkyOK = mysqli_query($pripojeni,$dotaz);
		
		if($tabObrazkyOK == false){
			echo "ERROR: In DB does not exist table tabObrazky.";
			echo "<br>";
		}
		
		if(($tabObrazkyOK != false) and ($tabUzivateleOK != false)){
			$tabulkyOK = true;	
		}
		return $tabulkyOK;
		
		
	}
	
	
	function pokusSeVytvoritUzivatele(){
	/*
		Attempts to create user. Input data are taken from z $_POST variables:
		"jmenoUzivatele", "heslo", "hesloPotvrzeni"
		Method verifies that they are filled out, and that strings "heslo" (password) and "hesloPotvrzeni" (repeat password) are equal.
		Subsequently, the method loads all current existing users (using object uzivatelDB) and through method jmenoJeVPoliUzivatelu
		checks for existence of user with a given name. If it exists, creation is stopped and a message showed.
		If everything is in order, method saves the new user into the DB (using object uzivatelDB). 
		
	*/
		$jmenoNovehoUzivatele = $_POST["jmenoUzivatele"];
		$hesloNovehoUzivatele = $_POST["heslo"];
		$hesloPotvrzeni = $_POST["hesloPotvrzeni"];
		
		$problemNastal = false;
		$zprava = "";
		
		if($jmenoNovehoUzivatele == ''){
				$zprava="Fill in username please";
				$problemNastal = true;
			
		}
		
		if($hesloNovehoUzivatele == ''){
				$zprava="Fill in password please";
				$problemNastal = true;
			
		}

		if($hesloNovehoUzivatele != $hesloPotvrzeni){
			$zprava="Given passwords are not the same.";
			$problemNastal = true;
		}
		
		$pripojeni = new mysqli(servername, username, password, databasename);
		$DBObjekt = new uzivatelDB($pripojeni);
		$uzivatele = $DBObjekt->nahrajUzivatele();
		$jmenoJizZabrano = jmenoJeVPoliUzivatelu($jmenoNovehoUzivatele,$uzivatele);
		
		if($jmenoJizZabrano == true){
			$zprava="Given name is already used. Please fill in a different one.";
			$problemNastal = true;
		}
		
		
		
		if($problemNastal == true){
			echo "<script type='text/javascript'>alert('$zprava');</script>";
			readfile("templatePRVNIuzivatel.html");	
		}
		else{
			//case vytvorit uzivatele a prihlasit se za nej
			$uzivatelNovy = new uzivatelObjekt($jmenoNovehoUzivatele,$hesloNovehoUzivatele);
			
			
			$DBObjekt->saveUzivatel($uzivatelNovy);
			mysqli_close($pripojeni);
			
			pokusSePrihlasitUzivatele();

		}
	
		
		

	
		
	}
	
	function jmenoAHesloJeVPoliUzivatelu($jmenoHledane,$hesloHledane,$poleUzivatelu){
	/*
		Returns true, if in $poleUzivatelu exists object uzivatelObjekt with properties 
		jmeno a heslo equal to  input parameters:
		$jmenoHledane = string
		$hesloHledane = string
		$poleUzivatelu  = array of objects uzivatelObjekt  
	*/
		$jmenoAHesloNalezeno = false;

		foreach($poleUzivatelu as $uzivatel ){
			
			//echo $uzivatel->jmeno . " " . $uzivatel->heslo . " VS " . $jmenoHledane ." ". $hesloHledane;
			
			
			if(($uzivatel->jmeno == $jmenoHledane) AND ($uzivatel->heslo == $hesloHledane)){
				//echo"UZIVATEL NALEZEN";
				$jmenoAHesloNalezeno = true;
				break;
			}
			
		}
		
		return $jmenoAHesloNalezeno;
		
		
	}
	
		function jmenoJeVPoliUzivatelu($jmenoHledane,$poleUzivatelu){
		/*
			Returns true, if in $poleUzivatelu exists object uzivatelObjekt   with property 
			jmeno equal to input parameter.
			$jmenoHledane = string
			$poleUzivatelu  = array of objects uzivatelObjekt  
		*/
		$jmenoNalezeno = false;

		foreach($poleUzivatelu as $uzivatel ){
			
			//echo $uzivatel->jmeno . " " . $uzivatel->heslo . " VS " . $jmenoHledane ." ". $hesloHledane;
			
			
			if($uzivatel->jmeno == $jmenoHledane){
				//echo"UZIVATEL NALEZEN";
				$jmenoNalezeno = true;
				break;
			}
			
		}
		
		return $jmenoNalezeno;
		
		
	}
	
	
	
	function najdiIDUzivateleVPoliUzivatelu($jmenoHledane,$poleUzivatelu){
		/*
			Attempts to find uzivatelObjekt in array $poleUzivatelu and return its property 
			uzivatelID. If it does not find it, returns 0.
			$jmenoHledane = retezec
			$poleUzivatelu  = array of objects uzivatelObjekt  
			
		*/	
		$nalezeneID = 0;

		foreach($poleUzivatelu as $uzivatel ){
			
						
			
			if($uzivatel->jmeno == $jmenoHledane){
				//echo"UZIVATEL NALEZEN";
				$nalezeneID = $uzivatel->uzivatelID;
				break;
			}
			
		}
		
		return $nalezeneID;
		
		
	}
	
	
	
	function pokusSePrihlasitUzivatele(){
	/*
		Recieves variables from $_POST
		"jmenoUzivatele", "heslo" 
		loads user using uzivatelDB->nahrajUzivatele() and verifies legality of user by method jmenoAHesloJeVPoliUzivatelu.
		If user is in array of existing users, calls method prihlasUzivatele, if it is not shows a message and returns to login.
	*/
				
		$jmenoNovehoUzivatele = $_POST["jmenoUzivatele"];
		$hesloNovehoUzivatele = $_POST["heslo"];

		$pripojeni = new mysqli(servername, username, password, databasename);
		$DBObjekt = new uzivatelDB($pripojeni);
		$poleUzivatelu = $DBObjekt->nahrajUzivatele();
		
		$uzivatelPlatny = jmenoAHesloJeVPoliUzivatelu($jmenoNovehoUzivatele,$hesloNovehoUzivatele,$poleUzivatelu);
		
		
		if($uzivatelPlatny){
				//readfile("templateTABULKA.html");	
				
				$idUzvatele = najdiIDUzivateleVPoliUzivatelu($jmenoNovehoUzivatele,$poleUzivatelu);
				
				
				prihlasUzivatele($jmenoNovehoUzivatele,$idUzvatele,$pripojeni);
		}
		else{
			
			//readfile("templateDALSIuzivatel.html");	
			//chybna konstrukce viz problemChromeEdge.bmp
			readfile("index.html");	
			
			
			$zprava = "Invalid user";
			echo "<script type='text/javascript'>alert('$zprava');</script>";
		}
		
	}
	
	function setInnerHTML($element, $html){
		/*
		Method for easy inserting of text into element - used only for display of 
		currently logged in user. 
		adopted from:
		https://stackoverflow.com/questions/2778110/change-innerhtml-of-a-php-domelement

		unlike in javascript in php innerhtml cannot be set - using Child nodes
		is needed (here DocumentFragment)
		
		*/
	
    $fragment = $element->ownerDocument->createDocumentFragment();
    $fragment->appendXML($html);
    while ($element->hasChildNodes())
        $element->removeChild($element->firstChild);
    $element->appendChild($fragment);
	}
	
	function getInnerHTML($element){
		/*
		Method to acquire text of an element, created by reverse engineering from method setInnerHTML.		
		*/
		$html = '';
		$doc = $element->ownerDocument;
		
		foreach ($element->childNodes as $node) {
			$html .= $doc->saveHTML($node);
		}
		
		return $html;
	}
	
	
	function prihlasUzivatele($jmenoNovehoUzivatele, $idUzivatele,$pripojeni){
		/*
			Method for user log in, AFTER CONTROL OF ENTRY DATA. 
			Begins a session and fills "userID" and "userName". 
			Then it loads a page for displaying of content ("templateTABULKA.html")
			and sets into given element name of logged in user.			
			After that the method loads content using 'LoadTable.php' (returns DOM element) 
			and puts the content into corresponding element. 
			At last prints the result using echo.
		
		*/
		
		session_start();
		$_SESSION["userID"] = $idUzivatele;
		$_SESSION["userName"] = $jmenoNovehoUzivatele;
		
	
			
		$dom = new DOMDocument();
		$dom -> loadHTMLFile("templateTABULKA.html");

		
		$uzivatelPole = $dom->getElementById('uzviatelZobraz');
		setInnerHTML($uzivatelPole,$jmenoNovehoUzivatele);
		//careful - useful only for simple text, it is better to use DOM object (like in loadTable.php)
		
		$naseptavac = $dom->getElementById('zadavac');		
		$naseptavacText = getInnerHTML($naseptavac);
		
		//echo "<script type='text/javascript'>alert('$naseptavacText');</script>";
		
		
		
		
		$tabulkaObrazkuPole = $dom->getElementById('tabulkaZde');

		$tabulkaObrazku = include 'LoadTable.php';
		//$tabulkaObrazku je DOM ELEMENT
		
		$tabulkaObrazkuPole->appendChild($tabulkaObrazku);
		
		echo $dom->saveHTML();	
	}
	
	function prejdiNaVytvoreniUzvivatele(){
	/*
		Method for transfer to page "templatePRVNIuzivatel.html" and 
		emptying of its one element.
	*/
	
		$dom = new DOMDocument();
		$dom -> loadHTMLFile("templatePRVNIuzivatel.html");
		
		$vyprazdnit = $dom->getElementById('prvniUzivatelZobrazovac');
		$prazdno = "";
		
		setInnerHTML($vyprazdnit,$prazdno);
		
		
		echo $dom->saveHTML();
		//readfile("templatePRVNIuzivatel.html");	
	}
	
	function zkusSmazatObrazek(){
	/*
		Takes 
		$_POST["IDMazanehoObrazku"]
		and 
		$_SESSION["userID"] $_SESSION["userName"]
		Loads all images from DB and cycles through them. If an image obrazekObjekt with obrazekID = IDMazanehoObrazku is found,
		the method determines it as the deleted one. If it is null, shows a message. 
		Also checks, that its property uzivatelID = userID ($_SESSION["userID"]  = active user), if it does, 
		method deletes the entry in DB and corresponding file in folder "obrazky". If it does not, shows a message.
		
		
	*/

		$idMazanehoObrazku = $_POST["IDMazanehoObrazku"];
		session_start();	
		$idAktivnihoUzivatele = $_SESSION["userID"];
		$jmenoAktivnihoUzivatele = $_SESSION["userName"];
		
		$pripojeni = new mysqli(servername, username, password, databasename);
		$DBObjekt = new obrazekObjektDB($pripojeni);
		$obrazekVDB = $DBObjekt->nahrajObrazekByID($idMazanehoObrazku);
		
		if(obrazekVDB == null){
			$zprava = "Error: image with ID: ".$idMazanehoObrazku. " was not loaded from DB";
			echo $zprava;	
		}
		else {
			if(($obrazekVDB -> uzivatelID) == $idAktivnihoUzivatele){
				$DBObjekt->smazObrazekByID($idMazanehoObrazku);
				
				$filepath = imageFolder."/".$obrazekVDB->filePath;

				unlink($filepath);
			}
			else{
				//case obrazek nenahral aktivni uzivatel
				$zprava = "Image was not uploaded by current user. Only the user that uploaded the image can delete it.";
				echo $zprava;	
			}
			
		}
		
		
		//MARKER SMAZAT
		
		//echo $jmenoAktivnihoUzivatele . " " . $idAktivnihoUzivatele . " " . $idMazanehoObrazku;
		//print_r($_SESSION);
			
	}
	
	function zkusNahratObrazek(){
	/*
		Method for uploading images to server. 
		Takes data about the current user 
		$_SESSION["userName"] $_SESSION["userID"]
		and checks existence of 
		$_FILES["obrazekVstup"]["tmp_name"]
		if it does not exist, shows script for a message. If it does, creates new image ObrazekObjekt. 
		This new image is compared with other already uploaded images and if  an image with the same name is found,
		shows a message. If not, saves the entry into DB and the file into folder "obrazky". 
		If everything runs OK, logs in current user into the page with data and switches to tab with content
		using echo javaScript.
	
	*/
		
		$zprava = "No file selected!";
		
		session_start();

		$pripojeni = new mysqli(servername, username, password, databasename);
		  $DBObjekt = new obrazekObjektDB($pripojeni);
		  
		  $jmenoAktivnihoUzivatele= $_SESSION["userName"];
		  $IDAktivnihoUzivatele=$_SESSION["userID"];
			
			
			//overeni existence vybraneho souboru			
			if(file_exists($_FILES["obrazekVstup"]["tmp_name"]) == false){				
				//echo "KONEC";
				//return;	
				$zprava = "No file selected!";
				prihlasUzivatele($jmenoAktivnihoUzivatele, $IDAktivnihoUzivatele,$pripojeni);
								
				echo "<script type='text/javascript'>
				prepniTab(event, 'tabObsah2');		  
				</script>";
				$retezec =  "<script type='text/javascript'> alert('" . $zprava . "'); </script>";
				echo $retezec;	
				return;		
				
			}

		  
		  
		  $jmenoObrazku = $_FILES["obrazekVstup"]["name"];
		  $obrazek = new obrazekObjekt(0, $jmenoObrazku,$IDAktivnihoUzivatele,$jmenoAktivnihoUzivatele); //ID je auto-increment
		  
		  //overeni existence obrazku s identickym nazvem -> je nutne jej predtim smazat 
		  $obrazky = $DBObjekt->nahrajObrazky();		  
		  foreach($obrazky as $prochazeny ){
			if( $prochazeny->filePath == $obrazek ->filePath) {
				//echo "KONEC";
				//return;	
				$zprava = "Picture with same name already uploaded.";
				prihlasUzivatele($jmenoAktivnihoUzivatele, $IDAktivnihoUzivatele,$pripojeni);
								
				echo "<script type='text/javascript'>
				prepniTab(event, 'tabObsah2');		  
				</script>";
				$retezec =  "<script type='text/javascript'> alert('" . $zprava . "'); </script>";
				echo $retezec;	
				return;		
			}
			  
		  }
		  
		  
		  $DBObjekt -> saveObrazek($obrazek);	
		
		
		
		
		
		
		//echo "START <br>";

		if ($_FILES["obrazekVstup"]["error"] > 0)
		  {
		  //echo "Error: " . $_FILES["obrazekVstup"]["error"] . "<br />";
		  $zprava = "Error: " . $_FILES["obrazekVstup"]["error"] . "<br />";
		  }
		else
		  {
			
			/*
			echo "Upload: " . $_FILES["obrazekVstup"]["name"] . "<br />";
			echo "Type: " . $_FILES["obrazekVstup"]["type"] . "<br />";
			echo "Size: " . ($_FILES["obrazekVstup"]["size"] / 1024) . " Kb<br />";
			echo "Stored in: " . $_FILES["obrazekVstup"]["tmp_name"]. "<br />";		  
			echo "Uploaded by: " . $_SESSION["userName"]. "<br />";
			echo "Uploaded by user ID: " . $_SESSION["userID"]. "<br />";
			*/
		
		  
		  $target_dir = "obrazky/";
		  $target_file = $target_dir . basename($_FILES["obrazekVstup"]["name"]);
		  $prubehOK= move_uploaded_file($_FILES["obrazekVstup"]["tmp_name"], $target_file);
		  
		  if($prubehOK == true){
			//echo "Nahráno do /obrázky";
			$zprava = "Image uploaded";
			
			
			
		  }
		  
		  }
		  
		  			
		  
		  prihlasUzivatele($jmenoAktivnihoUzivatele, $IDAktivnihoUzivatele,$pripojeni);
		  
		  echo "<script type='text/javascript'>
		  prepniTab(event, 'tabObsah1');		  
		  </script>";
		  
		  
		  $retezec =  "<script type='text/javascript'> alert('" . $zprava . "'); </script>";
		  
		   //echo "<script type='text/javascript'> alert('HAHA'); </script>";
		  echo $retezec;
		  
	}
?>