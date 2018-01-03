<?php
	require_once "model/database.php";

	session_save_path("sess");
	session_start();

	/*To show errors
	error_reporting(E_ALL);
	ini_set('display_errors', 'On');
	*/

	$database = new Database();

	$errors = array();
	$message = array();
	$view = "";

	//Controller code
	if(!isset($_SESSION['state'])){
		//default state us login page
		$_SESSION['state'] = "login";
	}

	switch($_SESSION['state']){
		//Login case allows user to log into their account.
		case "login":
			//view the login page
			$view="login.php";

			//check if the user wants to create a new account
			if(!empty($_POST['registerl']) && $_POST['registerl'] == "Register"){
				$_SESSION['state'] = "register";
				$view = "register.php";
			}elseif(!empty($_POST['login']) && $_POST['login'] == 'Login'){
			//validate and set errors
				if(empty($_POST['user'])){
					$errors[] = 'Username is required';
				}
				if(empty($_POST['password'])){
					$errors[] = 'Password is required';
				}
				if(empty($_POST['radio_group_1'])){
					$errors[] = 'Pick Student or Teacher';
				}
				if(!empty($errors))break;

				//Make a call to server to check user information.
				$output = $database->checkUserInfo($_POST['user'], $_POST['password'], 
							$_POST['radio_group_1']);
			
				if(is_array($output)){
					//Confirm valid login and allow user to proceed.
					$_SESSION['username'] = $_POST['user'];
					$_SESSION['firstname'] = $output['firstname'];
					$_SESSION['lastname'] = $output['lastname'];
					$_SESSION['email'] = $output['email'];
					$_SESSION['identity'] = $_POST['radio_group_1'];
				}else{
					//Means invalid login or something unexpected happens.
					$errors[] = "Invalid Login, Please try again.";
					break;
				}

				//Set state to profile and direct the user to their profile page.
				$_SESSION['state'] = 'profile';
				$view = "profile.php";
				
			}
			break;

		//Register case which allows the user to make a new account.
		case "register":
			//the view to display by default
			$view = "register.php";
			
			//If the user wants to go back to login page.
			if(!empty($_POST['back']) && $_POST['back'] == "Back"){
				$_SESSION['state'] = 'login';
				$view = "login.php";
			}elseif(!empty($_POST['register']) && $_POST['register'] == 'Register'){
				//validate and set errors
				if(empty($_POST['username']) || ctype_space($_POST['username'])){
					$errors[] = "Username is required";
				}
				if(!preg_match("/^[a-zA-Z ]*$/", $_POST['username'])){
					$errors[] = "Only letter and white space are allowed in the username";
				}
				if(empty($_POST['password']) || ctype_space($_POST['password'])){
					$errors[] = "Password is required";
				}
				if(empty($_POST['confirmpassword']) || ctype_space($_POST['confirmpassword'])){
					$errors[] = "Confirm Password is required";
				}
				if(empty($_POST['firstname']) || ctype_space($_POST['firstname'])){
					$errors[] = "First Name is required";
				}
				if(empty($_POST['lastname']) || ctype_space($_POST['lastname'])){
					$errors[] = "Last Name is required";
				}
				if(empty($_POST['email']) || ctype_space($_POST['email'])){
					$errors[] = "Email is required";
				}
				if(empty($_POST['type'])){
					$errors[] = "Type is required";
				}
				if($_POST['password'] != $_POST['confirmpassword']){
					$errors[]="Password and Confirm Password do not match.";
				}
				if(!empty($errors))break;

				//Enter user information into the database.
				$output = $database->registerUser($_POST['username'], 
							$_POST['password'], $_POST['firstname'], 
							$_POST['lastname'], $_POST['email'], $_POST['type']);

				//Display output depending on whether insert was successful or not.
				if($output == 409){
					$errors[] = "Username already exists";
				}elseif($output == 500){
					$errors[] = "Unknown error, Please Try again!";
				}elseif($output == 200){
					//Create the user session.
					$_SESSION['username'] = $_POST['username'];
					$_SESSION['firstname'] = $_POST['firstname'];
					$_SESSION['lastname'] = $_POST['lastname'];
					$_SESSION['email'] = $_POST['email'];
					$_SESSION['identity'] = $_POST['type'];
					//Set state to profile and let the user view their profile.
					$_SESSION['state'] = 'profile';
					$view = 'profile.php';
				}
			}
			break;

		//This is the profile case where user views their personal profile.
		case "profile":
			$view = 'profile.php';

			//If the user wants to see their classes.
			if(isset($_GET['action']) && $_GET['action'] == 'classroom'){
				//Set the state to classroom and display the classrom page.
				$_SESSION['state'] = 'classroom';
				$view = "classroom.php";
				unset($_GET['action']);
			}elseif(isset($_GET['action']) && $_GET['action'] == 'logout'){
			//If the user wants to logout.
				session_destroy();
				$_SESSION['state'] = 'login';
				$view = "controller.php";
				header("Location:controller.php");
				unset($_GET['action']);
			}elseif(!empty($_POST['update']) && $_POST['update'] == 'Update'){
			//If the user is trying to update information.
				//Check if anything is empty or just spaces.
				if(empty($_POST['password']) || ctype_space($_POST['password'])){
					$errors[] = "Password is required";
				}
				if(empty($_POST['firstname']) || ctype_space($_POST['firstname'])){
					$errors[] = "First Name is required";
				}
				if(empty($_POST['lastname']) || ctype_space($_POST['lastname'])){
					$errors[] = "Last Name is required";
				}
				if(empty($_POST['email']) || ctype_space($_POST['email'])){
					$errors[] = "Email is required";
				}

				if(!empty($errors))break;

				//Update the new user information.
				$output = $database->updateInfo($_SESSION['username'], $_POST['password'], 
							$_POST['firstname'], $_POST['lastname'], $_POST['email']);

				if($output == 1){
					//Update information into the session.
					$_SESSION['firstname'] = $_POST['firstname'];
					$_SESSION['lastname'] = $_POST['lastname'];
					$_SESSION['email'] = $_POST['email'];
				}else{
					$errors[] = "Unexpected Error, Please try again later.";
				}
			}
			break;

		//This is the classroom case where the user can view the classes they are enrolled in
		//and either create or join a new class.
		case "classroom":
			$view = 'classroom.php';

			//If the user wants to see their profile.
			if(isset($_GET['action']) && $_GET['action'] == 'profile'){
				$_SESSION['state'] = 'profile';
				$view = "profile.php";
				unset($_GET['action']);
			}elseif(isset($_GET['action']) && $_GET['action'] == 'logout'){
			//If the user wants to logout.
				session_destroy();
				$_SESSION['state'] = 'login';
				$view = "controller.php";
				header("Location:controller.php");
				unset($_GET['action']);
			}elseif(!empty($_GET['view']) && $_GET['view']=="View"){
			//Send user to view the class they picked.
				if(!empty($_GET['classOption'])){
					$_SESSION['classname'] = $_GET['classOption'];
					$_SESSION['votes'] = $database->getClassVotes($_SESSION['classname']);
					$_SESSION['state'] = 'class';
					$view = 'class.php';
					unset($_GET['action']);
				}else{
					$errors[] = 'Please pick a class.';
				}
			}elseif(!empty($_GET['Join']) && $_GET['Join'] == "Join" && $_SESSION['identity'] == 'S'){
				$message[] = joinOrcreateClass($database, $_SESSION['username'], $_GET['code'], $_SESSION['identity'], 'join');

			}elseif(!empty($_GET['Create']) && $_GET['Create'] == "Create" && $_SESSION['identity'] == 'I'){
				$message[] = joinOrcreateClass($database, $_SESSION['username'], $_GET['code'], $_SESSION['identity'], 'create');
			}
			break;

		//The class case which controls what the user can do when seeing the specific 
		//class content.
		case "class":
			$view = "class.php";
			$_SESSION['votes'] = $database->getClassVotes($_SESSION['classname']);
			//If the user wants to see their profile.
			if(isset($_GET['action']) && $_GET['action'] == 'profile'){
				$_SESSION['state'] = 'profile';
				$view = "profile.php";
				unset($_GET['action']);
			}elseif(isset($_GET['action']) && $_GET['action'] == 'classroom'){
				$_SESSION['state'] = 'classroom';
				$view = "classroom.php";
				unset($_GET['action']);
			}elseif(isset($_GET['action']) && $_GET['action'] == 'logout'){
				//If the user wants to logout.
				session_destroy();
				$_SESSION['state'] = 'login';
				$view = "controller.php";
				header("Location:controller.php");
				unset($_GET['action']);
			}elseif(isset($_GET['action']) && $_GET['action'] == 'Y' && $_SESSION['identity'] == 'S'){
			//If the student wants to vote yes for the class.
				//Register the vote.
				$output = $database->registerVote($_SESSION['classname'], 'Y');
				if($output != 200){
					$errors[] = "Could not register your vote, please try again later.";
				}
			}elseif(isset($_GET['action']) && $_GET['action'] == 'N' && $_SESSION['identity'] == 'S'){
			//If the student wants to vote no for the class.
				//Register the vote.
				$output = $database->registerVote($_SESSION['classname'], 'N');
				if($output != 200){
					$errors[] = "Could not register your vote, please try again later.";
				}
			}elseif(!empty($_GET['reset']) && $_GET['reset']=='Reset Votes' && $_SESSION['identity']=='I'){
				$output = $database->resetVotes($_SESSION['classname']);
				if($output != 200){
					$errors[] = 'Could not reset votes, please try again later.';
				}
			}
			break;
	}

	//This function allows to generate the select box which will show the user their classes.
	function createSelectBox($database, $username){
		//Get the classes from database.
		$_SESSION['classes'] = $database->getClasses($username);
		if($_SESSION['classes'] == 500){
			return "Server Error.";
		}elseif($_SESSION['classes'] == 404){
			return "You have not joined any classes.";
		}

		$output = "<select name='classOption'>";
		foreach($_SESSION['classes'] as $class){
			$output .= "<option value='" . $class['classname']. "'>" . $class['classname'] . '</option>';
		}
		$output .= '</select>';
		$output .=	"<p><input type='submit' name='view' value='View' /></p>";

		return $output;
	}

	//This function decides whether to show the user the create a class interface or 
	//join a class interface.
	function showjoinorcreateInterface($identity){
		//Student should join a class.
		//Teacher should create a class.
		if($identity == 'I'){
			$choice = 'Create';
		}elseif($identity == 'S'){
			$choice = 'Join';
		}else{
			//In case something unexpected happens, then show nothing.
			return;
		}
		$output = "<h1>" . $choice . " a Class</h1>
				<p><input type='text' name='code'/></p>
				<p><input type='submit' name='". $choice . "' value=" . $choice . ' /></p>';
		
		return $output;
	}

	//This function determines which style to display depending on if the class is being
	//shown to student or instructor.
	function styleDeterminer($identity){
		$output = "";
		if($identity == 'I'){
		//For instructor.
			$output = "<style>
			span {
				background-color:green;
				display:block;
				text-decoration:none;
				padding:20px;
				color:white;
				text-align:center;
			}</style>";
		}elseif($identity == 'S'){
		//For students.
			$output = "<style>
			td a{
				background-color:green;
				display:block;
				width:200px;
				text-decoration:none;
				padding:20px;
				color:white;
				text-align:center;
			}
			</style>";
		}
		return $output;
	}

	//This function determines which layout should be shown for the class depending on if the 
	//class is being shown to student or instructor.
	function classContent($classname, $identity, $votes, $error){
		$output = "";
		if($identity == 'I'){
		//For instructor.
			$output = "<h1>" . $classname . "</h1>
			<form method=\"post\">
				<fieldset>
					<legend>" . $classname . "</legend>
					<span style=\"background-color:green; width:" . $votes[0] . "%;\">I Get It</span>
					<span style=\"background-color:red; width:". $votes[1] . "%;\">I Don't Get It</span>
				</fieldset>
				<input type='submit' name='reset' value='Reset Votes' />
			</form>";
		}elseif($identity == 'S'){
		//For student.
			$output = "<h1>" . $classname . "</h1>
			<form method=\"post\">
				<fieldset>
					<legend>" . $classname . "</legend>
					<table style=\"width:100%;\">
						<tr>
							<td><a style=\"background-color:green;\" href=\"?action=Y\">I Get It</a></td>
							<td><a style=\"background-color:red;\" href=\"?action=N\">I Don't Get it</a></td>
						</tr>
					</table>
					<p>" . $error . "</p>
				</fieldset>
			</form>";
		}

		return $output;

	}

	//This function decides whether the user can join or create a class and then either
	//creates the class or lets the user join a class.
	function joinOrcreateClass($database, $username, $code, $identity, $option){
		$message = '';
		$output = '';
		//Check if a class code is supplied.
		if(empty($code) || ctype_space($code)){
			$message = 'Class code is required.';
		}else{
			//If a student is the user then let them join the class.
			if($identity == 'S'){
				$output = $database->joinClass($username, $code);
			}elseif($identity == 'I'){
			//If the user is instructor then let them create the class.
				$output = $database->createClass($username, $code);
			}
			//If successfully done the intended operation then show the success message.
			if($output == 200){
				$message = "Class successfully " . $option . "ed.";
			}else{
			//Otherwise inform the user of the fail message.
				$message = "Could not " . $option . " the class, please try again later.";
			}
		}
		return $message;
	}

	
	require_once "view/view_lib.php";
	require_once "view/$view";
?>
