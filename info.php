<?php
// info
$myNum = $_GET['myNum'];

$helpmessage = "Click buttons to browse changelog history. <br> You can also upload a genbank file. The valid format is '.gbxxx', i.e. gb followed by at most three literal characters, or '.zip'.";

$sql = "SELECT t.id, s.genotype, s.comment, s.recipient, s.donor, s.labnotes_id, s.date, u.signature, ts.status, g.genbank
		FROM stock_tube as t
		INNER JOIN (
				SELECT s1.*
				FROM strain_information as s1
				INNER JOIN (
						SELECT tube_id, max(date) as maxdate
						FROM strain_information
						WHERE tube_id=:myNum_s
					) as s2
					ON s1.tube_id=s2.tube_id AND s1.date=s2.maxdate
			) as s
			ON t.id=s.tube_id
		INNER JOIN users as u
			ON t.user_id=u.id
 		LEFT JOIN (
				SELECT ts1.tube_id, tsc.status
				FROM tube_status as ts1
				INNER JOIN (
						SELECT tube_id, max(date) as maxdate
						FROM tube_status
						WHERE tube_id=:myNum_u
					) as ts2
					ON ts1.tube_id=ts2.tube_id AND ts1.date=ts2.maxdate
				INNER JOIN tube_status_categories as tsc
					ON ts1.category_id=tsc.id
			) as ts
			ON t.id=ts.tube_id
		LEFT JOIN (
				SELECT g1.tube_id, g1.filename as genbank
				FROM genbank as g1
				INNER JOIN (
						SELECT tube_id, max(date) as maxdate
						FROM genbank
						WHERE tube_id=:myNum_g
					) as g2
					ON g1.tube_id=g2.tube_id AND g1.date=g2.maxdate
			) as g
			ON t.id=g.tube_id
		WHERE t.id=:myNum_t";
$stmt = $dbh->prepare($sql);
if($stmt){
$stmt->bindParam(":myNum_g", $myNum);
$stmt->bindParam(":myNum_s", $myNum);
$stmt->bindParam(":myNum_t", $myNum);
$stmt->bindParam(":myNum_u", $myNum);
$stmt->execute();
} else {
    //$error = $dbh->errno . ' ' . $dbh->error;
    echo print_r($dbh->errorInfo()); // 1054 Unknown column 'foo' in 'field list'
    }

$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($result) > 0) {

	$row = $result[0];
	echo "<table class='sample' width='100%' style='text-align:left'>";
		echo "<col width = '15%'>";
		echo "<col width = '85%'>";
		echo "<tr>";
			echo "<th>Tube</th><td>EL" . $row['id'] . "</td>";
		echo "</tr>";
		echo "<tr>";
			echo "<th>Genotype</th><td>" . htmlspecialchars($row['genotype'])  . "</td>";
		echo "</tr>";
		echo "<tr>";
			echo "<th>Comment</th><td>" . htmlspecialchars($row['comment']) . "</td>";
		echo "</tr>";
		echo "<tr>";
			echo "<th>Recipient</th>";
			echo "<td>";
			if ($row['recipient'] == "0" || $row['recipient'] == "") {
				echo "";
			}
			else {
				echo "EL" . $row['recipient'];
			}
			echo "</td>";
		echo "</tr>";
		echo "<tr>";
			echo "<th>Donor</th>";
			echo "<td>";
			if ($row['donor'] == "0" || $row['donor'] == "") {
				echo "";
			}
			else {
				echo "EL" . $row['donor'];
			}
			echo "</td>";
		echo "</tr>";
		echo "<tr>";
			if ($row['labnotes_id'] == "")
                        	echo  "<th>Labnote</th><td>" . $row['labnotes_id'] . "</td>";
                	else {
                        	echo "<th>Labnote</th><td>" . "<a href=https://www.eln.uu.se/notebook/experiment/" . $row['labnotes_id'] . " target='_blank' title='View labnote " . $row['labnotes_id'] . " in a new tab'>" . $row['labnotes_id'] . "</a>" . "</td>";
                	}
		echo "</tr>";
		echo "<tr>";
			echo "<th>Signature</th><td>" . $row['signature'] . "</td>";
		echo "</tr>";
		echo "<tr>";
			echo "<th>Status</th><td>" . $row['status'] . "</td>";
		echo "</tr>";
		echo "<tr>";
			echo "<th>Genbank</th>";
			echo "<td><a href='./uploads/" . $row['genbank'] . "'>" . $row['genbank']. "</a></td>";
		echo "</tr>";
		echo "<tr>";
			echo "<th>Modified</th><td>" . $row['date'] . "</td>";
		echo "</tr>";
	echo "</table>";
	echo "<br><br>";

	echo "<table style='width:300px' cellpadding='4' cellspacing='0'>";
		echo "<colgroup>";
			echo "<col style='width:100px'/>";
			echo "<col style='width:100px'/>";
			echo "<col style='width:100px'/>";
		echo "</colgroup>";
		if(in_array($_SESSION['Usertype'], array('Superuser','Admin'))) {
			echo "<tr>";
				echo "<form action='index.php?mode=myList' name='selectRow' method='post'>";
					echo "<input type='hidden' name='selected[]' value='" . $myNum . "'/>";
				echo "<td>";
		                	echo "<input type='submit' style='width:100%' title='Edit the selected strains' name='edit' value='Edit strain'/>";
				echo "</td>";
				echo "<td>";
		                	echo "<input type='submit' style='width:100%'title='Edit the selected tubes status' name='edit_status' value='Edit status'/>";
				echo "</td>";
				echo "</form>";
			echo "</tr>";
			echo "<tr><td><br></td></tr>";
		}
		echo "<tr>";
			echo "<td>";
				echo "<form name='log' action='index.php?mode=log&type=strain&myNum=" . $myNum . "' method='post'>";
					echo "<input type='submit' style='width:100%' title='Show strain info history' value='Strain log'/>";
				echo "</form>";
			echo "</td>";
			echo "<td>";
				echo "<form name='log' action='index.php?mode=log&type=status&myNum=" . $myNum . "' method='post'>";
					echo "<input type='submit' style='width:100%' title='Show tube status history' value='Status log'/>";
				echo "</form>";
			echo "</td>";
			echo "<td>";
				echo "<form name='log' action='index.php?mode=log&type=genbank&myNum=" . $myNum . "' method='post'>";
					echo "<input type='submit' style='width:100%' title='Show genbank file history' value='Genbank log'/>";
				echo "</form>";
			echo "</td>";
		echo "<tr>";
	echo "</table>";
	echo '<br><br>';

	// Upload Genbank file
	if (in_array($_SESSION['Usertype'], array('Superuser','Admin'))) {
		echo "<form action='index.php?mode=upload&myNum=" . $myNum . "' method='post' enctype='multipart/form-data'>";
			echo "Select genbank file to upload:";
			echo "<input type='file' name='fileToUpload' id='fileToUpload'>";
			echo "<input type='submit' name='form-type' id='form-type' value='Upload'>";
		echo "</form>";
		if (!empty($success)) {
			echo "<br>";
			echo "<span style='color:#FF4500'>Genbank file is successfully uploaded.</span>";
		}
	}
} else {
	echo "<span style='color: red'>Sorry, no matches. Try again</span>";
}
?>
