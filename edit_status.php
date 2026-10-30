
<?php

// Check user rights
if(in_array($_SESSION['Usertype'], array('Superuser','Admin'))) {


	//Begin List selected strains
	if ($_GET['mode'] == 'myList') {
		$selected = $_POST['selected'];
		$list = $selected;

		if(isset($list)){
			// Set help message
			$helpmessage = "Before you press the save button, please verify that you changed the strain information correctly";

			// Prepare parameters for strains in list
			$listInQuery = array();
			for($i = 1; $i <= count($list); $i++){
				$listInQuery[$i] = ":list_" . $i;
			}
			$listInQuery = implode(", ", $listInQuery);
			$numberOfListParameters = count($list);

			$message = "You selected the following ";

			$sql = "SELECT ts.tube_id, tsc.status 
					FROM tube_status as ts
					INNER JOIN (
							SELECT tube_id, max(date) as maxdate
							FROM tube_status 
							GROUP BY tube_id) as ts2
						ON ts.tube_id=ts2.tube_id AND ts.date=ts2.maxdate
					INNER JOIN tube_status_categories as tsc
						ON ts.category_id=tsc.id 
					WHERE ts.tube_id IN (" . $listInQuery . ")";
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

		for ($i=0;$i<count($result);$i++) {
			$oldstatus[$i] = $result[$i]['status'];
		}

		$total_records = count($result);

		// Get tube statuses
		$sql = "SELECT status FROM tube_status_categories";
		$stmt = $dbh->prepare($sql);
		$stmt->execute();
		$tubestatuses = $stmt->fetchAll(PDO::FETCH_ASSOC);

		// Make a nice table:
		echo $message . "<strong>" . $total_records . "</strong> " . ($total_records>1 ? "tubes" : "tube") . " for <span style='color: red'><strong>editing</strong></span>:";
		echo "<form name='strainstoupdate' method='post' action='index.php?mode=edit3'>";
		echo "<table class='sample' border='1'><br>";

			// Print out a table of the resulting strains:
			echo "<tr>";
				echo "<th>Strain</th>";
				echo "<th width='150px'>Status</th>";
				echo "<th width='300px'>Comment</th>";
			echo "</tr>";

			    echo "<input type='hidden' name='form-type' id='form-type' value='edit3'>";

				$strainIndex = 0;

				// Loop through all strains
				foreach ($result as $row) {
					$comment = htmlspecialchars($row['comment']);

					echo "<tr>";
						echo "<td>";
							echo "<a href=index.php?mode=info&myNum=" . $row['tube_id'] . " title='View full info about EL" . $row['tube_id']. "'>EL" . $row['tube_id'] . "</a>";
							echo "<input type='hidden' name='Strain[" . $strainIndex . "]' value='" . $row['tube_id'] . "'>";
						echo "</td>";

						echo "<td>";
							echo $row['status'] . "<br><br>";
							echo "<select id='status1' name='Status[" . $strainIndex . "]' style='width: 100%'>";
							foreach ($tubestatuses as $row1) {
								echo '<option value="' . $row1['status'] . ($row['status']==$row1['status'] ? '" selected="selected">' : '">') . $row1['status'] . '</option>';
							}
							echo "</select>";
						echo "</td>";

						echo "<td>";
							echo "<textarea type='text' rows=3 cols=40 name='Comment[" . $strainIndex . "]'>" . $row['comment'] . "</textarea>\n";
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
