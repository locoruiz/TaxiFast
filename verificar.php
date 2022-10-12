<?php	
	include 'inicioW.php';
	$correo = $_POST["correo"];
 	try{
		$result = $con->Select("Select * from Usuario where correo = ".$con->validar($correo));
		$filas = $result->rowCount();
		if ($filas > 0) {
	 		$fila = $result->fetch(PDO::FETCH_ASSOC);
	 		echo '{"success":"1", "nombre":"'.$fila["nombre"].'", "apellidos":"'.$fila["apellidos"].'", "telefono":"'.$fila["telefono"].'", "id":"'.$fila["id"].'", "esTaxi":"'.$fila["esTaxi"].'"}';
	 	}else{
	 		echo "{\"success\":\"2\"}";
	 	}
 	}catch(Exception $e){
		echo '{"success":"0", "msj":"'.$e->getMessage().'"}';
 	}
	$con->close();
?>