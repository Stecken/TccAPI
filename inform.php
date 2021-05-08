<?php
// Connect to database
	include("conectionmysql.php");
	$request_method = $_SERVER["REQUEST_METHOD"];
	$objJsonResult = array(
		"code" => NULL,
		"content" => NULL
	);
	$contentArray;
	
	switch($request_method)
	{
		case 'GET':
			$objJsonResult["code"] = 200;
			header('Content-Type: application/json');
			echo json_encode($objJsonResult);
			break;
		case 'POST':
			//$dbacces = createAccessDB();
			getValor();
			//mysqli_close($dbacces);
			header('Content-Type: application/json');
			echo json_encode($contentArray);
			break;
		default:
			// Invalid Request Method
			header("HTTP/1.0 405 Method Not Allowed");
			header('Content-Type: application/json');
			echo json_encode(array(
				"code" => 405,
				"content" => NULL
			));
			break;
	}

	function getValor(){
		global $contentArray;
		if (isset($_POST["option"])) {
			$option = $_POST["option"];

			switch ($option) {
				case "tempo":
					$contentArray = array(
						"code" => 200,
						"content" => array("aaaaaaaaa" => "a", "aaaaaaaaa" => "a")
					);
					break;
				case "temperatura":
					$contentArray = array(
						"code" => 200,
						"content" => array("aaaaaaaaa" => "a", "aaaaaaaaa" => "a")
					);
					break;
				case "radiacao":
					$contentArray = array(
						"code" => 200,
						"content" => array("aaaaaaaaa" => "a", "aaaaaaaaa" => "a")
					);
					break;
				case "velocidadeVento":
					$contentArray = array(
						"code" => 200,
						"content" => array("aaaaaaaaa" => "a", "aaaaaaaaa" => "a")
					);
					break;
				default:
					$contentArray = array(
						"code" => NULL,
						"content" => "Error: No value correct option"
					);
					break;
			}
		} else {
			$objJsonResult["content"] = "Error: Nonexistent Type Valor";
		}
	}


	//Código Base
	/*
	function get_products($product_id=0)
	{
		global $connection;
		$query="SELECT * FROM products";
		if($product_id != 0)
		{
			$query.=" WHERE id=".$product_id." LIMIT 1";
		}
		$response=array();
		$result=mysqli_query($connection, $query);
		while($row=mysqli_fetch_array($result))
		{
			$response[]=$row;
		}
		header('Content-Type: application/json');
		echo json_encode($response);
	}
	*/

	// Close database connection