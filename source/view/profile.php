<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Profile Page</title>
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
			<header><h1>Profile</h1></header>
			<form method="post">
				<fieldset>
					<legend>Edit Profile</legend>
					<p>Username: <?php echo($_SESSION['username']);?></p>
					<p>Password: <input type="password" name="password" /></p>
					<p>First Name: <input type="text" name="firstname" value=<?php echo $_SESSION['firstname']?> /></p>
					<p>Last Name: <input type="text" name="lastname" value=<?php echo $_SESSION['lastname']?> /></p>
					<p>Email: <input type="email" name="email" value=<?php echo $_SESSION['email']?> /></p>
					<p>Type: <?php if($_SESSION['identity'] == 'S'){ 
									echo("Student");
								}elseif($_SESSION['identity'] == 'I'){
									echo("Instructor");
								}?></p>
					<p><input type="submit" name="update" value="Update" /></p>
					<p><?php echo(view_errors($errors));?></p>
				</fieldset>
			</form>
		</main>
	</body>
</html>
