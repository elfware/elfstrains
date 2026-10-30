<?php

$go_ahead = FALSE;

//Begin List selected strains
if ($_GET['mode'] == 'myList') {
	$selected = $_POST['selected'];

	$list = $selected;
	$message = "You selected the following ";

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

		$sql = "SELECT s.*,	u.signature, t.status 
				FROM strain_information as s
				INNER JOIN ( 
						SELECT tube_id, max(date) as maxdate
						FROM strain_information
						GROUP BY tube_id) as s2
					ON s.tube_id=s2.tube_id AND s.date=s2.maxdate
				INNER JOIN users as u
					ON s.user_id=u.id 
				INNER JOIN (
						SELECT ts.tube_id, tsc.status 
						FROM tube_status as ts
						INNER JOIN (
								SELECT tube_id, max(date) as maxdate
								FROM tube_status 
								GROUP BY tube_id) as ts2
							ON ts.tube_id=ts2.tube_id AND ts.date=ts2.maxdate
						INNER JOIN tube_status_categories as tsc
							ON ts.category_id=tsc.status) as t 
					ON s.tube_id=t.tube_id 
				WHERE s.tube_id IN (" . $listInQuery . ")
				ORDER BY s.tube_id";
	}
}
//End List selected strains

// Do the actual mysql query
if($sql){
	// Prepare PDO statement
	$stmt = $dbh->prepare($sql);

	// Bind parameters
	for($i = 1; $i <= $numberOfListParameters; $i++){
		$stmt->bindParam(":list_" . $i, $list[$i-1]);
	}

	// Execute statement
	$stmt->execute();

	// Check how many rows were found
	$rows_found = $stmt->rowCount();

	if ($rows_found == '0') {
		echo "<span style='color: red'>Sorry, no matches. Try again</span>";
	}
	else {
		// Set some print variables
		if ($rows_found == '1') {
			$count = 'only one';
			$plural = '';
		} else {
			$plural = 's';
		}

		// Fetch results
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

		// Make a nice table:
		echo "<div class='noPrint'>" . $message . "<strong>" . $rows_found . "</strong> strain" . $plural . ":<br>";
			// Print out a table of the resulting strains
			// TOOD
			echo "<input type='button' value='Print' onclick='window.print()'>";
		echo "</div><br>";

		echo "<div id='landscape'>";
			echo "<table class='sample' style='width:940px'><br>";
				echo "<thead>";
					echo "<tr>";
						echo "<th>Strain</th>";
						echo "<th>Genotype</th>";
						echo "<th>Recipient</th>";
						echo "<th>Donor</th>";
						echo "<th>Comment</th>";
						echo "<th>Labnote</th>";
						echo "<th>Signature</th>";
					echo "</tr>";
				echo "</thead>";

				foreach($result as $row){
					$genotype = htmlspecialchars($row['genotype']);
					$comment = htmlspecialchars($row['comment']);

					echo "<tr>";
						echo "<td>";
							echo 'EL' . $row['tube_id'];
						echo "</td>";

						echo "<td>";
							echo $genotype;
						echo "</td>";

						echo "<td align='right'>";
							if ($row['recipient'] == "0") {
								echo "";
							} else {
								echo "EL" . $row['recipient'] . "</a>";
							}
						echo "</td>";

						echo "<td align='right'>";
							if ($row['donor'] == "0") {
								echo "";
							} else {
								echo "EL" . $row['donor'] . "</a>";
							}
						echo "</td>";

						echo "<td>";
							echo $comment;
						echo "</td>";

						echo "<td>";
							echo $row['labnotes_id'];
						echo "</td>";

						echo "<td>";
							echo $row['signature'];
						echo "</td>";
					echo "</tr>";
				} // END foreach($result as $row)

			echo "</table>";
		echo "</div>";
	}
}

?>
