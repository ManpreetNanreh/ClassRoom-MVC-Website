<?php
	//This function that helps with view generation
	
	//This function returns the errors in a standard format
	function view_errors($errors){
		$s = "";
		foreach($errors as $key=>$value){
			$s .= "<br/> $value";
		}
		return $s;
	}
?>
