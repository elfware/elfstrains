<?php




// CHECK IF USER EXISTS
function user_exists($user = NULL, $field = "id"){
	global $dbh;

	// Prepare PDO statement
	if($field == "id"){
		$stmt = $dbh->prepare("SELECT * FROM users WHERE id = :user");
	}
	elseif ($field == "username") {
		$stmt = $dbh->prepare("SELECT * FROM users WHERE username = :user");
	}
	elseif ($field == "signature") {
		$stmt = $dbh->prepare("SELECT * FROM users WHERE signature = :user");
	}

	// Bind parameter
	$stmt->bindParam(":user", $user);

	// Execute statement
	$stmt->execute();

	// Check how many rows were found
	$rows_found = $stmt->rowCount();

	// Return based on result
	if($rows_found > 0){
		return TRUE;
	} else {
		return FALSE;
	}
}

// CHECK IF STRAIN EXISTS
function strain_exists($strain = NULL){
	global $dbh;

	if($strain !== NULL && is_numeric($strain)){
		// Prepare PDO statement
		$stmt = $dbh->prepare("SELECT * FROM stock_tube WHERE id = :strain_id");

		// Bind parameter
		$stmt->bindParam(":strain_id", $strain);

		// Execute statement
		$stmt->execute();

		// Check how many rows were found
		$rows_found = $stmt->rowCount();

		// Return based on result
		if($rows_found > 0){
			return TRUE;
		} else {
			return FALSE;
		}
	} else {
		return FALSE;
	}
}

// CONVERT GREEK CHARACTERS
function convert_greek_characters($text){
	$pattern = array('/del/','/lambda/');
	$replacement = array('Δ','λ');
	$outtext = preg_replace($pattern,$replacement,$text,1,$count);
	if ($count == 0) {
		$pattern = array('/Δ/','/λ/');
		$replacement = array('del','lambda');
		$outtext = preg_replace($pattern,$replacement,$outtext,1);
	}
	return $outtext;
}
