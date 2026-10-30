<?php

$_POST['form-type'] = $_POST['form-type'] ?? '';
// DO LOGIN
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['form-type'] == 'login'){

	$uname = $_POST['username'];
	$pword = $_POST['password'];

	// Prepare PDO statement
	$stmt = $dbh->prepare("SELECT * FROM users WHERE username = :uname AND password = :pword");
	$stmtCount = $dbh->prepare("SELECT COUNT(*) FROM users WHERE username = :uname AND password = :pword");

	// Bind parameters
	$pwordmd5 = md5($pword);
	$stmt->bindParam(":uname", $uname);
	$stmt->bindParam(":pword", $pwordmd5);
	$stmtCount->bindParam(":uname", $uname);
	$stmtCount->bindParam(":pword", $pwordmd5);

	// Execute the statements
	$stmt->execute();
	$stmtCount->execute();

	// Cast the fetched count-column as an integaer
	$count = (int) $stmtCount->fetchColumn();

	if($count > 0){
		// Store the fetched row in a user variable
		$user = $stmt->fetch(PDO::FETCH_ASSOC);

		// Store session as logged in
		$_SESSION['login'] = "1";

		// Store user details in session
		$_SESSION['user'] = $user['username'];
		$_SESSION['Usertype'] = $user['usertype'];
		$_SESSION['Signature'] = $user['signature'];
		// Set max session duration for 8 hours
		$_SESSION['timeout'] = time();
		//$_SESSION['expire'] = $_SESSION['start'] + (8*60*60);

		// Close database cursors
		$stmt->closeCursor();
		$stmtCount->closeCursor();

	} else {
		$_SESSION['login'] = "";

		$errorMessage = "Error logging on";

		// Close database cursors
		$stmt->closeCursor();
		$stmtCount->closeCursor();
	}
}

// DO SEARCH
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['form-type'] == 'search'){

	// Search button has been pressed
	if(($_POST['search-text'] ?? '') == 'Search' || ($_POST['search-list'] ?? '') == 'Search'){
		$_SESSION['sign1'] = $_POST['sign1'];
		$_SESSION['status1'] = $_POST['status1'];

		// Search is text
		if(($_POST['search-text'] ?? '') == 'Search') {
			$_SESSION['term1'] = $_POST['term1'];
			$_SESSION['term2'] = $_POST['term2'];
			$_SESSION['term3'] = $_POST['term3'];
			$_SESSION['term4'] = $_POST['term4'];
			$_SESSION['notterm1'] = $_POST['notterm1'];
			$_SESSION['notterm2'] = $_POST['notterm2'];
			$_SESSION['notterm3'] = $_POST['notterm3'];
			$_SESSION['notterm4'] = $_POST['notterm4'];
			$_SESSION['genotype'] = $_POST['check']['genotype'] ?? '';
			$_SESSION['comment'] = $_POST['check']['comment'] ?? '';
			if(!empty($_POST['check']['genotype'])){
				$_SESSION['searchgenotype'] = 1;
			} else {
				$_SESSION['searchgenotype'] = 0;
			}
			if(!empty($_POST['check']['comment'])){
				$_SESSION['searchcomment'] = 1;
			} else {
				$_SESSION['searchcomment'] = 0;
			}

			// If all search boxes are empty, redirect to error
			if (empty($_POST['term1']) &&
				empty($_POST['term2']) &&
				empty($_POST['term3']) &&
				empty($_POST['term4']) &&
				empty($_POST['sign1']) &&
				empty($_POST['status1'])
			) {
				header("Location: index.php?mode=search&error=error");
				exit;
			}
		}

		// Search is number/list
		if(($_POST['search-list'] ?? '') == 'Search') {
			$_SESSION['minNum'] = $_POST['minNum'];
			$_SESSION['maxNum'] = $_POST['maxNum'];
		}
	}

	// The reset button clears all submitted variables from all forms
	if(($_POST['reset-list'] ?? '') == 'Reset' OR ($_POST['reset-word'] ?? '') == 'Reset'){
		//Search:
		$_SESSION['total_records'] = '';
		$_SESSION['word'] = '';
		$_SESSION['num'] = '';
		$_SESSION['check'] = '';
		$_SESSION['genotype'] = '';
		$_SESSION['comment'] = '';
		$_SESSION['term1'] = '';
		$_SESSION['term1'] = '';
		$_SESSION['term3'] = '';
		$_SESSION['term2'] = '';
		$_SESSION['term4'] = '';
		$_SESSION['notterm1'] = '';
		$_SESSION['notterm2'] = '';
		$_SESSION['notterm3'] = '';
		$_SESSION['notterm4'] = '';
		$_SESSION['commentterm1'] = '';
		$_SESSION['commentterm1'] = '';
		$_SESSION['commentterm3'] = '';
		$_SESSION['commentterm2'] = '';
		$_SESSION['commentterm4'] = '';
		$_SESSION['sign1'] = '';
		$_SESSION['status1'] = '';
		$_SESSION['minNum'] = '';
		$_SESSION['maxNum'] = '';

		if(($_POST['reset-list'] ?? '') == 'Reset'){
			header("Location: index.php?mode=search&type=number");
			exit;
		}
		elseif(($_POST['reset-word'] ?? '') == 'Reset'){
			header("Location: index.php?mode=search&type=word");
			exit;
		}
	}

}

