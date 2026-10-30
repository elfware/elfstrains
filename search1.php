<?php
// PHP 8: defaults for variables only set in some modes/branches
foreach (array('term1','term2','term3','term4','notterm1','notterm2','notterm3','notterm4',
               'gterm1','gterm2','gterm3','gterm4','notgterm1','notgterm2','notgterm3','notgterm4',
               'sign1','status1','recipient1','donor1','minNum','maxNum','myNum',
               'searchgenotype','searchcomment','where','message') as $v) {
	if (!isset($$v)) { $$v = ''; }
}
if (!isset($page))  { $page = 1; }
if (!isset($limit)) { $limit = 100; }
$numberOfListParameters = 0;
$csv_hdr = '';
$csv_output = '';
//Begin Search for keywords//
if($type == 'word') {
	// If all but signature fields are empty
	if(empty($term1) && empty($term2) && empty($term3) && empty($term4) && empty($notterm1) && empty($notterm2) && empty($notterm3) && empty($notterm4) && empty($status1) && empty($recipient1) && empty($donor1) && !empty($sign1)){
		$where = "WHERE signature LIKE :sign1";
		$message = "Your search for the above keyword in <strong>Signature</strong> resulted in ";
	}
	// If all but status fields are empty
	elseif(empty($term1) && empty($term2) && empty($term3) && empty($term4) && empty($notterm1) && empty($notterm2) && empty($notterm3) && empty($notterm4) && empty($sign1) && empty($recipient1) && empty($donor1) && !empty($status1)){
		$where = "WHERE ts.status LIKE :status1";
		$message = "Your search for the above keyword in <strong>Status</strong> resulted in ";
	}
	elseif(empty($term1) && empty($term2) && empty($term3) && empty($term4) && empty($notterm1) && empty($notterm2) && empty($notterm3) && empty($notterm4) && !empty($sign1) && empty($recipient1) && empty($donor1) && !empty($status1)){
		$where = "WHERE signature LIKE :sign1 AND ts.status LIKE :status1";
		$message = "Your search for the above keyword in <strong>Signature</strong>, </strong>Status</strong> resulted in ";
	}
	// If all but recipient fields are empty
	elseif(empty($term1) && empty($term2) && empty($term3) && empty($term4) && empty($notterm1) && empty($notterm2) && empty($notterm3) && empty($notterm4) && empty($sign1) && !empty($recipient1) && empty($donor1) && empty($status1)){
		$where = "WHERE s.recipient = :recipient1";
		$message = "Your search for the above keyword in <strong>Recipient</strong> resulted in ";
	}
	// If all but donor fields are empty
	elseif(empty($term1) && empty($term2) && empty($term3) && empty($term4) && empty($notterm1) && empty($notterm2) && empty($notterm3) && empty($notterm4) && empty($sign1) && empty($recipient1) && !empty($donor1) && empty($status1)){
		$where = "WHERE s.donor = :donor1";
		$message = "Your search for the above keyword in <strong>Donor</strong> resulted in ";
	}
	// If all but recipient and donor fields are empty
	elseif(empty($term1) && empty($term2) && empty($term3) && empty($term4) && empty($notterm1) && empty($notterm2) && empty($notterm3) && empty($notterm4) && empty($sign1) && !empty($recipient1) && !empty($donor1) && empty($status1)){
		$where = "WHERE s.recipient = :recipient1 AND s.donor = :donor1";
		$message = "Your search for the above keyword in <strong>Recipient</strong>, </strong>Donor</strong> resulted in ";
	}
	else {

		$searchfield = $_POST['check'];

		//If not all search boxes are filled, reassign the variables (necessary for highlighting matching keywords in the table)
		if($term3 == '') {
			$term3 = $term4;
			$term4 = '';
		}
		if($term2 == '') {
			$term2 = $term3;
			$term3 = $term4;
			$term4 = '';
		}
		if($term1 == '') {
			$term1 = $term2;
			$term2 = $term3;
			$term3 = $term4;
			$term4 = '';
		}

		// Handle greek characters
		$gterm1 = convert_greek_characters($term1);
		$gterm2 = convert_greek_characters($term2);
		$gterm3 = convert_greek_characters($term3);
		$gterm4 = convert_greek_characters($term4);
		$notgterm1 = convert_greek_characters($notterm1);
		$notgterm2 = convert_greek_characters($notterm2);
		$notgterm3 = convert_greek_characters($notterm3);
		$notgterm4 = convert_greek_characters($notterm4);

		if($searchgenotype && !$searchcomment) {
			$field1 = "genotype";
			$field2 = "genotype";
			$message = "Your search for the above keywords in <strong>Genotype</strong> resulted in ";
		}
		elseif($searchgenotype && $searchcomment) {
			$field1 = "genotype";
			$field2 = "comment";
			$message = "Your search for the above keywords in <strong>Genotype</strong> or <strong>Comment</strong> resulted in ";
		}
		elseif(!$searchgenotype && $searchcomment) {
			$field1 = "comment";
			$field2 = "comment";
			$message = "Your search for the above keywords in <strong>Comment</strong> resulted in ";
		} else {
			$field1 = "genotype";
			$field2 = "genotype";
		}

		// Adjust sql query depending on terms entered
		if($term2 == '') {
			$incl2 = '';
		} else {
			$incl2 = "AND ($field1 LIKE :term2 OR $field1 LIKE :gterm2 OR $field2 LIKE :commentterm2 OR $field2 LIKE :commentgterm2)";
		}

		if($term3 == '') {
			$incl3 = '';
		} else {
			$incl3 = "AND ($field1 LIKE :term3 OR $field1 LIKE :gterm3 OR $field2 LIKE :commentterm3 OR $field2 LIKE :commentgterm3)";
		}

		if($term4 == '') {
			$incl4 = '';
		} else {
			$incl4 = "AND ($field1 LIKE :term4 OR $field1 LIKE :gterm4 OR $field2 LIKE :commentterm4 OR $field2 LIKE :commentgterm4)";
		}

		if($notterm1 == '') {
			$excl1 = '';
		} else {
			$excl1 = "AND ($field1 NOT LIKE :notterm1 AND $field1 NOT LIKE :notgterm1 AND $field2 NOT LIKE :commentnotterm1 AND $field2 NOT LIKE :commentnotgterm1)";
		}

		if($notterm2 == '') {
			$excl2 = '';
		} else {
			$excl2 = "AND ($field1 NOT LIKE :notterm2 AND $field1 NOT LIKE :notgterm2 AND $field2 NOT LIKE :commentnotterm2 AND $field2 NOT LIKE :commentnotgterm2)";
		}

		if($notterm3 == ''){
			$excl3 = '';
		} else {
			$excl3 = "AND ($field1 NOT LIKE :notterm3 AND $field1 NOT LIKE :notgterm3 AND $field2 NOT LIKE :commentnotterm3 AND $field2 NOT LIKE :commentnotgterm3)";
		}

		if($notterm4 == ''){
			$excl4 = '';
		} else {
			$excl4 = "AND ($field1 NOT LIKE :notterm4 AND $field1 NOT LIKE :notgterm4 AND $field2 NOT LIKE :commentnotterm4 AND $field2 NOT LIKE :commentnotgterm4)";
		}

		if($sign1 == ''){
			$sign = '';
		} else {
			$sign = " AND signature LIKE :sign1";
		}

		if($status1 == ''){
			$status = '';
		} else {
			$status = " AND ts.status LIKE :status1";
		}

		if($recipient1 == ''){
			$recipient = '';
		} else {
			$recipient = " AND s.recipient = :recipient1";
		}

		if($donor1 == ''){
			$donor = '';
		} else {
			$donor = " AND s.donor = :donor1";
		}

		$where = "WHERE ($field1 LIKE :term1 OR $field1 LIKE :gterm1 OR $field2 LIKE :commentterm1 OR $field2 LIKE :commentgterm1 )
					$incl2 $incl3 $incl4 $excl1 $excl2 $excl3 $excl4 $sign $status $recipient $donor";
	}
}
//End Search for keywords//

