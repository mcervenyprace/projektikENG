<?php

/*
	php module where constants and classes for project projektikENG are defined
	constants define connection to server and username and password for DB
*/	



	define("servername", "localhost");
	define("username", "martin");
	define("password", "martin");
	define("databasename", "projektikdb");
	define("imageFolder","obrazky");

class uzivatelObjekt{
/*
	class representing users
*/
		
		public $jmeno;
		public $heslo;
		public $uzivatelID;
		
		public function __construct($jmeno, $heslo) {
			$this -> jmeno = $jmeno;
			$this -> heslo = $heslo;
		}
	}
	
class uzivatelDB{
/*
	class for working with table "tabUzivatele" in the DB, uploaded data transform into objects uzivatelObjekt
	$pripojeni - mysqliconnection, existence for fucntion of this class is necessary
*/
	public $pripojeni;
	
	public function __construct($pripojeniNew) {
		$this -> pripojeni = $pripojeniNew;
	}
	
	public function saveUzivatel($uzivatel){
	/*
		method for insertion of user, DOES NOT CHECK ANYTHING
	*/
		$jmeno = $uzivatel -> jmeno;
		$heslo = $uzivatel -> heslo;
		
		
		$dotaz = "INSERT INTO `tabUzivatele` (`uzivatelJmeno`, `uzivatelHeslo`) VALUES ( '".$jmeno."', '".$heslo."');";
		
		//echo " ".$dotaz." ";
		
		$prubehOK = mysqli_query($this -> pripojeni,$dotaz);
		
			if($prubehOK == true){
				//nedelat nic
			}
		else{
			echo("Pri vkladani dat nastala chyba!" . mysqli_error($this->pripojeni) );		
		}	
	
	}
	
	public function nahrajUzivatele(){			
		/*
			returns array of objects uzivatelObjekt loaded from DB table tabUzivatele (no select)
			always returns at least empty array
		*/			
			$dotaz = "SELECT uzivatelID,uzivatelJmeno,uzivatelHeslo FROM `tabUzivatele` WHERE 1 ";
			
						
			$vysledek = mysqli_query($this -> pripojeni,$dotaz);
			$vratit = array();
			
			if($vysledek == false){
				echo("Pri nahravani dat nastala chyba!" . mysqli_error($this->pripojeni) );
			}
		else{
			//case result is mysqli_result
			$pole = $vysledek->fetch_all(MYSQLI_ASSOC);
			foreach($pole as $radek ){
				
				/*
				foreach($radek as $x => $x_value) {
					echo "Key=" . $x . ", Value=" . $x_value;
					echo "<br>";
				}
				*/

				$jmeno = $radek["uzivatelJmeno"];
				$heslo = $radek["uzivatelHeslo"];
				
				$uzivatel = new uzivatelObjekt($jmeno,$heslo);
				
				$uzivatel->uzivatelID = $radek["uzivatelID"];
				
				array_push($vratit,$uzivatel); 
				
				
			}
		}	
		return	$vratit;		
		
	}

}

class obrazekObjekt{
/*
	object for working with objects representing file-  image, an entry in table "tabObrazky"
	
	
	public $obrazekID; - ID from tabObrazky
	public $filePath; - filename (located in folder "obrazky")
	public $uzivatelID; - ID of user that uploaded the image, FK into tabUzivatele
	public $jmenoUzivateleCoNahral;	- name of user that uploaded the image (from tabUzivatele)
	
*/
	public $obrazekID;
	public $filePath;
	public $uzivatelID;
	public $jmenoUzivateleCoNahral;	
	
	public function __construct($obrazekID, $filePath,$uzivatelID,$jmenoUzivateleCoNahral) {
		$this -> obrazekID = $obrazekID;
		$this -> filePath = $filePath;
		$this -> uzivatelID = $uzivatelID;
		$this -> jmenoUzivateleCoNahral = $jmenoUzivateleCoNahral;
	}
}

class obrazekObjektDB{
/*
	class for operating with table "tabObrazky" in the DB,transforms data into object obrazekObjekt
	$pripojeni - mysqliconnection, existence necessary for this class
*/

	public $pripojeni;
	
	public function __construct($pripojeniNew) {
		$this -> pripojeni = $pripojeniNew;
	}
	