// DO EDIT STRAINS
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['form-type'] == 'edit'){
	// Check user rights
	if(in_array($_SESSION['Usertype'], array('Superuser','Admin'))) {
		// Find out how many records there are to update
		$size = count($_POST['Genotype']);
		//echo ' size: ' . $size;
		// Begin database transaction
		$dbh->beginTransaction();

		// Create SQL query
		$sql = "INSERT INTO strain_information (tube_id, genotype, donor, recipient, comment, labnotes_id, user_id)
					SELECT :strain, :genotype, :donor, :recipient, :comment, :labnote, id as user_id
					FROM users
					WHERE users.username = :username";

		// Prepare statement
		$stmt = $dbh->prepare($sql);

		// Bind parameters
		$stmt->bindParam(":strain", $strain);
		$stmt->bindParam(":genotype", $genotype);
		$stmt->bindParam(":donor", $donor);
		$stmt->bindParam(":recipient", $recipient);
		$stmt->bindParam(":comment", $comment);
		$stmt->bindParam(":labnote", $labnote);
		$stmt->bindParam(":username", $_SESSION['user']);

		for($i = 0; $i < $size; $i++){

			// Define variables
			$genotype  = str_replace(array("\r", "\r\n", "\n"), " ", $_POST['Genotype'][$i]);
			$recipient = intval($_POST["Recipient"][$i]);
			if ($recipient == 0) {
				$recipient = null;
			}
			$donor = intval($_POST["Donor"][$i]);
			if ($donor == 0) {
				$donor = null;
			}
			$comment   = str_replace(array("\r", "\r\n", "\n"), " ", $_POST['Comment'][$i]);
			$strain    = $_POST['Strain'][$i];
			//$labnote   = $_POST['Labnote'][$i];
			$labnote   = trim($_POST['Labnote'][$i]);
			if (preg_match('/EXP-\d{2}-[A-Z0-9]+/i', $labnote, $m)) {
				$labnote = strtoupper($m[0]);
			}


			$selected[$i] = $strain;

			// Execute statement
			$stmt->execute();
			//$stmt->debugDumpParams();


			// Check error codes
			if($stmt->errorCode() === '00000'){
				$success = TRUE;
			} else {
				$success = FALSE;
				break;
			}
		}

		// Commit if all were successful
		unset($transaction_fail);
		if($success){
			$dbh->commit();
			$transaction_fail = FALSE;
		} else {
			$dbh->rollBack();
			$transaction_fail = TRUE;
		}
	}
}

// DO EDIT STATUS
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['form-type'] == 'edit3'){

	// Check user rights
	if(in_array($_SESSION['Usertype'], array('Superuser','Admin'))) {
		// Find out how many records there are to update
		$size = count($_POST['Strain']);

		// Begin database transaction
		$dbh->beginTransaction();

		// Create SQL query
		$sql = "INSERT INTO tube_status (tube_id, category_id, comment, user_id)
					SELECT :tube, t.id, :comment, u.id
					FROM tube_status_categories as t
					JOIN users as u
					WHERE t.status=:status AND u.username = :username";
		//echo "sql = ".$sql."<br>";
		// Prepare statement
		$stmt = $dbh->prepare($sql);

		// Bind parameters
		$stmt->bindParam(":tube", $tube);
		$stmt->bindParam(":status", $status);
		$stmt->bindParam(":comment", $comment);
		$stmt->bindParam(":username", $_SESSION['user']);

		for($i = 0; $i < $size; $i++){

			// Define variables
			$comment   = str_replace(array("\r", "\r\n", "\n"), " ", $_POST['Comment'][$i]);
			$tube    = $_POST['Strain'][$i];
			$status   = $_POST['Status'][$i];

			// Skip update if status is not changed
			if ($status == $oldstatus[$i]){
				continue;
			}

			$selected[$i] = $tube;

			// Execute statement
			$stmt->execute();
			//$stmt->debugDumpParams();


			// Check error codes
			if($stmt->errorCode() === '00000'){
				$success = TRUE;
			} else {
				$success = FALSE;
				break;
			}
		}

		// Commit if all were successful
		unset($transaction_fail);
		if($success){
			$dbh->commit();
			$transaction_fail = FALSE;
		} else {
			$dbh->rollBack();
			$transaction_fail = TRUE;
		}
	}
}