//Begin Search for Strain numbers//
if($type == 'number') {
	$searchcomment = NULL;
	$searchgenotype = NULL;
	//$myList = $_GET['myList'];
	$message = "Your search resulted in ";

	//If all search variables are empty, we do nothing, otherwise we do the search:
	if (!(empty($minNum) && empty($maxNum) && empty($sign1) && empty($status1) && empty($recipient1) && empty($donor1))) {

		// If only signature box is filled
		if(empty($minNum) && empty($maxNum) && empty($status) && !empty($sign1) && empty($recipient1) && empty($donor1)) {
			$where = "WHERE signature LIKE :sign1";
			$message = "Your search for the above keyword in <strong>Signature</strong> resulted in ";
		}
		elseif(empty($minNum) && empty($maxNum) && empty($sign1) && !empty($status1) && empty($recipient1) && empty($donor1)) {
			$where = "WHERE ts.status LIKE :status1";
			$message = "Your search for the above keyword in <strong>Status</strong> resulted in ";
		}
		elseif(empty($minNum) && empty($maxNum) && !empty($sign1) && !empty($status1) && empty($recipient1) && empty($donor1)) {
			$where = "WHERE signature LIKE :sign1 AND ts.status LIKE :status1";
			$message = "Your search for the above keyword in <strong>Signature</strong>, </strong>Status</strong> resulted in ";
		}
		elseif(empty($minNum) && empty($maxNum) && empty($sign1) && empty($status1) && !empty($recipient1) && empty($donor1)) {
                        $where = "WHERE s.recipient = :recipient1";
                        $message = "Your search for the above keyword in <strong>Recipient</strong> resulted in ";
                }
		elseif(empty($minNum) && empty($maxNum) && empty($sign1) && empty($status1) && empty($recipient1) && !empty($donor1)) {
                        $where = "WHERE s.donor = :donor1";
                        $message = "Your search for the above keyword in <strong>Donor</strong> resulted in ";
                }
		elseif(empty($minNum) && empty($maxNum) && empty($sign1) && empty($status1) && !empty($recipient1) && !empty($donor1)) {
                        $where = "WHERE s.recipient = :recipient1 AND s.donor = :donor1";
                        $message = "Your search for the above keyword in <strong>Recipient</strong>. <strong>Donor</strong> resulted in ";
                }
		else {
			// If both min and max are filled, and min is larger than max: invert the search
			if (!empty($minNum) && !empty($maxNum) != '' && $minNum > $maxNum) {
				$where = "WHERE s.tube_id >= :maxNum AND s.tube_id <= :minNum";
			}

			// If both min and max are filled, and max is -- as intended -- larger than min: search normally
			if (!empty($minNum) && !empty($maxNum) && $minNum <= $maxNum){
				$where = "WHERE s.tube_id >= :minNum AND s.tube_id <= :maxNum";
			}

			// If max is empty while min is filled: only find the entered strain
			if (!empty($minNum) && empty($maxNum)) {
				$where = "WHERE s.tube_id = :minNum";
			}

			// If min is empty while max is filled: only find the entered strain
			if (empty($minNum) && !empty($maxNum)) {
				$where = "WHERE s.tube_id = :maxNum";
			}

			if (!empty($sign1)) {
				$where .= " AND signature LIKE :sign1";
			}

			if (!empty($status1)) {
				$where .= " AND ts.status LIKE :status1";
			}

			if (!empty($recipient1)) {
				$where .= " AND s.recipient = :recipient1";
			}

			if (!empty($donor1)) {
				$where .= " AND s.donor = :donor1";
			}
		}
	}
}
//End Search for strain numbers//

