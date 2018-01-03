<?php
class Database{
	private $dbconn;
	private $dbUser;
	private $dbPass;
	private $dbName;
	private $dbHost;

	function __construct(){
		$this->dbHost = ""; //Database host location.
		$this->dbUser = ""; //Database username.
		$this->dbPass = ""; //Database password.
		$this->dbName = ""; //The database name in use.

		try{
			$this->dbconn = new PDO("mysql:host=$this->dbHost;dbname=$this->dbName",$this->dbUser,$this->dbPass);
			$this->dbconn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->dbconn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		}catch(PDOException $e){
			echo "Connection Failed";
		}
	}

	//This method attempts to register the user into the database.
	public function registerUser($username, $pass, $fname, $lname, $email, $role){
		$finalresult = null;

		//Hash the password using the function provided by php.
		$h_pass = password_hash($pass, PASSWORD_DEFAULT);
		
		if($role != 'S' && $role != 'I'){
			$finalresult = 500;
		}

		//Create the prepare statement to execute insert action.
		$statement = $this->dbconn->prepare("INSERT INTO userinfo(uname, pass, firstname, lastname, email, identity) VALUES (:username, :pass, :fname, :lname, :email, :identity)");
		$statement->bindParam(':username', $username);
		$statement->bindParam(':pass', $h_pass);
		$statement->bindParam(':fname', $fname);
		$statement->bindParam(':lname', $lname);
		$statement->bindParam(':email', $email);
		$statement->bindParam(':identity', $role);

		//Try to enter new user info and see if they already exists or not.
		try{
			//Execute the prepare statement.
			$statement->execute();
			$finalresult = 200;
		}catch (PDOException $e){
			if($e->errorInfo[1] == 1062){
				$finalresult = 409;
			}else{
				$finalresult = 500;
			}
		}
		$this->closeDbConnection();
		return $finalresult;
	}

	//This function checks the user information provided and determines if they can
	//login or not.
	public function checkUserInfo($username, $pass, $identity){
		$finalresult = null;

		//Setup the prepare statement for the select query.
		$statement = $this->dbconn->prepare("SELECT * FROM userinfo WHERE uname=:username");
		$statement->bindParam(':username', $username);

		try{
			//Execute the prepare statement.
			$statement->execute();
			//Check how many rows were obatined to see if the user exists or not.
			if($statement->rowCount() == 1){
				//Means we found the user and proceed to check their password.
				$row = $statement->fetch(PDO::FETCH_ASSOC);
				
				//Successful login is when the username, password and identity match.
				if(password_verify($pass, $row['pass']) && $identity==$row['identity']){
					//Password and identity matches.
					$output['firstname'] = $row['firstname'];
					$output['lastname'] = $row['lastname'];
					$output['email'] = $row['email'];
					$finalresult = $output;
				}else{
					//Password Does not match.
					$finalresult = 404;
				}	
			}else{
				//Means we did not find the user.
				$finalresult = 404;
			}
		}catch (PDOException $e){
			//Either user does not exists or some unknown error happened.
			$finalresult = 500;
		}

		$this->closeDbConnection();
		return $finalresult;
	}

