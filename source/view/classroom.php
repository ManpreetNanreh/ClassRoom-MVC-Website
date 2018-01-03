<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Instructor Classroom</title>
	</head>
	<body>
		<header><h1>Menu</h1></header>
		<nav>
			<ul>
				<li><a href="?action=classroom">Classroom</a></li>
				<li><a href="?action=profile">Profile</a></li>
				<li><a href="?action=logout">Logout</a></li>
			</ul>
		</nav>
		<main>
			<form method="get">
				<h1>Current Classes</h1>
				<?php echo createSelectBox($database, $_SESSION['username']);?>
				<p><?php echo(view_errors($errors)); ?></p>
				<?php echo showjoinorcreateInterface($_SESSION['identity']);?>
				<p><?php echo(view_errors($message)); ?></p>
			</form>
		</main>
	</body>
</html>