//Show Donor/ recipient from clickable link in table:
if ($_GET['mode'] == 'myNum') {

	$page = 1;
	$limit = 100;
	$myNum = $_GET['myNum'];
	$where = "WHERE s.tube_id = :myNum";
}
//End Show Donor/ recipient from clickable link in table

//Begin List selected strains
if ($_GET['mode'] == 'myList') {
	$page = 1;
	$limit = 100;
	$selected = $_POST['selected'];
	$list = $selected;


	if(count($selected) == 0) {
		echo "You did not select any strain<br>";
	} else {
		// Prepare parameters for strains in list
		$listInQuery = array();
		for($i = 1; $i <= count($list); $i++){
			$listInQuery[$i] = ":list_" . $i;
		}
		$listInQuery = implode(", ", $listInQuery);
		$numberOfListParameters = count($list);

		$message = "You selected the following ";
		$where  = "WHERE s.tube_id IN (" . $listInQuery . ")";
	}
}
//End List selected strains

//Begin List inserted strains
if ($_GET['mode'] == 'add3') {
	$page = 1;
	$limit = 100;
	$list = $inserted ?? array();

	if(count($list) == 0) {
		echo "No strains were saved<br>";
	} else {
		// Prepare parameters for strains in list
		$listInQuery = array();
		for($i = 1; $i <= count($list); $i++){
			$listInQuery[$i] = ":list_" . $i;
		}
		$listInQuery = implode(", ", $listInQuery);
		$numberOfListParameters = count($list);

		$message = "You successfully saved the following ";
		$where  = "WHERE s.tube_id IN (" . $listInQuery . ")";
	}
}
//End List inserted strains

//Begin List edited strains
if ($_GET['mode'] == 'edit2' || $_GET['mode'] == 'edit3') {
	if(!$transaction_fail && !empty($selected)){
		$page = 1;
		$limit = 100;
		$list = $selected;
		// Prepare parameters for strains in list
		$listInQuery = array();
		for($i = 1; $i <= count($list); $i++){
			$listInQuery[$i] = ":list_" . $i;
		}
		$listInQuery = implode(", ", $listInQuery);
		$numberOfListParameters = count($list);

		$message = "Please check the following ";
		$where  = "WHERE s.tube_id IN (" . $listInQuery . ")";
	} else {
		unset($sql);
	}
}
//End List inserted strains

