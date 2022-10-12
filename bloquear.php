<?php
	include 'inicioW.php';
	if (isset($_SESSION["usuario"])) {
		//esta logeado, puede eliminar
		$id = $_POST["id"];
		$valor = $_POST["valor"];
		try{
			$con->EjecutarSQL("update Usuario set bloqueado = ".$con->validar($valor)." where id = ".$con->validar($id));	
		}
		catch(Exception $e){
		    echo 'Hubo un error. '.$e->getMessage();
		}
	}else
		echo "debe estar logueado para eliminar un taxi";
	$con->close();
?>