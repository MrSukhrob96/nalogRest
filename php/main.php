<?php	
	require("./index.php");
	
	// require("./functions.php");

	$_POST = json_decode(file_get_contents("php://input"),true);
	    
	$inn = $_POST['inn'];
	$postKey = $_POST['postKey'];


	if ($postKey === 1)
	{
		firstPostCloseAcct($inn);
	}

	if ($postKey === 2){
		secondPostCloseAcct($_POST);
	}

	if ($postKey === 3){
		firstPostCreateAcct($inn);
	}

	if ($postKey === 4){
		secondPostCreateAcct($_POST);
	}