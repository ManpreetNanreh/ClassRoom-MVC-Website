<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<?php echo styleDeterminer($_SESSION['identity']);?>
		<title>Class</title>
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
			<?php echo classContent($_SESSION['classname'], $_SESSION['identity'], $_SESSION['votes'], view_errors($errors));?>
		</main>
	</body>
</html>
