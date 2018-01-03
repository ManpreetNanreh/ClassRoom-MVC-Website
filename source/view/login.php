<?php
// This prevents unset $_REQUEST['user'] when refilling the form
$_REQUEST['user'] = !empty($_REQUEST['user']) ? $_REQUEST['user'] : '';
$_REQUEST['password'] = !empty($_REQUEST['password']) ? $_REQUEST['password'] : '';
?>

<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset = "utf-8">
		<title>Classroom</title>
	</head>
	<body>
		<header><h1>Welcome to Classroom</h1></header>
		<main>
			<h1>Login</h1>
			<form method="post">
				<fieldset>
					<p>Username: <input type="text" name="user" /></p>
					<p>Password: <input type="password" name="password" /></p>
					<p>Student <input type="radio" name="radio_group_1" value="S" />
					Instructor <input type="radio" name="radio_group_1" value="I" /></p>
					<p><input type="submit" name="login" value="Login" /></p>
					<p><input type="submit" name="registerl" value="Register" /></p>
					<p><?php echo(view_errors($errors)); ?></p>
				</fieldset>
			</form>
		</main>
	</body>
</html>