// DO ADD
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['form-type'] == 'add' && !empty($_POST['submit'])){
	
	// Check user rights
	if(in_array($_SESSION['Usertype'], array('Superuser','Admin'))) {
		$test = array();
		$num_lines = count($_POST["txtGenotype"]);

		$testresult = 0;

		if($num_lines == 1) {
			$plural = '';
		} else {
			$plural = 's';
		}

		$_SESSION["Line"] = $num_lines;

		unset($_SESSION['saveFail']);

		// Save sessions and validate
		for($i = 1; $i <= $num_lines; $i++) {
			$_SESSION["txtGenotype"][$i] = $_POST["txtGenotype"][$i];
			$_SESSION["txtDonor"][$i] = $_POST["txtDonor"][$i];
			$_SESSION["txtRecipient"][$i] = $_POST["txtRecipient"][$i];
			$_SESSION["txtComment"][$i] = $_POST["txtComment"][$i];
			$_SESSION["txtLabnote"][$i] = $_POST["txtLabnote"][$i];

			// Validate fields
			if(!empty($_POST["txtGenotype"][$i])){
				$validate['Genotype'] = 1;
			} else {
				$validate['Genotype'] = 0;
			}

			if(strain_exists($_POST["txtRecipient"][$i]) || empty($_POST["txtRecipient"][$i])){
				$validate['Recipient'] = 1;
			} else {
				$validate['Recipient'] = 0;
			}

			if(strain_exists($_POST["txtDonor"][$i]) || empty($_POST["txtDonor"][$i])){
				$validate['Donor'] = 1;
			} else {
				$validate['Donor'] = 0;
			}

			// If all fields are empty
			if(empty($_POST["txtDonor"][$i]) && empty($_POST['txtRecipient'][$i]) &&
			   empty($_POST['txtComment'][$i]) && empty($_POST['txtLabnote'][$i])){
				$emptyRow = TRUE;
			} else {
				$emptyRow = FALSE;
			}

			// If all validates pass
			if(array_sum($validate) == count($validate)){
				$test[$i] = 'PASS';
			} else {
				// If ALL fields are empty, set test to empty
				if($emptyRow){
					$test[$i] = 'EMPTY';
				} else {
					$test[$i] = 'FAIL';
					$_SESSION['saveFail'][$i] = $validate;
				}
			}
		}

		if (!empty($_SESSION['saveFail'])) {
			$goAhead = FALSE;
		} else {
			$goAhead = TRUE;
			unset($_SESSION['saveFail']);
		}

		if($goAhead) {
			// Create SQL query
			$sql = "INSERT INTO stock_tube (user_id)
						SELECT id from users WHERE username = :uname LIMIT 1;";

			$sqlInfo = "INSERT INTO strain_information (tube_id, genotype, donor, recipient, comment, labnotes_id, user_id, date)
							SELECT st.id, :genotype, :donor, :recipient, :comment, :labnote, st.user_id, st.creation_date
							FROM stock_tube as st
							WHERE st.id = :strain";

			$sqlStatus = "INSERT INTO tube_status (tube_id, user_id, date)
						  	  SELECT st.id, st.user_id, st.creation_date
							  FROM stock_tube as st
							  WHERE st.id = :strain";

			// Begin database transaction
			$dbh->beginTransaction();

			// Prepare statement
			$stmt = $dbh->prepare($sql);
			$stmtInfo = $dbh->prepare($sqlInfo);
			$stmtStatus = $dbh->prepare($sqlStatus);

			unset($inserted);

			$username = $_SESSION['user'];
			$stmt->bindParam(":uname", $username);
			//$stmtInfo->bindParam(":uname", $username);

			// Loop through all rows to be added
			for($i = 1; $i <= $num_lines; $i++) {
				if($test[$i] != "EMPTY"){
					// Prepare variables
					$genotype = str_replace(array("\r", "\r\n", "\n"), " ", $_POST["txtGenotype"][$i]);
					$recipient = intval($_POST["txtRecipient"][$i]);
					if ($recipient == 0) {
						$recipient = null;
					}
					$donor = intval($_POST["txtDonor"][$i]);
					if ($donor == 0) {
						$donor = null;
					}
					$comment = str_replace(array("\r", "\r\n", "\n"), " ", $_POST["txtComment"][$i]);
					//$labnote = str_replace(array("\r", "\r\n", "\n"), " ", $_POST["txtLabnote"][$i]);
					$labnote = trim($_POST["txtLabnote"][$i]);
					if (preg_match('/EXP-\d{2}-[A-Z0-9]+/i', $labnote, $m)) {
						$labnote = strtoupper($m[0]);
					}


					// Bind parameters
					$stmtInfo->bindParam(":genotype", $genotype);
					$stmtInfo->bindParam(":donor", $donor);
					$stmtInfo->bindParam(":recipient", $recipient);
					$stmtInfo->bindParam(":comment", $comment);
					$stmtInfo->bindParam(":labnote", $labnote);
					$stmtInfo->bindParam(":strain", $strain);
					$stmtStatus->bindParam(":strain",$strain);

					// Execute statements and check error codes
					$stmt->execute();
					if($stmt->errorCode() === '00000'){
						$success = TRUE;
					} else {
						$success = FALSE;
						break;
					}

					// Save ID of last inserted row
					$strain = $dbh->lastInsertId();
					$inserted[] = $strain;

					$stmtInfo->execute();
					if($stmtInfo->errorCode() === '00000'){
						$success = TRUE;
					} else {
						$success = FALSE;
						break;
					}

					$stmtStatus->execute();
					if($stmtStatus->errorCode() === '00000'){
						$success = TRUE;
					} else {
						$success = FALSE;
						break;
					}

					// Clear the session
					unset($_SESSION["txtGenotype"][$i]);
					unset($_SESSION["txtDonor"][$i]);
					unset($_SESSION["txtRecipient"][$i]);
					unset($_SESSION["txtComment"][$i]);
					unset($_SESSION["txtLabnote"][$i]);
				}
			}

			// Commit if all were successful
			unset($transaction_fail);
			//$success = FALSE;
			if($success){
				$dbh->commit();
				$transaction_fail = FALSE;
				unset($_SESSION["Line"]);
			} else {
				$dbh->rollBack();
				$transaction_fail = TRUE;
				// Reset auto_increment
				$stmt = $dbh->prepare('ALTER TABLE stock_tube AUTO_INCREMENT=1');
				$stmt->execute();
			}
		}
		else{
			header("Location: index.php?mode=add&Line=" . $_SESSION["Line"]);
			exit;
		}
	}
}

