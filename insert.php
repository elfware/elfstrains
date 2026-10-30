<?php

// Only allow insert if the user is a Superuser
if(in_array($_SESSION['Usertype'], array('Superuser','Admin'))) {

	$helpmessage = "Genotype field can not be empty.";

	echo '<form action="index.php?mode=add3" name="frmAdd" method="post">';
		echo "<p>";
			echo "Number of strains to add: ";
			echo '<select class="droplist" name="menu1">';

				for($i = 1; $i <= 50; $i++) {
					if($_GET["Line"] == $i){
						$sel = "selected";
					} else {
						$sel = "";
					}

					echo '<option value="' . $i . '" ' . $sel . '>' . $i . '</option>';
				}

			echo "</select>";
			echo '<input type="submit" name="update_lines" value="Update">';
		echo "</p>";
		echo "<input type='hidden' name='form-type' id='form-type' value='add'>";
?>
<p Title='Move the mouse pointer over the field you need help with.'>For help, please see the mouseover tooltips and read the <a href="index.php?mode=guidelines" target="_blank">Guidelines</a>.</br></br>
	<?php
		// Did we fail validation
		if(isset($_SESSION['saveFail'])) {
			echo '<p class="validation-fail">There are fields with errors.</p>';
			$failedRows = $_SESSION['saveFail'];
		}

		// Print table header
		echo "<table class='insert'>";
			echo "<tr>";
				echo "<th>Genotype</th>";
				echo "<th></th>";
				echo "<th>Comment</th>";
			echo "</tr>";

			// Fetch number of lines and set it to 1 if not suitable
			$line = intval($_GET["Line"] ?? 1); // by Claude
			//$line = $_GET["Line"];
			if($line <= 0){
				$line = 1;
			}
			$_SESSION["Line"] = $line;

			// Loop through all rows
			for($i = 1; $i <= $line; $i++){
				//unset($failClass);
				$failClass = array('genotype' => '', 'recipient' => '', 'donor' => '', 'labnote' => '');

				// Specify classes for failed fields
				if(isset($failedRows) && array_key_exists($i, $failedRows)){
					if(!$failedRows[$i]['Genotype']){
						$failClass['genotype'] = "validation-fail";
					}

					if(!$failedRows[$i]['Donor']){
						$failClass['donor'] = "validation-fail";
					}

					if(!$failedRows[$i]['Recipient']){
						$failClass['recipient'] = "validation-fail";
					}

				}

				// Print input fields
				echo "<tr id='line-" . $i . "'>";
					echo "<td>";
						echo '<textarea class="' . $failClass['genotype'] . '" Title="An indication of species, strain or isolate, followed by a complete list of mutations compared to the wildtype strain. Use correct genetic nomenclature, and be consistent." rows="7" cols="45" type="text" style="font-size: 14px;" wrap=soft name="txtGenotype[' . $i . '] ">';
							echo $_SESSION["txtGenotype"][$i] ?? '';
						echo "</textarea>";
					echo "</td>";

					echo "<td>";
						echo "<table>";

							echo "<tr>";
								echo "<td align='center'>";
									echo "<div>Recipient:</div>";
									echo '<input  class="' . $failClass['recipient'] . '" Title="This is the parental strain used in your construction or the recipient strain used in transduction, transformation or conjugation." type="text" name="txtRecipient[' . $i . ']" size="5" value="' . ($_SESSION["txtRecipient"][$i] ?? '') . '" />';
								echo "</td>";
							echo "</tr>";
							echo "<tr>";
								echo "<td align='center'>";
									echo "<div>Donor:</div>";
									echo '<input  class="' . $failClass['donor'] . '"   Title="This is only used for strain used as source of any DNA that has been transferred into the parental strain. Leave this field empty if no DNA has been transferred from another strain." type="text" name="txtDonor[' . $i . ']" size="5" value="' . ($_SESSION["txtDonor"][$i] ?? '') . '" />';
								echo "</td>";
							echo "</tr>";
							echo "<tr>";
								echo "<td align='center'>";
									echo "<div>Labnote:</div>";
									echo '<input  class="' . $failClass['labnote'] . '"   Title="This is only for strain related labnote experiment." type="text" name="txtLabnote[' . $i . ']" size="13" maxlength="13" value="' . ($_SESSION["txtLabnote"][$i] ?? '') . '" />';
								echo "</td>";
							echo "</tr>";
						echo "</table>";
					echo '</td>';

					echo "<td>";
						echo '<textarea Title="Everything that does not go into any of the other fields goes here. Should indicate if transduction, conjugation, (linear) transformation or direct selection/experimental evolution was used to construct the strain. If the strain came from another lab, this should be indicated here, including references if possible." rows="7" cols="45" type="text" style="font-size: 14px;" wrap=soft name="txtComment[' . $i . ']">';
							echo $_SESSION["txtComment"][$i] ?? '';
						echo "</textarea>";
					echo "</td>";
				echo "</tr>";
			}
		echo "</table>";

		echo "<p>These will be added under the signature <strong>" . $_SESSION['Signature'] . "</strong>.</p>";
		echo '<input type="hidden" name="txtSignature" value="' . $_SESSION['Signature'] . '">';
		echo "<br />";

		echo '<input type="submit" class="savebutton" name="submit" value="Save">';
		echo '<input type="submit" title="Reset all fields and clear memory" name="reset" value="Clear fields">';
	echo "</form>";
}
else {
	echo "<span style='color: red; font-weight: bold;'>You are not trusted to add new strains!</span>";
}
?>