	public function saveObrazek($obrazek){
	/*
		method for insertion of object $obrazek into table tabObrazky, DOES NOT DO ANY CHECKING
	*/
		$filePath = $obrazek -> filePath;
		$uzivatelID = $obrazek -> uzivatelID;
		
		
		$dotaz = "INSERT INTO `tabObrazky` (`filepath`, `uzivatelID`) VALUES ( '".$filePath."', '".$uzivatelID."');";
		
		//echo " ".$dotaz." ";
		
		$prubehOK = mysqli_query($this -> pripojeni,$dotaz);
		
			if($prubehOK == true){
				//dont do anything
			}
		else{
			echo("During data upload an error occured!" . mysqli_error($this->pripojeni) );		
		}	
	
	}


	
	public function nahrajObrazky(){			
		/*
		Retruns array of objects obrazekObjekt loaded from DB table "tabObrazky" (no select)
		always returns at least empty array
		*/			
			$dotaz = "SELECT obrazekID,filepath,tabObrazky.uzivatelID,uzivatelJmeno FROM tabObrazky 
					JOIN tabUzivatele ON tabUzivatele.uzivatelID = tabObrazky.uzivatelID WHERE 1 ";
			
				//echo "X ".$dotaz ." X";
			
			$vysledek = mysqli_query($this -> pripojeni,$dotaz);
			$vratit = array();
			
			if($vysledek == false){
				echo("During data upload an error occured!" . mysqli_error($this->pripojeni) );
			}
		else{
			//case result is mysqli_result
			$pole = $vysledek->fetch_all(MYSQLI_ASSOC);
			foreach($pole as $radek ){
				
				/*
				foreach($radek as $x => $x_value) {
					echo "Key=" . $x . ", Value=" . $x_value;
					echo "<br>";
				}
				*/

				$obrazekID = $radek["obrazekID"];
				$filePath = $radek["filepath"];
				$uzivatelID = $radek["uzivatelID"];
				$jmenoUzivateleCoNahral = $radek["uzivatelJmeno"];	
				
				/*
				echo $obrazekID;
				echo $filePath;
				echo $uzivatelID;
				*/
				//echo '*'.$jmenoUzivateleCoNahral.'*';
				
				
				$obrazek = new obrazekObjekt($obrazekID, $filePath,$uzivatelID,$jmenoUzivateleCoNahral);
				
				//echo $obrazek->filePath;
				//echo $obrazek->jmenoUzivateleCoNahral;
				
				array_push($vratit,$obrazek); 
				
				
			}
		}	
		return	$vratit;		
		
	}
	
	



	public function nahrajObrazkyVyhledavac($textHledani){			
		/*
		returns array of objects obrazekObjekt loaded from DB table "tabObrazky" - by string $textHledani, which must be in column "filepath" (ILIKE select)
		always returns at least empty array
		*/			
			$dotaz = "SELECT obrazekID,filepath,tabObrazky.uzivatelID,uzivatelJmeno FROM tabObrazky 
					JOIN tabUzivatele ON tabUzivatele.uzivatelID = tabObrazky.uzivatelID WHERE filepath LIKE ".'"%' . $textHledani.'%"';
			
				//echo "X ".$dotaz ." X";
			
			$vysledek = mysqli_query($this -> pripojeni,$dotaz);
			$vratit = array();
			
			if($vysledek == false){
				echo("During data upload an error occured!" . mysqli_error($this->pripojeni) );
			}
		else{
			//case result is mysqli_result
			$pole = $vysledek->fetch_all(MYSQLI_ASSOC);
			foreach($pole as $radek ){
				
				/*
				foreach($radek as $x => $x_value) {
					echo "Key=" . $x . ", Value=" . $x_value;
					echo "<br>";
				}
				*/

				$obrazekID = $radek["obrazekID"];
				$filePath = $radek["filepath"];
				$uzivatelID = $radek["uzivatelID"];
				$jmenoUzivateleCoNahral = $radek["uzivatelJmeno"];	
				
				/*
				echo $obrazekID;
				echo $filePath;
				echo $uzivatelID;
				*/
				//echo '*'.$jmenoUzivateleCoNahral.'*';
				
				
				$obrazek = new obrazekObjekt($obrazekID, $filePath,$uzivatelID,$jmenoUzivateleCoNahral);
				
				//echo $obrazek->filePath;
				//echo $obrazek->jmenoUzivateleCoNahral;
				
				array_push($vratit,$obrazek); 
				
				
			}
		}	
		return	$vratit;		
		
	}
	
	public function nahrajObrazekByID($obrazekID){
	/*
		function that loads one image from "tabObrazky" by $obrazekID, if the load fails returns 
		null
	*/
		$dotaz = "SELECT obrazekID,filepath,tabObrazky.uzivatelID,uzivatelJmeno FROM tabObrazky 
					JOIN tabUzivatele ON tabUzivatele.uzivatelID = tabObrazky.uzivatelID WHERE obrazekID = ". $obrazekID;
			
				//echo "X ".$dotaz ." X";
			$vratit = null;
			
			$vysledek = mysqli_query($this -> pripojeni,$dotaz);
			if($vysledek == false){
				echo("During loading of data an error occured!" . mysqli_error($this->pripojeni) );
			}
			else{
				//case vysledek je mysqli_result
				$pole = $vysledek->fetch_all(MYSQLI_ASSOC);
				foreach($pole as $radek ){
			
					$obrazekID = $radek["obrazekID"];
					$filePath = $radek["filepath"];
					$uzivatelID = $radek["uzivatelID"];
					$jmenoUzivateleCoNahral = $radek["uzivatelJmeno"];	
					
										
					$obrazek = new obrazekObjekt($obrazekID, $filePath,$uzivatelID,$jmenoUzivateleCoNahral);
			
					$vratit = $obrazek;
					
					
				}
			}	
		
		return $vratit;
	}	
	
	public function smazObrazekByID($obrazekID){
	/*
		attempts to delete an entry from "tabObrazky" by "obrazekID", returns TRUE if successful, FALSE if not
	*/
		$vratit = false;
		$dotaz = "DELETE FROM tabObrazky WHERE obrazekID = ". $obrazekID;
		$vysledek = mysqli_query($this -> pripojeni,$dotaz);
			if($vysledek == false){
				echo("During deletion of data an error occured!" . mysqli_error($this->pripojeni) );
			}
			else{
				$vratit = true;
			}
		return $vratit;	
	}
}





	
?>