// DO UPDATE LINES
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['form-type'] == 'add' && !empty($_POST['update_lines'])){
	$num_lines = count($_POST["txtGenotype"]);

	unset($_SESSION["txtGenotype"]);
	unset($_SESSION["txtDonor"]);
	unset($_SESSION["txtRecipient"]);
	unset($_SESSION["txtComment"]);
	unset($_SESSION["txtLabnote"]);

	// Save sessions
	for($i = 1; $i <= $num_lines; $i++) {
		$_SESSION["txtGenotype"][$i] = $_POST["txtGenotype"][$i];
		$_SESSION["txtDonor"][$i] = $_POST["txtDonor"][$i];
		$_SESSION["txtRecipient"][$i] = $_POST["txtRecipient"][$i];
		$_SESSION["txtComment"][$i] = $_POST["txtComment"][$i];
		$_SESSION["txtLabnote"][$i] = $_POST["txtLabnote"][$i];
	}

	$newLines = $_POST['menu1'];
	$_SESSION['Line'] = $newLines;

	header("Location: index.php?mode=add&Line=" . $newLines);
	exit;
}

// DO RESET INPUT
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['form-type'] == 'add' && !empty($_POST['reset'])){

	unset($_SESSION["txtGenotype"]);
	unset($_SESSION["txtDonor"]);
	unset($_SESSION["txtRecipient"]);
	unset($_SESSION["txtComment"]);
	unset($_SESSION["txtLabnote"]);
	$_SESSION['Line'] = 1;

	unset($_SESSION['saveFail']);

	header("Location: index.php?mode=add&Line=1");
	exit;
}

