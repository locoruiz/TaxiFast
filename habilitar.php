<?php
	include 'inicioW.php';
	if (isset($_SESSION["usuario"])) {
		date_default_timezone_set('America/La_Paz');
		$date = date('Y-m-d');
		$id = $_POST["id"];
		$fechaFin = strtotime("+30 days", strtotime($date));
		try{
			$con->EjecutarSQL("update Taxista set fechaInicio = '".$date."', fechaFin = '".date("Y-m-d", $fechaFin)."',
							activo = 1 where id = ".$con->validar($id));
		}catch(Exception $e){
		    echo 'Hubo un error. '.$e->getMessage();
		}
	}else
		echo "debe estar logueado para habilitar un taxi";
	$con->close();
?>