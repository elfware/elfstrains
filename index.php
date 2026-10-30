<?php
header('Content-Type: text/html; charset=utf-8');

### Set the default timezone / Jocke
date_default_timezone_set('Europe/Berlin');

// Set cookie lifetime for 8 hours
$lifetime = 8*60*60;
session_set_cookie_params($lifetime);

// Start the session
session_start();
$mode   = $_GET['mode']   ?? '';
$type   = $_GET['type']   ?? '';
$logout = $_GET['logout'] ?? '';
if(isset($_SESSION['timeout'])) {
	$duration = time() - (int)$_SESSION['timeout'];
	if ($duration > $lifetime) {
		$user = '';
		session_destroy();
		header('Location: index.php');
	}
}
// Logout
//$now = time();
//if ( (isset($_SESSION['expire']) && ($now > $_SESSION['expire'])) || ($_GET['logout'] == '1')) {
if($logout == '1') {
	$user = '';
	session_destroy();
	header('Location: index.php');
}
// Connect to the database or die
include("db.php");

// Include all functions
include("functions.php");

// Include all actions for forms
include("actions.php");

include("download.php");

// Troubleshooting
//print("session:<pre class='noPrint'>".print_r($_SESSION, true)."</pre>");

?>

<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta charset="utf-8" />
		<link rel="stylesheet" type="text/css" href="variant.css" media="screen,projection" title="Variant Portal" />
		<link rel="stylesheet" type="text/css" href="print.css" media="print">
		<script src="//code.jquery.com/jquery-1.11.2.min.js"></script>
		<script type="text/javascript" src="js/script.js" ></script>

		<title>Orthanc's basement</title>
	</head>
	<body>
		<div id="container" >
			<div id="toplinks" class='noPrint'>
				<p>
					Welcome to the machine. Today is <?php echo date('M d, Y'); ?>.<br>

					<?php
					if (!(isset($_SESSION['login']) && $_SESSION['login'] != '')){
						echo "You are not logged in.";
					} else {
						echo "You are currently logged in as <strong>" . $_SESSION['user'] . "</strong>.";
						echo "<br>";
					    echo "If you are not " . $_SESSION['user'] . ", please <a href='index.php?logout=1'> Log out</a>.";
					}
					?>
				</p>
			</div>

			<div id="logo" class='noPrint'>
				<h1><a href="index.php">Orthanc's basement</a></h1>
				<p><br></p>
			</div>

			<div class='noPrint'>
				<h2 class="hide">Site menu:</h2>
				<ul id="navitab">
					<li><a class="<?php echo ($mode == 'search' && $type == 'word' ? 'current' : NULL); ?>" href="index.php?mode=search&amp;type=word">Search strains</a></li>
					<li><a class="<?php echo ($mode == 'search' && $type == 'number' ? 'current' : NULL); ?>" href="index.php?mode=search&amp;type=number">List strains</a></li>


					<?php if(in_array(($_SESSION['Usertype'] ?? ''), array('Superuser','Admin'))){ ?>
						<li><a class="<?php echo ($mode == 'add' ? 'current' : NULL); ?>" href="index.php?mode=add&amp;Line=<?php echo ($_SESSION['Line'] ?? 1);?>">Add strain(s)</a></li>
					<?php } ?>
					<?php if(($_SESSION['Usertype'] ?? '') == 'Admin'){ ?>
						<li><a class="<?php echo ($mode == 'addUser' ? 'current' : NULL); ?>" href="index.php?mode=addUser">Add User(s)</a></li>
					<?php } ?>
					<li><a class="<?php echo ($mode == 'guidelines' ? 'current' : NULL); ?>" href="index.php?mode=guidelines">Guidelines</a></li>
					<li><a class="<?php echo ($mode == 'popStrains' ? 'current' : NULL); ?>" href="index.php?mode=popStrains">Popular Strains</a></li>
				</ul>
			</div>
			<div id="desc" class='noPrint'>
				<div class="splitleft">
					<h2>Welcome to the Elflab strain database!</h2>
					<p>This is where only the good <i>E. coli</i> strains are...</br></p>
				</div>
				<hr>
			</div>

			<div id="main">
				<p id="js-disabled" class="validation-fail">No JS detected! Some functionality disabled!</p>
<?php $today = date ('Y-m-d H:i:s'); ?>
				<?php
				$value = $mode;

				if (($_SESSION['login'] ?? '') != '1') {
					include("login.php");
				} else {
					if (($value == 'search') && ($_GET['error'] ?? '')  == 'error') {
						echo "<span style='color: red'><strong>You need to provide at least one keyword!</strong></span><br>";
						include("search.php");
					}
					elseif (($value == 'search') && (($_POST['isPosted'] ?? '') == 'TRUE' || ($_GET['search'] ?? '') == "1")) {
						include("search.php");
						include("search1.php");
					}
					elseif (($value == 'search') && (($_POST['isPosted']  ?? '') != 'TRUE')) {
						include("search.php");
					}
					elseif ($value == 'myNum') {
						include("search1.php");
					}
					elseif (($value == 'myList') && (!empty($_POST['show']))) {
						include("search1.php");
					}
					elseif (($value == 'myList') && (!empty($_POST['edit']))) {
						include("edit.php");
					}
					elseif (($value == 'myList') && (!empty($_POST['edit_status']))) {
						include("edit_status.php");
					}
					elseif (($value == 'myList') && (!empty($_POST['print']))) {
						include("print.php");
					}
					elseif ($value == 'add') {
						include("insert.php");
					}
					elseif ($value == 'add3') {
						include("search1.php");
					}
					elseif ($value == 'edit2') {
						include("search1.php");
					}
					elseif ($value == 'edit3') {
						include("search1.php");
					}
					elseif (($value == 'addUser') && (($_GET['save'] ?? '') != 'success')) {
						include("signup.php");
					}
					elseif ($value == 'guidelines') {
						include("guidelines.htm");
					}
					elseif ($value == 'popStrains') {
						//include("popstrains.html");
					}
					elseif ($value == 'misc') {
						include("misc.html");
					}
					elseif ($value == 'info') {
						include("info.php");
					}
					elseif ($value == 'log') {
						include("log.php");
					}
					elseif ($value == 'upload') {
						include("info.php");
					}
					else {
						include("search.php");
					}
									}
				?>

				<div class='noPrint'>
					<br><br>
					<p class="block" <?php echo ((!isset($helpmessage) || ($helpmessage==''))?'hidden':'');?>><strong>Please note:</strong> <?php echo ($helpmessage ?? ''). " " . ($helpmessage2 ?? ''); ?></p>
				</div>
			</div> <!-- id="main" -->

			<div class='noPrint'>
				<div id="footer">
					<p>2011 &middot; Joakim Näsvall &middot; This page was last updated 2026-10-27 by Spartak Zikrin.</p>
				</div>
			</div>
		</div> <!-- id="container" -->
	</body>
</html>
