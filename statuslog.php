<?php

$myNum = $_GET['myNum'];

$sql = "SELECT ts.*, u.signature, tsc.status 
		FROM tube_status as ts
		INNER JOIN tube_status_categories as tsc
			ON ts.category_id=tsc.status
		INNER JOIN users as u
			ON ts.user_id=u.id
		WHERE ts.tube_id = :myNum
		ORDER BY date ASC";

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
}
?>