	//This function take user information and updates it into the database.
	public function updateInfo($username, $pass, $firstname, $lastname, $email){
		$finalresult = null;

		//Hash the password.
		$h_pass = password_hash($pass, PASSWORD_DEFAULT);

		//Setup prepare statement to update the user's information.
		$statement = $this->dbconn->prepare("UPDATE userinfo SET pass=:h_pass, 
					firstname=:firstname, lastname=:lastname, email=:email WHERE uname=:username");
		$statement->bindParam(':h_pass', $h_pass);
		$statement->bindParam(':firstname', $firstname);
		$statement->bindParam(':lastname', $lastname);
		$statement->bindParam(':email', $email);
		$statement->bindParam(':username', $username);

		try{
			//Execute the prepare statement.
			$statement->execute();
			$finalresult = $statement->rowCount();
		}catch (PDOException $e){
			$finalresult = 500;
		}
		$this->closeDbConnection();
		return $finalresult;
	}

	//This function obtains the classes the user is enroled in.
	public function getClasses($username){
		$finalresult = null;

		//Prepare statement to select the classes for a certain user.
		$statement = $this->dbconn->prepare("SELECT classname FROM userclass WHERE uname=:username");
		$statement->bindParam(':username', $username);

		//Attempt to execute the prepare statement.
		try{
			$statement->execute();
			$rows =	$statement->fetchAll(PDO::FETCH_ASSOC);
			//If zero classes were obtained.
			if($statement->rowCount() == 0){
				$finalresult = 404;
			}
			$finalresult = $rows;
			
		}catch(PDOException $e){
			$finalresult = 500;
		}

		$this->closeDbConnection();
		return $finalresult;
	}

	//This function takes the username and a classname. Then it creates that class and registers
	//it to that user. This function applies to the instructors only.
	public function createClass($username, $classname){
		$finalresult = null;

		//Prepare statement to create the class.
		$statement1 = $this->dbconn->prepare("INSERT INTO classinfo(classname, Y, N) VALUES(:classname, 0, 0)");
		$statement1->bindParam(':classname', $classname);

		//Prepare statement to register which user is associated with the class.
		$statement2 = $this->dbconn->prepare("INSERT INTO userclass(uname, classname) VALUES (:username, :classname)");
		$statement2->bindParam(':username', $username);
		$statement2->bindParam(':classname', $classname);

		//Prepare statement if needed to delete the class information from the class table.
		$statement3 = $this->dbconn->prepare("DELETE FROM classinfo WHERE classname=:classname");
		$statement3->bindParam(':classname', $classname);

		try{
			$statement1->execute();
			try{
				$statement2->execute();
				$finalresult = 200;
			}catch(PDOException $e){
				$statement3->execute();
				$finalresult = 404;
			}
		}catch(PDOException $e){
			$finalresult = 500;
		}

//		$this->closeDbConnection();
		return $finalresult;
	}

	//This function takes the classname and username. Then it registers that user to that class.
	//This function applies to students only.
	public function joinClass($username, $classname){
		$finalresult = null;

		//Prepare statement to join the student into the class.
		$statement = $this->dbconn->prepare("INSERT INTO userclass(uname, classname) SELECT :username, classname FROM classinfo WHERE classname=:classname");
		$statement->bindParam(':username', $username);
		$statement->bindParam(':classname', $classname);

		try{
			$statement->execute();
			if($statement->rowCount() == 1){
				$finalresult = 200;
			}else{
				$finalresult = 404;
			}
		}catch(PDOException $e){
			$finalresult = 500;
		}

//		$this->closeDbConnection();
		return $finalresult;
	}

	//This function obtains the votes a certain class has.
	public function getClassVotes($classname){
		$finalresult = null;

		//Prepare statement to get the class votes.
		$statement = $this->dbconn->prepare("SELECT Y, N FROM classinfo WHERE classname=:classname");
		$statement->bindParam(':classname', $classname);

		//Attempt to execute the prepare statement.
		try{
			$statement->execute();
			$row = $statement->fetch(PDO::FETCH_NUM);
			if($statement->rowCount() == 0){
				$finalresult = 404;
			}else{
				$finalresult = $row;
			}
		}catch(PDOException $e){
			$finalresult = 500;
		}

		return $finalresult;
	}

	//This function takes the user vote and registers it to that class. Applies only to students.
	public function registerVote($classname, $choice){
		$finalresult = 200;

		if($choice == 'Y'){
		//Query if the student picked yes.
			$statement = $this->dbconn->prepare("UPDATE classinfo SET Y=Y+1 WHERE classname=:classname");
		}elseif($choice == 'N'){
		//Query is the student picked no.
			$statement = $this->dbconn->prepare("UPDATE classinfo SET N=N+1 WHERE classname=:classname");
		}else{
		//If some other choice is sent or something unexpected happens.
			$finalresult = 500;
		}

		//Attempt to execute the prepare statement.
		try{
			$statement->bindParam(':classname', $classname);
			$statement->execute();
		}catch(PDOException $e){
			$finalresult = 500;
		}

		$this->closeDbConnection();
		return $finalresult;
	}

	//This function resets the votes for a specific class.
	public function resetVotes($classname){
		$finalresult = 200;
		$statement = $this->dbconn->prepare("UPDATE classinfo SET Y=0, N=0 WHERE classname=:classname");
		$statement->bindParam(':classname', $classname);

		try{
			$statement->execute();
		}catch(PDOException $e){
			$finalresult = 500;
		}
		$this->closeDbConnection();
		return $finalresult;

	}

	//This function closes the database connection.
	public function closeDbConnection(){
		$this->dbconn = null;
	}
}

?>
