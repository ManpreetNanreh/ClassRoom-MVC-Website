<?php
//So we don't have to deal with unset $_REQUEST['user'] when refilling the form
$_REQUEST['user'] = !empty($_REQUEST['user']) ? $_REQUEST['user'] : '';
$_REQUEST['password'] = !empty($_REQUEST['password']) ? $_REQUEST['password'] : '';
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset = "utf-8">
		<title>Registration Page</title>
	</head>
	<body>
		<h1>Registration</h1>
		<form method="post">
			<table style="width:100%">
				<tr><td>Username: <input type="text" name="username" /></td></tr>
				<tr><td>Password: <input type="password" name="password" /></td></tr>
				<tr><td>Confirm Password: <input type="password" name="confirmpassword" /></td></tr>
				<tr><td>First Name: <input type="text" name="firstname" /></td></tr>
				<tr><td>Last Name: <input type="text" name="lastname" /></td></tr>
				<tr><td>Email: <input type="email" name="email" /></td></tr>
				<tr><td>Type:
					<input type="radio" name="type" value="S">Student
					<input type="radio" name="type" value="I">Instructor
				</td></tr>
				<tr><td><input type="submit" name="register" value="Register"/></td></tr>
				<tr><td><input type="submit" name="back" value="Back"/></td></tr>
				<tr><td><?php echo(view_errors($errors)); ?></td></tr>
			</table>
		</form>
	</body>
</html>