// DO ADD NEW USER
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['form-type'] == 'new-user' && $_SESSION['Usertype'] === 'Admin'){

	// Save post variables
	$username  = $_POST['username'];
    	$usertype  = $_POST['usertype'];
	$password  = $_POST['password'];
	$signature = $_POST['signature'];

	// Get length of input
	$uLength = strlen($username);
	$pLength = strlen($password);
	$sLength = strlen($signature);

	// Validate
	$goAhead = TRUE;
	unset($validation);

	$validation['username'] = 1;
	$validation['signature'] = 1;
	$validation['password'] = 1;

	if ($uLength < 5) {
		$goAhead = FALSE;
		$errorMessage .= "Username must be at least 5 characters long<br>";
		$validation['username'] = 0;
	}

	if ($sLength < 2 OR $sLength > 15) {
		$goAhead = FALSE;
		$errorMessage .= "Signature must be between 2 and 15 characters<br>";
		$validation['signature'] = 0;
	}

	if ($pLength < 8) {
		$goAhead = FALSE;
		$errorMessage .= "Password must be at least 8 characters long<br>";
		$validation['password'] = 0;
	}

	// Check that type is one of 'User' or 'Superuser'
	if (!(in_array($usertype, array('Superuser','User','Admin')))) {
		$goAhead = FALSE;
		$errorMessage .= "User Type can only be User, Superuser or Admin<br>";
	}

	// Check if username already exists
	if(user_exists($username, "username")){
		$goAhead = FALSE;
		$errorMessage .= "User name already taken!<br>";
		$validation['username'] = 0;
	}

	// Check if username already exists
	if(user_exists($username, "signature")){
		$goAhead = FALSE;
		$errorMessage .= "Signature already taken!<br>";
		$validation['signature'] = 0;
	}

	// Validation passed
	if ($goAhead) {
		// Create SQL query
		$sql = "INSERT INTO users (username, usertype, password, signature) VALUES (:username, :usertype, :password, :signature)";

		// Prepare statement
		$stmt = $dbh->prepare($sql);

		// Hash password
		$password = md5($password);

		// Bind parameters
		$stmt->bindParam(":username", $username);
		$stmt->bindParam(":usertype", $usertype);
		$stmt->bindParam(":password", $password);
		$stmt->bindParam(":signature", $signature);

		// Execute statement
		if($stmt->execute()){
			$insert_success = TRUE;
		} else {
			$insert_success = FALSE;
		}
	}
}

//DO UPLOAD GENBANK FILE
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['form-type'] == 'Upload'){
	// Check user rights
	if(in_array($_SESSION['Usertype'], array('Superuser','Admin'))) {
		$target_dir = "./uploads/";
		$file_name = basename($_FILES["fileToUpload"]["name"]);
		$timestamp = time();
		$strain = $_GET['myNum'];
		$suffix = $strain . '_' . $timestamp . '_';
		$sf_file_name = $suffix . $file_name;
		$target_file = $target_dir . $sf_file_name;

		$fileType = pathinfo($target_file,PATHINFO_EXTENSION);
		$success = TRUE;

		// Check file format
		if ( !( preg_match('/^gb[a-z]{0,3}$/',$fileType) || $fileType=='zip')) {
			$success = FALSE;
			$message = "Wrong file format";
		}
		// Check if file exists
		if ($success && file_exists($target_file)) {
			$success = FALSE;
			$message = "File already exists";
                        echo $message;
		}
		if ($success && $_FILES["fileToUpload"]["size"] > 20000000) {
			$success = FALSE;
			$message = "Sorry, your file is too large";
		}
		if ($success && !(move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file))) {
			$success = FALSE;
			$message = "Sorry, there was an error uploading your file";
		}
		if ($success === TRUE) {
			$sql = "INSERT INTO genbank (tube_id, filename, user_id, date)
						SELECT :strain, :file, id, FROM_UNIXTIME(:date)
						FROM users
						WHERE username=:user";
			$stmt = $dbh->prepare($sql);
			$stmt->bindParam(':strain', $strain);
			$stmt->bindParam(':file', $sf_file_name);
			$stmt->bindParam(':user', $_SESSION['user']);
			$stmt->bindParam(':date', $timestamp);
			$stmt->execute();

			if($stmt->errorCode() != '00000'){
				$success = FALSE;
				$message = "Failed to update db table";
				if (file_exists($target_file)) {
					unlink($target_file);
				}
			}
		}

		if ($success === FALSE) {
			die('Something went wrong: ' . $message);
		}
	}
}
?>
