<?php
	if (isset($_POST["id"]) && isset($_POST["codigo"]) && isset($_POST["password"])) {
		include 'Conexion.php';
		$password = $_POST["password"];
		
		$con = ConexionDeFBE();
		$sql = "update Usuario set password = ".$con->validar(md5($password))." where id = ".$con->validar($_POST["id"]);
		//echo $sql."<br/>";
		$con->EjecutarSQL($sql);
		$sql = "delete from rec_p where id = ".$con->validar($_POST["id"]);
		//echo $sql."<br/>";
		$con->EjecutarSQL($sql);

		$result = array('success' => 1, 'mensaje' => 'Contrseña cambiada correctamente');
		$con->close();
	}else{
		$result = array('success' => 0, 'mensaje' => 'Hubo un error. Intentelo mas tarde');
	}
	echo json_encode($result);
?>