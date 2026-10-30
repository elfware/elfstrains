<?php
$myNum = $_GET['myNum'];

$sql = "SELECT s.*, u.signature
		FROM strain_information as s		
		INNER JOIN users as u 
			ON s.user_id=u.id 
		WHERE s.tube_id = :myNum
		ORDER BY date ASC";

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
			echo "<td>" . $row['genotype'] . "</td>";
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
			echo "<td>" . $row['comment'] . "</td>";
			echo "<td>" . $row['signature'] . "</td>";
			echo "<td>" . $row['labnotes_id'] . "</td>";
			echo "</tr>";
		}
	echo "</table>";
}
?>
