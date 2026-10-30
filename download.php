<?php
//DOWNLOAD GENBANK FILE
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['form-type'] == 'Genbank file'){
	$file = basename($_GET['file']);
	$file = './uploads/' . $file;
	if (!file_exists($file)) {
		die('file not found');
	} else {
		header("Cache-Control: public");
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=$file");
		header("Content-Type: text/plain");
		header("Content-Transfer-Encoding: binary");
		readfile($file);
	}
}
?>
