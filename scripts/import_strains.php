<?php
// Import strains from a .csv file into the database.
// Run from the command line in the elfstrains directory:
//   php scripts/import_strains.php strains.csv username
//
// The .csv file is separated by ';' and has a header line with the columns
//   number;Genotype;Donor;Recipient;Comment;.gb available;labnotes
// "number" is the strain's number in the file. Donor and Recipient refer to
// these numbers and are converted to the new strain numbers in the database.
// The ".gb available" column is ignored.

if (PHP_SAPI !== 'cli') {
	die('Run this script from the command line');
}
if ($argc != 3) {
	die("Usage: php scripts/import_strains.php file.csv username\n");
}
$filename = $argv[1];
$username = $argv[2];

// Open database connection
include(__DIR__ . '/../db.php');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Get the user that will be shown as creator of the strains
$stmt = $dbh->prepare("SELECT id FROM users WHERE username = :uname");
$stmt->bindParam(':uname', $username);
$stmt->execute();
$user_id = $stmt->fetchColumn();
if ($user_id === FALSE) {
	die("User '" . $username . "' not found\n");
}

// Read the .csv file
$handle = fopen($filename, "r");
if ($handle === FALSE) {
	die("Cannot open " . $filename . "\n");
}
fgetcsv($handle, 3000, ";"); // skip header
$strains = array();
while ( ($data = fgetcsv($handle, 3000, ";")) !== FALSE) {
	if ($data[0] === NULL || trim($data[0]) === '') {
		continue; // skip empty lines
	}
	$strains[] = array(
		'number'    => intval($data[0]),
		'genotype'  => trim($data[1] ?? ''),
		'donor'     => trim($data[2] ?? ''),
		'recipient' => trim($data[3] ?? ''),
		'comment'   => trim($data[4] ?? ''),
		'labnote'   => trim($data[6] ?? ''),
	);
}
fclose($handle);

// Create SQL queries
$sql = "INSERT INTO stock_tube (user_id) VALUES (:user_id)";

$sqlInfo = "INSERT INTO strain_information (tube_id, genotype, donor, recipient, comment, labnotes_id, user_id, date)
				SELECT st.id, :genotype, :donor, :recipient, :comment, :labnote, st.user_id, st.creation_date
				FROM stock_tube as st
				WHERE st.id = :strain";

$sqlStatus = "INSERT INTO tube_status (tube_id, user_id, date)
			  	  SELECT st.id, st.user_id, st.creation_date
				  FROM stock_tube as st
				  WHERE st.id = :strain";

$stmt = $dbh->prepare($sql);
$stmtInfo = $dbh->prepare($sqlInfo);
$stmtStatus = $dbh->prepare($sqlStatus);

// Begin database transaction
$dbh->beginTransaction();
try {
	// Create one stock tube per strain and remember its new strain number
	$newNumber = array();
	foreach ($strains as $s) {
		if (isset($newNumber[$s['number']])) {
			throw new Exception('Strain number ' . $s['number'] . ' appears twice in the file');
		}
		$stmt->execute(array(':user_id' => $user_id));
		$newNumber[$s['number']] = intval($dbh->lastInsertId());
	}

	// Add strain information and tube status
	foreach ($strains as $s) {
		$parents = array();
		foreach (array('donor', 'recipient') as $parent) {
			if ($s[$parent] === '') {
				$parents[$parent] = NULL;
			} elseif (isset($newNumber[intval($s[$parent])])) {
				$parents[$parent] = $newNumber[intval($s[$parent])];
			} else {
				throw new Exception(ucfirst($parent) . ' ' . $s[$parent] . ' of strain ' . $s['number'] . ' is not in the file');
			}
		}

		$stmtInfo->execute(array(
			':strain'    => $newNumber[$s['number']],
			':genotype'  => $s['genotype'],
			':donor'     => $parents['donor'],
			':recipient' => $parents['recipient'],
			':comment'   => $s['comment'],
			':labnote'   => ($s['labnote'] === '' ? NULL : $s['labnote']),
		));
		$stmtStatus->execute(array(':strain' => $newNumber[$s['number']]));
		if ($stmtInfo->rowCount() != 1 || $stmtStatus->rowCount() != 1) {
			throw new Exception('Failed to save strain ' . $s['number']);
		}
	}

	$dbh->commit();
	echo 'Inserted ' . count($strains) . ' strains';
	if (count($strains) > 0) {
		echo ' as ' . min($newNumber) . '-' . max($newNumber);
	}
	echo "\n";
}
catch (Exception $e) {
	$dbh->rollBack();
	// Give back the unused strain numbers
	$dbh->exec('ALTER TABLE stock_tube AUTO_INCREMENT=1');
	echo 'Rollback, nothing was inserted: ' . $e->getMessage() . "\n";
	exit(1);
}

?>
