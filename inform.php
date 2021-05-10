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
			$dbaccess = createAccessDB();
			getValor(); // pega os valores e coloca em um array chave-valor
			$dbaccess->close();
			header('Content-Type: application/json');
			echo json_encode($contentArray); // transforma array em json e manda ao requerente
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
				case "tudo":
					getLastDado("SELECT id, tempo, T1, T2, T3, T4, T5, T6, T7, T8, T9, T10, vento, luminosidade, radiacao FROM tcc.dados ORDER BY id DESC LIMIT 5", 1);
					break;
				case "tempo":
					getLastDado("SELECT id, tempo FROM tcc.dados ORDER BY id DESC LIMIT 5", 2);
					break;
				case "temperatura":
					getLastDado("SELECT id, tempo, T1, T2, T3, T4, T5, T6, T7, T8, T9, T10 FROM tcc.dados ORDER BY id DESC LIMIT 5", 3);
					break;
				case "radiacao":
					getLastDado("SELECT id, tempo, radiacao FROM tcc.dados ORDER BY id DESC LIMIT 5", 4);
					break;
				case "velocidadeVento":
					getLastDado("SELECT id, tempo, vento FROM tcc.dados ORDER BY id DESC LIMIT 5", 5);
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

	function getLastDado($sqlquery, $arrayType) {
		global $dbaccess, $contentArray;

		$result = $dbaccess->query($sqlquery);
		if ($result->num_rows > 0) {     // output data of each row   
			$contentArray["code"] = 200; 
			while($row = $result->fetch_assoc()) {
				if ($arrayType == 1) { // tudo
					$contentArray["content"][] = array(
						$row["id"] => array($row["tempo"], $row["vento"], $row["luminosidade"], $row["radiacao"], 
					$row["T1"], $row["T2"], $row["T3"], $row["T4"], $row["T5"], $row["T6"], $row["T7"], $row["T8"], $row["T9"], $row["T10"]));
				}
				else if ($arrayType == 2) { // tempo
					$contentArray["content"][] = array($row["id"] => array($row["tempo"]));
				}
				else if ($arrayType == 3) { // temperatura
					$contentArray["content"][] = array($row["id"] => array($row["tempo"], $row["T1"], $row["T2"], $row["T3"], $row["T4"], $row["T5"], $row["T6"], $row["T7"], $row["T8"], $row["T9"], $row["T10"]));
				}
				else if ($arrayType == 4) { // radiacao
					$contentArray["content"][] = array($row["id"] => array($row["tempo"], $row["radiacao"]));
				} 
				else if ($arrayType == 5) { // velocidadeVento
					$contentArray["content"][] = array($row["id"] => array($row["tempo"], $row["vento"]));
				}
			} 
		}
		else {
			$contentArray = array(
				"code" => 200,
				"content" => "No results"
			); 
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