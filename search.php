<?PHP
// Limit the number of entries shown in the table:
if(isset($_GET['limit'])){
	$limit = $_GET['limit'];
}
elseif(isset($_POST['limit'])) {
	$limit = $_POST['limit'];
}
elseif(isset($_SESSION['limit'])) {
	$limit = $_SESSION['limit'];
}
else {
	$limit = 100;
}

if (isset($_GET['page'])) {
	$page = ($_GET['page']);
} else {
	$page = 1;
}

// Set variables for min and max number as well as signature, in order Get, Post, Session.
if(($_GET['search'] ?? '') == 1){
	$minNum = ($_GET['minNum'] ?? '');
	$maxNum = ($_GET['maxNum'] ?? '');
	$sign1 = ($_GET['sign1'] ?? '');
	$status1 = ($_GET['status1'] ?? '');
        $recipient1 = ($_GET['recipient1'] ?? '');
        $donor1 = ($_GET['donor1'] ?? '');
	$term1 = ($_GET['term1'] ?? '');
	$term2 = ($_GET['term2'] ?? '');
	$term3 = ($_GET['term3'] ?? '');
	$term4 = ($_GET['term4'] ?? '');

	$notterm1 = ($_GET['notterm1'] ?? '');
	$notterm2 = ($_GET['notterm2'] ?? '');
	$notterm3 = ($_GET['notterm3'] ?? '');
	$notterm4 = ($_GET['notterm4'] ?? '');

	$searchgenotype = ($_GET['searchgenotype'] ?? '');
	$searchcomment  = ($_GET['searchcomment'] ?? '');

	if(!isset($_GET['searchgenotype']) && !isset($_GET['searchcomment'])){
		$searchgenotype = 1;
	}

} else {
	if(isset($_POST['minNum'])) {
		$minNum = $_POST['minNum'];
	}
	else {
		$minNum = $_SESSION['minNum'] ?? '';
	}

	if(isset($_POST['maxNum'])) {
		$maxNum = $_POST['maxNum'];
	}
	else {
		$maxNum = $_SESSION['maxNum'] ?? '';
	}

	if(isset($_POST['sign1'])) {
		$sign1 = $_POST['sign1'];
	}
	else {
		$sign1 = $_SESSION['sign1'] ?? '';
	}

	if(isset($_POST['status1'])) {
		$status1 = $_POST['status1'];
	}
	else {
		$status1 = $_SESSION['status1'] ?? '';
	}

	if(isset($_POST['recipient1'])) {
		$recipient1 = $_POST['recipient1'];
	}
	else {
		$recipient1 = $_SESSION['recipient1'] ?? '';
	}

	if(isset($_POST['donor1'])) {
		$donor1 = $_POST['donor1'];
	}
	else {
		$donor1 = $_SESSION['donor1'] ?? '';
	}


	// Terms
	if(isset($_POST['term1'])) {
		$term1 = $_POST['term1'] ?? '';
	}
	else {
		$term1 = $_SESSION['term1'] ?? '';
	}

	if(isset($_POST['term2'])) {
		$term2 = $_POST['term2'];
	}
	else {
		$term2 = $_SESSION['term2'] ?? '';
	}

	if(isset($_POST['term3'])) {
		$term3 = $_POST['term3'];
	}
	else {
		$term3 = $_SESSION['term3'] ?? '';
	}

	if(isset($_POST['term4'])) {
		$term4 = $_POST['term4'];
	}
	else {
		$term4 = $_SESSION['term4'] ?? '';
	}

	// Not-terms
	if(isset($_POST['notterm1'])) {
		$notterm1 = $_POST['notterm1'];
	}
	else {
		$notterm1 = $_SESSION['notterm1'] ?? '';
	}

	if(isset($_POST['notterm2'])) {
		$notterm2 = $_POST['notterm2'];
	}
	else {
		$notterm2 = $_SESSION['notterm2'] ?? '';
	}

	if(isset($_POST['notterm3'])) {
		$notterm3 = $_POST['notterm3'];
	}
	else {
		$notterm3 = $_SESSION['notterm3'] ?? '';
	}

	if(isset($_POST['notterm4'])) {
		$notterm4 = $_POST['notterm4'];
	}
	else {
		$notterm4 = $_SESSION['notterm4'] ?? '';
	}

	// Checkboxes
	if(isset($_POST['check']['genotype'])){
		$searchgenotype = 1;
	}
	else {
		$searchgenotype = $_SESSION['searchgenotype'] ?? '';
	}

	if(isset($_POST['check']['comment'])){
		$searchcomment = 1;
	}
	else {
		$searchcomment = $_SESSION['searchcomment'] ?? '';
	}
}

