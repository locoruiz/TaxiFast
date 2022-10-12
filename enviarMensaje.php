<?php
	include 'inicio.php';
	$dataset = (object)array();
	$dataset->success = 1;
	$dataset->mensaje = '';
	try{
		$idDest = $_POST["id"];
		$idCarrera = $_POST["id_carrera"];
		$sql = "insert into Chat (id_recibe, id_envia, id_carrera, mensaje) values (".
									$con->validar($idDest).", ".
									$id.", ".$con->validar($idCarrera).", ".
									$con->validar($_POST["mensaje"]).")";
		$result = $con->EjecutarSQL($sql);
		$dataset->mensaje = $_POST["mensaje"];
		$dataset->id_msj = $result;
	}catch(Exception $e){
		$dataset->success = 0;
		$dataset->mensaje = $e->getMessage();
	}
	print json_encode($dataset);
?>