<?php
	session_start();
	header('Cache-control: private');
	include "Conexion.php";
	$con = ConexionDeFBE();
	if(!$con){
		$resultado = array('success' => 0,
                        'mensaje' => 'No pudo conectar: '.$pdo_error);
    	echo json_encode($resultado);
    	exit() or die();
	}
	$usu = $_POST["usuario"];
	$pas = $_POST["password"];

	$result = $con->Select("select password from Admin where usuario = ".$con->validar($usu));
	$result = $result->fetch(PDO::FETCH_ASSOC);
	if($pas == $result["password"]){
		$resultado = array('success' => 1,
                        'mensaje' => 'bien');
    	echo json_encode($resultado);

		$_SESSION["usuario"] = $usu;
		$_SESSION["token"] = md5(uniqid(mt_rand(), true));
	}else{
		$resultado = array('success' => 0,
                        'mensaje' => 'Password incorrecto');
    	echo json_encode($resultado);
	}
	$con->close();
?>