<?php

// Check user rights
if(in_array($_SESSION['Usertype'],  array('Superuser','Admin'))) {

	//Begin List selected strains
	if ($_GET['mode'] == 'myList') {
		$selected = $_POST['selected'];
		$list = $selected;

		if(isset($list)){
			// Set help message
			$helpmessage = "Before you press the save button, please verify that you changed the strain information correctly.";

			// Prepare parameters for strains in list
			$listInQuery = array();
			for($i = 1; $i <= count($list); $i++){
				$listInQuery[$i] = ":list_" . $i;
			}
			$listInQuery = implode(", ", $listInQuery);
			$numberOfListParameters = count($list);

			$message = "You selected the following ";
			$sql = "SELECT s.*
					FROM strain_information as s
					INNER JOIN (
							SELECT tube_id, max(date) as maxdate
							FROM strain_information
							GROUP BY tube_id) as s2
						ON s.tube_id=s2.tube_id AND s.date=s2.maxdate
					WHERE s.tube_id IN (" . $listInQuery . ")
					ORDER BY s.tube_id ASC";
		} else {
			$helpmessage = "You selected nothing to edit.";
		}
	}

	//End List selected strains

	if($sql){
		// Prepare PDO statement
		$stmt = $dbh->prepare($sql);

		// Bind parameters
		for($i = 1; $i <= $numberOfListParameters; $i++){
			$stmt->bindParam(":list_" . $i, $list[$i-1]);
		}

		// Execute statement
		$stmt->execute();

		// Fetch results
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$total_records = count($result);

		// Make a nice table:
		echo $message . "<strong>" . $total_records . "</strong> " . ($total_records>1 ? "strains" : "strain") . " for <span style='color: red'><strong>editing</strong></span>:";
		echo "<form name='strainstoupdate' method='post' action='index.php?mode=edit2'>";
		echo "<table class='sample' border='1'><br>";

			// Print out a table of the resulting strains:
			echo "<tr>";
				echo "<th>Strain</th>";
				echo "<th width='300px'>Genotype</th>";
				echo "<th>Parents</th>";
				echo "<th width='300px'>Comment</th>";
				echo "<th>Labnote</th>";
			echo "</tr>";

//			echo "<form name='strainstoupdate' method='post' action='index.php?mode=edit2'>";
			    echo "<input type='hidden' name='form-type' id='form-type' value='edit'>";
//				echo "<input type='submit' title='Save the changes' name='save' value='save'>";

				$strainIndex = 0;

				// Loop through all strains
				foreach ($result as $row) {
					$genotype = htmlspecialchars($row['genotype']);
					$comment = htmlspecialchars($row['comment']);

					echo "<tr>";
						echo "<td>";
							echo "<a href=index.php?mode=info&myNum=" . $row['tube_id'] . " title='View full info about EL" . $row['tube_id']. "'>EL" . $row['tube_id'] . "</a>";
							echo "<input type='hidden' name='Strain[" . $strainIndex . "]' value='" . $row['tube_id'] . "'>";
						echo "</td>";

						echo "<td>";
							echo $genotype . "<br><br>";
							echo "<textarea type='text' rows=3 cols=40 name='Genotype[" . $strainIndex . "]'>" . $row['genotype'] . "</textarea>\n";
						echo "</td>";

						echo "<td>";
						echo "<div>Recipient:</div>";
						if ($row['recipient'] == "0") {
							echo "";
							echo "EL<input type='text' size=5 name='Recipient[" . $strainIndex . "]' value=''>\n";
						} else {
							echo "<a href=index.php?mode=myNum&myNum=" . $row['recipient'] . " title='View EL" . $row['recipient'] . " in a new tab'>EL" . $row['recipient'] . "</a><br>";
							echo "EL<input type='text' target='_blank' size=5 name='Recipient[" . $strainIndex . "]' value='" . $row['recipient'] . "'>\n";

						}
						echo "<br><br>";
							echo "<div>Donor:</div>";
							if ($row['donor'] == "0" || empty($row['donor'])) {
							  echo "";
								echo "EL<input type='text' size=5 name='Donor[" . $strainIndex . "]' value=''>\n<br><br>";
							} else {
								echo "<a href=index.php?mode=myNum&myNum=" . $row['donor'] . " target='_blank' title='View EL" . $row['donor'] . " in a new tab'>EL" . $row['donor'] . "</a><br>";
								echo "EL<input type='text' size=5 name='Donor[" . $strainIndex . "]' value='" . $row['donor'] . "'>\n";
							}


						echo "</td>";

						echo "<td>";
							echo $comment . "<br><br>";
							echo "<textarea type='text' rows=3 cols=40 name='Comment[" . $strainIndex . "]'>" . $row['comment'] . "</textarea>\n";
						echo "</td>";

						echo "<td>";
							if($row['labnotes_id'] == '') {
								echo "";
								echo "<textarea type='text' maxlength='13' name='Labnote[" . $strainIndex . "]'></textarea>";
							} else {
								echo "<a href=https://www.eln.uu.se/notebook/experiment/" . $row['labnotes_id'] . " target='_blank' title='View labnote " . $row['labnotes_id'] . " in a new tab'>" . $row['labnotes_id'] . "</a>";
								echo "<br><br>";
								echo "<textarea type='text'maxlength='13'  name='Labnote[" . $strainIndex . "]'>" . $row['labnotes_id'] . "</textarea>";
							}
						echo "</td>";

					echo "</tr>";
					//Stop making the table

					$strainIndex++;
				} // End foreach ($result as $row)
			echo "</table>";
			echo "<br>";
			echo "<input type='submit' style='width:100px' title='Save the changes' name='save' value='save'>";
		echo "</form>";

		// Close the mysql connection
		$stmt->closeCursor();
		//$stmtTotal->closeCursor();

	} // End if($sql)
} // End if($_SESSION['Usertype'] == 'Superuser')
else {
	echo "<span style='color: red; font-weight: bold;'>You are not trusted to edit strains!</span>";
}
?>