// Get user signatures
$sql = "SELECT signature FROM users";
$stmt = $dbh->prepare($sql);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get tube statuses
$sql = "SELECT status FROM tube_status_categories";
$stmt = $dbh->prepare($sql);
$stmt->execute();
$tubestatuses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<form name="search" id="search" action="index.php?mode=search&amp;type=<?php echo ($type != '' ? $type : 'word');?>" method="POST">

	<?php

	//Are we in the 'Search' tab?
	if($type == 'word' || $type == '') {
		$searchtype = 'search-text';
		$resettype = 'reset-word';
		// Set Genotype as default or remember the status of the checkboxes in the previous search:
		$helpmessage = "Select which field(s) to search in and type a keyword in at least one of the input boxes.<br>When you click 'Search', your keywords are remembered until you click 'Reset'.";
		$helpmessage2 = "<br><font color='red'>Tip: </font> Use '%' to denote any string of characters, and '_' to denote any single character.";
		if($searchgenotype && !$searchcomment) {
			$check1 = 'checked="checked"';
			$check2 = '';
		}
		elseif($searchgenotype && $searchcomment) {
			$check1 = 'checked="checked"';
			$check2 = 'checked="checked"';
		}
		elseif(!$searchgenotype && $searchcomment) {
			$check1 = '';
			$check2 = 'checked="checked"';
		} else {
			$check1 = 'checked="checked"';
			$check2 = '';
		}
		?>

		<p>Search in:</p>
		<label for="checkboxGenotype"><input type="checkbox" name="check[genotype]" id="checkboxGenotype" title="Select which fields to search in" value="genotype" <?php echo ($check1); ?>>Genotype</label><br>
		<label for="checkboxComment"><input type="checkbox" name="check[comment]" id="checkboxComment" title="Select which fields to search in" value="comment" <?php echo ($check2); ?>>Comment</label><br>
		<br>


		<table width="100%" border="0" style="table-layout: fixed">
			<colgroup>
				<col style="width: 25%;"/>
				<col style="width: 25%;"/>
				<col style="width: 25%;"/>
				<col style="width: 25%;"/>
			</colgroup>
			<tr>
				<td colspan="4">Include:</td>
			</tr>
			<tr>
				<td><input type="text" style="width: 95%;" title="Tip: use % as wildcard" name="term1" value="<?php echo $term1; ?>"></td>
				<td><input type="text" style="width: 95%;" title="Tip: use % as wildcard" name="term2" value="<?php echo $term2; ?>"></td>
				<td><input type="text" style="width: 95%;" title="Tip: use % as wildcard" name="term3" value="<?php echo $term3; ?>"></td>
				<td><input type="text" style="width: 95%;" title="Tip: use % as wildcard" name="term4" value="<?php echo $term4; ?>"></td>
			</tr>

			<tr>
				<td colspan="4">Exclude:</td>
			</tr>
			<tr>
				<td><input type="text" style="width: 95%;" title="Tip: use % as wildcard" name="notterm1" value="<?php echo $notterm1; ?>"></td>
				<td><input type="text" style="width: 95%;" title="Tip: use % as wildcard" name="notterm2" value="<?php echo $notterm2; ?>"></td>
				<td><input type="text" style="width: 95%;" title="Tip: use % as wildcard" name="notterm3" value="<?php echo $notterm3; ?>"></td>
				<td><input type="text" style="width: 95%;" title="Tip: use % as wildcard" name="notterm4" value="<?php echo $notterm4; ?>"></td>
			</tr>
		</table>
	<?php
	}

	//Are we in the 'List' tab?
	if($type == 'number' or $type == 'advanced') {
		$searchtype = 'search-list';
		$resettype = 'reset-list';
		$helpmessage = "To list a range of strain numbers, use both of the input boxes. To find a single strain, use only one of the input boxes.<br>When you click 'Search', the values are remembered until you click 'Reset'.";
	?>
		<p>List strain numbers:</p>

	    <table width="100%" border="0">
			<colgroup>
				<col style="width: 25%;"/>
				<col style="width: 25%;"/>
				<col style="width: 25%;"/>
				<col style="width: 25%;"/>
			</colgroup>
			<tr>
				<td colspan="4">Between:</td>
			</tr>
	    	<tr>
	    		<td colspan="1"><input type="text" name="minNum" maxlength="5" value="<?php echo $minNum; ?>"></td>
				<td></td>
	    	</tr>
			<tr>
				<td colspan="4">And:</td>
			</tr>
	    	<tr>
	    		<td colspan="1"><input type="text" name="maxNum" maxlength="5" value="<?php echo $maxNum; ?>"></td>
	    	</tr>
		</table>

	<?php
	}

	if($searchtype){
	?>

		<table width="100%" border="0">
			<colgroup>
				<col style="width: 25%;"/>
				<col style="width: 25%;"/>
				<col style="width: 25%;"/>
				<col style="width: 25%;"/>
			</colgroup>
			<tr>
				<td colspan="1">Limit to strains frozen by:</td>
				<td colspan="1">Limit to status:</td>
			</tr>
			<tr>
				<td colspan="1">
					<div class="form-group">
						<select id="sign1" name="sign1" style="width: 100%">
						<option></option>
						<?php foreach ($users as $row) {echo '<option value="' . $row['signature'] . ($row['signature']==$sign1 ? '" selected="selected">' : '">') . $row['signature'] . '</option>';}?>
						</select>
					</div>
				</td>
				<td colspan="1">
					<div class="form-group">
						<select id="status1" name="status1" style="width: 100%">
						<option></option>
						<?php foreach ($tubestatuses as $row) {echo '<option value="' . $row['status'] . ($row['status']==$status1 ? '" selected="selected">' : '">') . $row['status'] . '</option>';}?>
						</select>
					</div>
				</td>
			</tr>
			<tr>
				<td colspan="1">Limit to recipient:</td>
				<td colspan="1">Limit to donor:</td>
				<td colspan="1">Show:</td>
			</tr>
			<tr>
				<td colspan="1">
					<input type="text" style="width: 100%" name="recipient1" value="<?php echo $recipient1; ?>">
				</td>
				<td colspan="1">
					<input type="text" style="width: 100%" name="donor1" value="<?php echo $donor1; ?>">
				</td>
				<td colspan="1">
					<input type="text" name="limit" size=4 maxlength="5" value="<?php echo $limit; ?>">
					<input type="hidden" name="startval" value="<?php echo ($startval ?? 0); ?>">
					<input type="hidden" name="page" value="1">
				</td>
			</tr>

			<tr>
				<td colspan="4">
				    <input type="hidden" name="isPosted" value="TRUE">
		        	<input type='hidden' name='form-type' id='form-type' value="search">
				    <input type="submit" name="<?php echo $searchtype; ?>" value="Search">
					<input type="submit" title="Reset all fields and clear the results table" name="<?php echo $resettype; ?>" value="Reset">
				</td>
			</tr>
		</table>

<?php
	}
	?>

</form>