// Do the actual mysql query
if($where){
	// Build SQL statements, one limited and one that counts unlimited
	$sql = "SELECT s.*,	u.signature, ts.status 
			FROM strain_information AS s
			INNER JOIN stock_tube AS t
				ON s.tube_id=t.id
			INNER JOIN ( 
					SELECT tube_id, max(date) AS maxdate
					FROM strain_information
					GROUP BY tube_id 
				) AS s2
				ON s.tube_id=s2.tube_id AND s.date=s2.maxdate
			INNER JOIN users AS u
				ON t.user_id=u.id 
			INNER JOIN (
					SELECT ts1.tube_id, tsc.status 
					FROM tube_status AS ts1
					INNER JOIN (
							SELECT tube_id, max(date) AS maxdate
							FROM tube_status 
							GROUP BY tube_id
						) AS ts2
						ON ts1.tube_id=ts2.tube_id AND ts1.date=ts2.maxdate
					INNER JOIN tube_status_categories AS tsc
						ON ts1.category_id=tsc.status) AS ts 
				ON s.tube_id=ts.tube_id ";
	$sql = $sql . $where . " ORDER BY s.tube_id LIMIT :startval, :limitval";

	$sql2 = "SELECT COUNT(s.tube_id) 
			FROM strain_information AS s
			INNER JOIN stock_tube AS t
				ON s.tube_id=t.id
			INNER JOIN (
					SELECT tube_id, max(date) AS maxdate
					FROM strain_information
					GROUP BY tube_id
				) AS s2
				ON s.tube_id=s2.tube_id AND s.date=s2.maxdate
			INNER JOIN users AS u 
				ON t.user_id=u.id 
			INNER JOIN (
					SELECT ts1.tube_id, tsc.status 
					FROM tube_status AS ts1
					INNER JOIN (
							SELECT tube_id, max(date) AS maxdate
							FROM tube_status 
							GROUP BY tube_id
						) AS ts2
						ON ts1.tube_id=ts2.tube_id AND ts1.date=ts2.maxdate
					INNER JOIN tube_status_categories AS tsc
						ON ts1.category_id=tsc.status) AS ts 
				ON s.tube_id=ts.tube_id ";
	$sql2 = $sql2 . $where;

	// Prepare PDO statements
	$stmt = $dbh->prepare($sql);
	$stmtTotal = $dbh->prepare($sql2);

	// Prepare some variables for binding
	$term1param = '%' . $term1 . '%';
	$term2param = '%' . $term2 . '%';
	$term3param = '%' . $term3 . '%';
	$term4param = '%' . $term4 . '%';
	$gterm1param = '%' . $gterm1 . '%';
	$gterm2param = '%' . $gterm2 . '%';
	$gterm3param = '%' . $gterm3 . '%';
	$gterm4param = '%' . $gterm4 . '%';
	$commentTerm1 = $term1param;
	$commentTerm2 = $term2param;
	$commentTerm3 = $term3param;
	$commentTerm4 = $term4param;
	$commentgTerm1 = $gterm1param;
	$commentgTerm2 = $gterm2param;
	$commentgTerm3 = $gterm3param;
	$commentgTerm4 = $gterm4param;

	$notterm1param = '%' . $notterm1 . '%';
	$notterm2param = '%' . $notterm2 . '%';
	$notterm3param = '%' . $notterm3 . '%';
	$notterm4param = '%' . $notterm4 . '%';
	$notgterm1param = '%' . $notgterm1 . '%';
	$notgterm2param = '%' . $notgterm2 . '%';
	$notgterm3param = '%' . $notgterm3 . '%';
	$notgterm4param = '%' . $notgterm4 . '%';
	$commentNotterm1 = $notterm1param;
	$commentNotterm2 = $notterm2param;
	$commentNotterm3 = $notterm3param;
	$commentNotterm4 = $notterm4param;
	$commentNotgterm1 = $notgterm1param;
	$commentNotgterm2 = $notgterm2param;
	$commentNotgterm3 = $notgterm3param;
	$commentNotgterm4 = $notgterm4param;

	// Helper: only bind a parameter if the placeholder actually exists in the SQL.
	// This prevents PDO HY093 errors when a search mode only uses a subset of parameters.
	function bindIfUsed($stmt, $sql, $name, &$value, $type = PDO::PARAM_STR) {
		if (strpos($sql, $name) !== false) {
			$stmt->bindParam($name, $value, $type);
		}
	}

	// Bind parameters for total number of rows
	bindIfUsed($stmtTotal, $sql2, ":status1",          $status1);
	bindIfUsed($stmtTotal, $sql2, ":sign1",             $sign1);
	bindIfUsed($stmtTotal, $sql2, ":recipient1",        $recipient1);
	bindIfUsed($stmtTotal, $sql2, ":donor1",            $donor1);
	bindIfUsed($stmtTotal, $sql2, ":term1",             $term1param);
	bindIfUsed($stmtTotal, $sql2, ":term2",             $term2param);
	bindIfUsed($stmtTotal, $sql2, ":term3",             $term3param);
	bindIfUsed($stmtTotal, $sql2, ":term4",             $term4param);
	bindIfUsed($stmtTotal, $sql2, ":gterm1",            $gterm1param);
	bindIfUsed($stmtTotal, $sql2, ":gterm2",            $gterm2param);
	bindIfUsed($stmtTotal, $sql2, ":gterm3",            $gterm3param);
	bindIfUsed($stmtTotal, $sql2, ":gterm4",            $gterm4param);
	bindIfUsed($stmtTotal, $sql2, ":commentterm1",      $commentTerm1);
	bindIfUsed($stmtTotal, $sql2, ":commentterm2",      $commentTerm2);
	bindIfUsed($stmtTotal, $sql2, ":commentterm3",      $commentTerm3);
	bindIfUsed($stmtTotal, $sql2, ":commentterm4",      $commentTerm4);
	bindIfUsed($stmtTotal, $sql2, ":commentgterm1",     $commentgTerm1);
	bindIfUsed($stmtTotal, $sql2, ":commentgterm2",     $commentgTerm2);
	bindIfUsed($stmtTotal, $sql2, ":commentgterm3",     $commentgTerm3);
	bindIfUsed($stmtTotal, $sql2, ":commentgterm4",     $commentgTerm4);
	bindIfUsed($stmtTotal, $sql2, ":notterm1",          $notterm1param);
	bindIfUsed($stmtTotal, $sql2, ":notterm2",          $notterm2param);
	bindIfUsed($stmtTotal, $sql2, ":notterm3",          $notterm3param);
	bindIfUsed($stmtTotal, $sql2, ":notterm4",          $notterm4param);
	bindIfUsed($stmtTotal, $sql2, ":notgterm1",         $notgterm1param);
	bindIfUsed($stmtTotal, $sql2, ":notgterm2",         $notgterm2param);
	bindIfUsed($stmtTotal, $sql2, ":notgterm3",         $notgterm3param);
	bindIfUsed($stmtTotal, $sql2, ":notgterm4",         $notgterm4param);
	bindIfUsed($stmtTotal, $sql2, ":commentnotterm1",   $commentNotterm1);
	bindIfUsed($stmtTotal, $sql2, ":commentnotterm2",   $commentNotterm2);
	bindIfUsed($stmtTotal, $sql2, ":commentnotterm3",   $commentNotterm3);
	bindIfUsed($stmtTotal, $sql2, ":commentnotterm4",   $commentNotterm4);
	bindIfUsed($stmtTotal, $sql2, ":commentnotgterm1",  $commentNotgterm1);
	bindIfUsed($stmtTotal, $sql2, ":commentnotgterm2",  $commentNotgterm2);
	bindIfUsed($stmtTotal, $sql2, ":commentnotgterm3",  $commentNotgterm3);
	bindIfUsed($stmtTotal, $sql2, ":commentnotgterm4",  $commentNotgterm4);
	bindIfUsed($stmtTotal, $sql2, ":minNum",            $minNum);
	bindIfUsed($stmtTotal, $sql2, ":maxNum",            $maxNum);
	bindIfUsed($stmtTotal, $sql2, ":myNum",             $myNum);

	for($i = 1; $i <= $numberOfListParameters; $i++){
		bindIfUsed($stmtTotal, $sql2, ":list_" . $i, $list[$i-1]);
	}

	//Exectue total statement
	$stmtTotal->execute();

	// Fetch total number of rows
	$total_records = $stmtTotal->fetchColumn();

	// Convert pages to starting row number
	if ($total_records > $limit) {
		$pages = ceil($total_records / $limit);

		// Set page number to highest possible if too big
		if($page > $pages){
			$page = $pages;
		}

		$startval = ($page - 1) * $limit;
	} else {
		$pages = 1;
		$startval = 0;
	}

	// Bind parameters for limited rows
	bindIfUsed($stmt, $sql, ":status1",          $status1);
	bindIfUsed($stmt, $sql, ":sign1",            $sign1);
	bindIfUsed($stmt, $sql, ":recipient1",       $recipient1);
	bindIfUsed($stmt, $sql, ":donor1",           $donor1);
	bindIfUsed($stmt, $sql, ":startval",         $startval, PDO::PARAM_INT);
	bindIfUsed($stmt, $sql, ":limitval",         $limit,    PDO::PARAM_INT);
	bindIfUsed($stmt, $sql, ":term1",            $term1param);
	bindIfUsed($stmt, $sql, ":term2",            $term2param);
	bindIfUsed($stmt, $sql, ":term3",            $term3param);
	bindIfUsed($stmt, $sql, ":term4",            $term4param);
	bindIfUsed($stmt, $sql, ":gterm1",           $gterm1param);
	bindIfUsed($stmt, $sql, ":gterm2",           $gterm2param);
	bindIfUsed($stmt, $sql, ":gterm3",           $gterm3param);
	bindIfUsed($stmt, $sql, ":gterm4",           $gterm4param);
	bindIfUsed($stmt, $sql, ":commentterm1",     $commentTerm1);
	bindIfUsed($stmt, $sql, ":commentterm2",     $commentTerm2);
	bindIfUsed($stmt, $sql, ":commentterm3",     $commentTerm3);
	bindIfUsed($stmt, $sql, ":commentterm4",     $commentTerm4);
	bindIfUsed($stmt, $sql, ":commentgterm1",    $commentgTerm1);
	bindIfUsed($stmt, $sql, ":commentgterm2",    $commentgTerm2);
	bindIfUsed($stmt, $sql, ":commentgterm3",    $commentgTerm3);
	bindIfUsed($stmt, $sql, ":commentgterm4",    $commentgTerm4);
	bindIfUsed($stmt, $sql, ":notterm1",         $notterm1param);
	bindIfUsed($stmt, $sql, ":notterm2",         $notterm2param);
	bindIfUsed($stmt, $sql, ":notterm3",         $notterm3param);
	bindIfUsed($stmt, $sql, ":notterm4",         $notterm4param);
	bindIfUsed($stmt, $sql, ":notgterm1",        $notgterm1param);
	bindIfUsed($stmt, $sql, ":notgterm2",        $notgterm2param);
	bindIfUsed($stmt, $sql, ":notgterm3",        $notgterm3param);
	bindIfUsed($stmt, $sql, ":notgterm4",        $notgterm4param);
	bindIfUsed($stmt, $sql, ":commentnotterm1",  $commentNotterm1);
	bindIfUsed($stmt, $sql, ":commentnotterm2",  $commentNotterm2);
	bindIfUsed($stmt, $sql, ":commentnotterm3",  $commentNotterm3);
	bindIfUsed($stmt, $sql, ":commentnotterm4",  $commentNotterm4);
	bindIfUsed($stmt, $sql, ":commentnotgterm1", $commentNotgterm1);
	bindIfUsed($stmt, $sql, ":commentnotgterm2", $commentNotgterm2);
	bindIfUsed($stmt, $sql, ":commentnotgterm3", $commentNotgterm3);
	bindIfUsed($stmt, $sql, ":commentnotgterm4", $commentNotgterm4);
	bindIfUsed($stmt, $sql, ":minNum",           $minNum);
	bindIfUsed($stmt, $sql, ":maxNum",           $maxNum);
	bindIfUsed($stmt, $sql, ":myNum",            $myNum);

	for($i = 1; $i <= $numberOfListParameters; $i++){
		bindIfUsed($stmt, $sql, ":list_" . $i, $list[$i-1]);
	}

	// Execute statement
	$stmt->execute();

	// Fetch results
	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$showing_records = count($result);

       /* TODO Troubleshooting
	echo $sql;
	echo "<br>";
	echo $sql2;
	echo "<br>";
	echo "sign1: $sign1";
	echo "<br>";
	echo "startval: $startval";
	echo "<br>";
	echo "limitval: $limit";
	echo "<br>";
	echo "term1: $term1";
	echo "<br>";
	echo "gterm1: $gterm1";
	echo "<br>";
	echo "notterm1: $notterm1";
	echo "<br>";
	echo "comterm1: $commentTerm1";
	echo "<br>";
	echo "comnotterm1: $commentNotterm1";
	echo "<br>";
	echo "field1: $field1";
	echo "<br>";
	echo "minNum: $minNum";
	echo "<br>";
	echo "maxNum: $maxNum";
	echo "<br>";
	echo "numberOfListParameters: $numberOfListParameters";
	echo "<br>";
	echo "field2: $field2";
	echo "<br>";
	echo "total_records: $total_records<br>";
	echo "total_records session: " . $_SESSION['total_records'] . "<br>";
	print("result:<pre>".print_r($result, true)."</pre>");
	print("list:<pre>".print_r($list, true)."</pre>");*/

	$_SESSION['total_records'] = $total_records;

	// Set result text and plural ending
	if ($total_records == '1') {
		$message_records = 'only one';
		$plural = '';
	} else {
		$message_records = $total_records;
		$plural = 's';
	}

	// Print result text
	/*if($transaction_fail){
		echo "<span style='color: red'><strong>Something went wrong when talking to the database!</strong></span><br>";
	} else*/if ($total_records == '0') {
		echo "<span style='color: red'>Sorry, no matches. Try again</span>";
	} else {
		//$helpmessage2 = "<br><span style='font-weight: bold; color: red;'>Tip:</span> To see the information about a donor or recipient, click the link in the corresponding cell. Use ctrl+click (cmd+click in Mac OS) to open in a new tab.";
	    echo $message . "<span style='font-weight: bold'>" . $total_records . "</span> strain" . $plural . ". ";

		//Multi page:
		if ($total_records > $limit) {
			echo "Showing <span style='font-weight: bold'> " . $showing_records . "</span> strains per page starting at no. <span style='font-weight: bold'>" . ($startval+1) . "</span>:<br>";

			echo "<br>Page <span style='font-weight: bold'>" . $page . "</span> of <span style='font-weight: bold'>" . $pages . "</span>. ";

			// Print pagination links
			for($n = 1; $n <= $pages; $n++) {

				if($type == 'word'){
					if ($n != $page){
						$link  = "?mode=search&amp;type=word&amp;search=1";
						$link .= "&amp;term1=" . $term1;
						$link .= "&amp;term2=" . $term2;
						$link .= "&amp;term3=" . $term3;
						$link .= "&amp;term4=" . $term4;

						$link .= "&amp;notterm1=" . $notterm1;
						$link .= "&amp;notterm2=" . $notterm2;
						$link .= "&amp;notterm3=" . $notterm3;
						$link .= "&amp;notterm4=" . $notterm4;

						$link .= "&amp;sign1=" . $sign1;
						$link .= "&amp;status1=" . $status1;
						$link .= "&amp;recipient1=" . $recipient1;
						$link .= "&amp;donor1=" . $donor1;


						$link .= "&amp;searchcomment="  . $searchcomment;
						$link .= "&amp;searchgenotype=" . $searchgenotype;

						$link .= "&amp;limit=" . $limit;

						$link .= "&amp;page=" . $n;
						echo ' <a href="' . $link . '">' . $n . '</a> ';
					} else {
						echo " <span style='font-weight: bold; color: blue;'>" . $n . "</span> ";
					}
				}
				elseif($type == 'number'){
					if ($n != $page){
						echo ' <a href="?mode=search&amp;type=number&amp;search=1&amp;minNum=' . $minNum . '&amp;maxNum=' . $maxNum . '&amp;sign1=' . $sign1 . '&amp;status1=' . $status1 . '&amp;recipient1=' . $recipient1 . '&amp;donor1=' . $donor1 . '&amp;limit=' . $limit . '&amp;page=' . $n . '">' . $n . '</a> ';
					} else {
						echo " <span style='font-weight: bold; color: blue;'>" . $n . "</span> ";
					}
				}
			}
		}

		// Print out a table of the resulting strains
		echo "<table class='sample' data-searchcomment=" . ($searchcomment ? "1" : "0") . ">";

			echo "<tr>";
				echo "<th>Strain</th>";
				echo "<th style='width: 250px'>Genotype</th>";
				echo "<th>Recipient</th>";
				echo "<th>Donor</th>";
				echo "<th style='width: 250px'>Comment</th>";
				echo "<th>Signature</th>";
				echo "<th>Labnote</th>";
				echo "<th>Status</th>";
				echo "<th style='min-width: 70px'>Modified</th>";
				echo "<th>";
					echo "Select";
					echo "<button class='display-none' id='check-all'>All</button><br>";
					echo "<button class='display-none' id='check-none'>None</button>";
				echo "</th>";
			echo "</tr>";

			echo "<form action='index.php?mode=myList' name='selectRow' method='post'>";
				echo "<input type='submit' title='Show a table of only the selected strains' name='show' value='Show selected'/>";

				if(in_array($_SESSION['Usertype'], array('Superuser','Admin'))) {
					echo "<input type='submit' title='Edit the selected strains' name='edit' value='Edit strains'/>";
				}

				if(in_array($_SESSION['Usertype'], array('Superuser','Admin'))) {
					echo "<input type='submit' title='Edit the selected tubes status' name='edit_status' value='Edit status'/>";
				}

				echo "<input type='submit' title='Open the selected strains in a printer-friendly table' name='print' value='print selected'/>";

				// Prepare CSV header
				$csv_hdr .= "Strain;Genotype;Recipient;Donor;Comment;Signature;Labnote;Status;Modified\n";

				// Loop through all strains


				foreach ($result as $row) {

					// Convert html characters to printable
					$genotype = htmlspecialchars($row['genotype']);
					$genotype1 = htmlspecialchars($row['genotype']);
					$comment = htmlspecialchars($row['comment']);
					$comment1 = htmlspecialchars($row['comment']);

					// Display every match to the search word with colored text:
					// Genotype:
					if($searchgenotype){
						if($term1 != '') {
							$genotype = preg_replace('/' . preg_quote($term1, '/') . '/i', "¢$0¦", $genotype);
							$genotype = preg_replace('/' . preg_quote($gterm1, '/') . '/i', "¢$0¦", $genotype);
						}

						if($term2 != '') {
							$genotype = preg_replace('/' . preg_quote($term2, '/') . '/i', "¢$0¦", $genotype);
							$genotype = preg_replace('/' . preg_quote($gterm2,'/') . '/i', "¢$0¦", $genotype);
						}

						if($term3 != '') {
							$genotype = preg_replace('/' . preg_quote($term3, '/') . '/i', "¢$0¦", $genotype);
							$genotype = preg_replace('/' . preg_quote($gterm3, '/') . '/i', "¢$0¦", $genotype);
}

						if($term4 != '') {
							$genotype = preg_replace('/' . preg_quote($term4, '/') . '/i', "¢$0¦", $genotype);
							$genotype = preg_replace('/' . preg_quote($gterm4, '/') . '/i', "¢$0¦", $genotype);
						}

						$genotype = preg_replace('/' . preg_quote('¢', '/') . '/i', "<span style='color: blue; font-weight: bold;'>", $genotype);
						$genotype = preg_replace('/' . preg_quote('¦', '/') . '/i', "</span>", $genotype);
					}

					// Comment:
					if($searchcomment){

						if($term1 != '') {
							$comment = preg_replace('/' . preg_quote($term1, '/') . '/i', "¢$0¦", $comment);
							$comment = preg_replace('/' . preg_quote($gterm1, '/') . '/i', "¢$0¦", $comment);
						}

						if($term2 != '') {
							$comment = preg_replace('/' . preg_quote($term2, '/') . '/i', "¢$0¦", $comment);
							$comment = preg_replace('/' . preg_quote($gterm2, '/') . '/i', "¢$0¦", $comment);
						}

						if($term3 != '') {
							$comment = preg_replace('/' . preg_quote($term3, '/') . '/i', "¢$0¦", $comment);
							$comment = preg_replace('/' . preg_quote($gterm3, '/') . '/i', "¢$0¦", $comment);
						}

						if($term4 != '') {
							$comment = preg_replace('/' . preg_quote($term4, '/') . '/i', "¢$0¦", $comment);
							$comment = preg_replace('/' . preg_quote($gterm4, '/') . '/i', "¢$0¦", $comment);
						}

						$comment = preg_replace('/' . preg_quote('¢', '/') . '/i', "<span style='color: blue; font-weight: bold;'>", $comment);
						$comment = preg_replace('/' . preg_quote('¦', '/') . '/i', "</span>", $comment);

					}

					/*if (strpos($comment, "<span style='color: blue;") === false) {
						$comment = (strlen($comment) > 75) ? substr($comment,0,75) . '...' : $comment;
					}*/

					echo "<tr>";
						echo "<td>";
						echo "<a href=index.php?mode=info&myNum=" . $row['tube_id'] . " target='_blank' title='View full info about EL" . $row['tube_id']. "'>EL" . $row['tube_id'] . "</a>";
						$csv_output .= "EL" . $row['tube_id'] . "; ";
						echo "</td>";

						echo "<td>";
							echo $genotype;
							$csv_output .= $genotype1 . "; ";
						echo "</td>";

						echo "<td align='left'>";
							if ($row['recipient'] == "0" || $row['recipient'] == "") {
								echo "";
								$csv_output .= "; ";
							} else {
								echo "<a href=index.php?mode=myNum&myNum=" . $row['recipient'] . " target='_blank' title='View EL" . $row['recipient']. " in a new tab'>EL" . $row['recipient'] . "</a>";
								$csv_output .= "EL" . $row['recipient'] . "; ";
							}
						echo "</td>";

						echo "<td align='left'>";
							if ($row['donor'] == "0" || $row['donor'] == "") {
								echo "";
								$csv_output .= "; ";
							} else {
								echo "<a href=index.php?mode=myNum&myNum=" . $row['donor'] . " title='View EL" . $row['donor'] . " in a new tab'>EL" . $row['donor'] . "</a>";
								$csv_output .= "EL" . $row['donor'] . "; ";
							}
						echo "</td>";

						echo "<td>";
							echo $comment;
							$csv_output .= $comment1 . "; ";
						echo "</td>";

						echo "<td>";
							echo $row['signature'];
							$csv_output .= $row['signature'] . "; ";
						echo "</td>";

						echo "<td>";
							if ($row['labnotes_id'] == "")
								echo "";
							else {
								echo "<a href=https://www.eln.uu.se/notebook/experiment/" . $row['labnotes_id'] . " target='_blank' title='View labnote " . $row['labnotes_id'] . " in a new tab'>" . $row['labnotes_id'] . "</a>"; 
							}
							$csv_output .= $row['labnotes_id'] . "; ";
						echo "</td>";

						echo "<td>";
							echo $row['status'];
							$csv_output .= $row['status'] . "; ";
						echo "</td>";

						echo "<td>";

							if ($row['date'] == "0000-00-00 00:00:00"){
								echo '';
								$csv_output .= ";\n";
							} else {
								echo $row['date'];
								$csv_output .= $row['date'] . "\n";
							}
						echo "</td>";
						echo "<td>";
							echo "<div align=center><input class='strain-checkbox' type=checkbox name='selected[]' value=" . $row['tube_id'] . "></div>";
						echo "</td>";
					echo "</tr>";
					//Stop making the table
				}

			echo "</form>";
		echo "</table>";

		// Close the mysql connection
		$stmt->closeCursor();
		$stmtTotal->closeCursor();

		if ($total_records > $limit) {

			echo "<br>Page <span style='font-weight: bold'>" . $page . "</span> of <span style='font-weight: bold'>" . $pages . "</span>.";

			// Print pagination links
			for($n = 1; $n <= $pages; $n++) {

				if($type == 'word'){
					if ($n != $page){
						$link  = "?mode=search&amp;type=word&amp;search=1";
						$link .= "&amp;term1=" . $term1;
						$link .= "&amp;term2=" . $term2;
						$link .= "&amp;term3=" . $term3;
						$link .= "&amp;term4=" . $term4;

						$link .= "&amp;notterm1=" . $notterm1;
						$link .= "&amp;notterm2=" . $notterm2;
						$link .= "&amp;notterm3=" . $notterm3;
						$link .= "&amp;notterm4=" . $notterm4;

						$link .= "&amp;sign1=" . $sign1;
						$link .= "&amp;status1=" . $status1;
						$link .= "&amp;recipient1=" . $recipient1;
						$link .= "&amp;donor1=" . $donor1;

						$link .= "&amp;searchcomment="  . $searchcomment;
						$link .= "&amp;searchgenotype=" . $searchgenotype;

						$link .= "&amp;limit=" . $limit;

						$link .= "&amp;page=" . $n;
						echo ' <a href="' . $link . '">' . $n . '</a> ';
					} else {
						echo " <span style='font-weight: bold; color: blue;'>" . $n . "</span> ";
					}
				}
				elseif($type == 'number'){
					if ($n != $page){
						echo ' <a href="?mode=search&amp;type=number&amp;search=1&amp;minNum=' . $minNum . '&amp;maxNum=' . $maxNum . '&amp;sign1=' . $sign1 . '&amp;status1=' . $status1 . '&amp;recipient1=' . $recipient1 . '&amp;donor1=' . $donor1 . '&amp;limit=' . $limit . '&amp;page=' . $n . '">' . $n . '</a> ';
					} else {
						echo " <span style='font-weight: bold; color: blue;'>" . $n . "</span> ";
					}
				}
			}
		}


		echo '<form name="export" action="export.php" method="post">';
			echo '<input type="submit" value="Export table to CSV">';
			echo '<input type="hidden" value="' . $csv_hdr . '" name="csv_hdr">';
			echo '<input type="hidden" value="' . $csv_output . '" name="csv_output">';
		echo "</form>";
	}

} else {
	if($transaction_fail){
		echo "<span style='color: red'><strong>Something went wrong when talking to the database!</strong></span><br>";
	} else {
		echo "<span style='color: red'><strong>You need to provide at least one keyword!</strong></span><br>";
	}
}
?>
