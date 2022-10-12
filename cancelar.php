<?php
	include 'inicio.php';
	$dataset = (object)array();
	$dataset->success = 1;
	$dataset->mensaje = '';
	try{
		if(isset($_POST["estado"]) && $_POST["estado"] == "AB"){
			$sql = "update Carrera set estado = 'AB' where id = ".$con->validar($_POST["id_carrera"]);
			$result = $con->EjecutarSQL($sql);
			$dataset->request = 2;
			$dataset->mensaje = 'Carrera actualizada correctamente';
		}else if(isset($_POST["estado"]) && $_POST["estado"] == "TE"){
			// Carrera terminada, guardar en el historico
			$sql = "select * from Carrera where id = ".$con->validar($_POST["id_carrera"]);
			$result = $con->Select($sql);
			$carrera = $result->fetch(PDO::FETCH_ASSOC);
			if($carrera["repetida"] == 0){
				$sql = "insert into Historico values (null, '".$carrera["direccionOrigen"]."', '".$carrera["referencia"]."', ".$carrera["latOrigen"]."
					, ".$carrera["longOrigen"].", ".$carrera["id_usuario"].", ".$carrera["id_taxista"].", CURRENT_DATE)";
				$result = $con->EjecutarSQL($sql);
			}
			$sql = "update Carrera set estado = 'TE' where id = ".$con->validar($_POST["id_carrera"]);
			$result = $con->EjecutarSQL($sql);
			$dataset->request = 1;
			$dataset->mensaje = 'Carrera finalizada correctamente';
		}else{
			$id_carrera = 0;
			if(isset($_POST["id_carrera"]) && $_POST["id_carrera"] > 0)
				$id_carrera = $con->validar($_POST["id_carrera"]);
			else
				$id_carrera = $_SESSION["id_carrera"];
			
			$sql = "select estado from Carrera where id = ".$con->validar($id_carrera);
			$result = $con->Select($sql);
			$carrera = $result->fetch(PDO::FETCH_ASSOC);
			
			if($carrera["estado"] != "HA"){
				// ya tiene taxi, contar canceladas
				$sql = "update Usuario set cancelados = cancelados + 1 where id = ".$id;
				$con->EjecutarSQL($sql);
			}
			
			$sql = "delete from Carrera where id = ".$id_carrera;
			$result = $con->EjecutarSQL($sql);
			$dataset->mensaje = 'Carrera cancelada correctamente';
		}
	}catch(Exception $e){
		$dataset->success = 0;
		$dataset->mensaje = $e->getMessage();
	}

	$con->close();
	print json_encode($dataset);
?>