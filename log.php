<?php

// Show strain changelog
if ($_GET['type'] == 'strain') {
	$myNum = $_GET['myNum'];

	$sql = "SELECT s.*, u.signature
			FROM strain_information as s
			INNER JOIN stock_tube as t
				ON s.tube_id=t.id
			INNER JOIN users as u
				ON s.user_id=u.id
			WHERE s.tube_id = :myNum
			ORDER BY date DESC";

	$stmt = $dbh->prepare($sql);
	$stmt->bindParam(":myNum", $myNum);
	$stmt->execute();

	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

	if (count($result) > 0) {
		echo "Strain change history for tube ";
		echo "<a href=index.php?mode=info&myNum=" . $myNum . " title='View full info about EL" . $myNum . "'>EL" . $myNum . "</a>";
		echo "<br></br>";
		echo "<table class='sample'>";
			echo "<tr>";
				echo "<th style='width:1*'>Date</th>";
				echo "<th style='width: 300px'>Genotype</th>";
				echo "<th>Recipient</th>";
				echo "<th>Donor</th>";
				echo "<th style='width: 300px'>Comment</th>";
				echo "<th>Signature</th>";
				echo "<th>Labnote</th>";
			echo "</tr>";
			foreach ($result as $row) {
				echo "<tr>";
				echo "<td>" . $row['date'] . "</td>";
				echo "<td>" . htmlspecialchars($row['genotype']) . "</td>";
				if ($row['recipient'] == "0" || $row['recipient'] == "") {
					echo "<td></td>";
				}
				else {
					echo "<td>EL" . $row['recipient'] . "</td>";
				}
				if ($row['donor'] == "0" || $row['donor'] == "") {
					echo "<td></td>";
				}
				else {
					echo "<td>EL" . $row['donor'] . "</td>";
				}
				echo "<td>" . htmlspecialchars($row['comment']) . "</td>";
				echo "<td>" . $row['signature'] . "</td>";
				echo "<td>" . $row['labnotes_id'] . "</td>";
				echo "</tr>";
			}
		echo "</table>";
	} else {
		echo "<span style='color: red'>Sorry, no matches. Try again</span>";
	}
}

// Show status changelog
if ($_GET['type'] == 'status') {
	$myNum = $_GET['myNum'];

	$sql = "SELECT ts.*, u.signature, tsc.status
			FROM tube_status as ts
			INNER JOIN stock_tube as t
				ON ts.tube_id=t.id
			INNER JOIN tube_status_categories as tsc
				ON ts.category_id=tsc.status
			INNER JOIN users as u
				ON ts.user_id=u.id
			WHERE ts.tube_id = :myNum
			ORDER BY date DESC";

	$stmt = $dbh->prepare($sql);
	$stmt->bindParam(":myNum", $myNum);
	$stmt->execute();

	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

	if (count($result) > 0) {

		echo "Status change history for tube ";
		echo "<a href=index.php?mode=info&myNum=" . $myNum . " title='View full info about EL" . $myNum . "'>EL" . $myNum . "</a>";
		echo "<br></br>";
		echo "<table class='sample' width='100%'>";
			echo "<tr>";
				echo "<th width='15%'>Date</th>";
				echo "<th>Status</th>";
				echo "<th>Signature</th>";
				echo "<th width = '50%'>Comment</th>";
			echo "</tr>";

			foreach ($result as $row) {
				echo "<tr>";
					echo "<td>" . $row['date'] . "</td>";
					echo "<td>" . $row['status'] . "</td>";
					echo "<td>" . $row['signature'] . "</td>";
					echo "<td>" . $row['comment'] . "</td>";
				echo "</tr>";
			}
		echo "</table>";
	} else {
		echo "<span style='color: red'>Sorry, no matches. Try again</span>";
	}
}

// Show changelog for genbank files
if ($_GET['type'] == 'genbank') {
	$myNum = $_GET['myNum'];

	$sql = "SELECT g.*, u.signature
			FROM genbank as g
			INNER JOIN stock_tube as t
				ON g.tube_id=t.id
			INNER JOIN users as u
				ON g.user_id=u.id
			WHERE g.tube_id = :myNum
			ORDER BY date DESC";

	$stmt = $dbh->prepare($sql);
	$stmt->bindParam(":myNum", $myNum);
	$stmt->execute();

	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

	if (count($result) > 0) {

		echo "Status change history for tube ";
		echo "<a href=index.php?mode=info&myNum=" . $myNum . " title='View full info about EL" . $myNum . "'>EL" . $myNum . "</a>";
		echo "<br></br>";
		echo "<table class='sample'>";
			echo "<tr>";
				echo "<th width=150px>Date</th>";
				echo "<th width=400px>Filename</th>";
				echo "<th width=200p>Signature</th>";
			echo "</tr>";

			foreach ($result as $row) {
				echo "<tr>";
					echo "<td>" . $row['date'] . "</td>";
					echo "<td><a href='./uploads/" . $row['filename'] . "'>" . $row['filename']. "</a></td>";
					echo "<td>" . $row['signature'] . "</td>";
				echo "</tr>";
			}
		echo "</table>";
	} else {
		echo "<span style='color: red'>Sorry, no matches. Try again</span>";
	}

}
?>
