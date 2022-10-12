<?php
	$usu = NULL;
	include 'inicioW.php';

	try{
		$usu = $_SESSION["usuario"];
		$licencia = $_POST["licencia"];
		$telefono = $con->validar($_POST["telefono"]);
		$nombre = $con->validar($_POST["nombre"]);
		$apellidos = $con->validar($_POST["apellidos"]);
		$id = (int)trim($_POST["id"]);
		$correo = $con->validar($_POST["correo"]);
		$modif = $_POST["modif"];
		$fotoA = $con->validar($_POST["fotoA"]);
		$idT = (int)trim($_POST["idT"]);

		$continuar = true;

		$result = $con->Select("Select id from Taxista where licencia = ".$con->validar($licencia));
		$numResults = $result->rowCount();

		 if ($numResults > 0 && $modif == "true") {
		 	$fila = $result->fetch(PDO::FETCH_ASSOC);
		 	if ($idT == $fila["id"]) {
		 		// esta bien, se puede modificar
		 		$continuar = true;
		 	}else{
		 		echo '{"success":"0", "msj":"Ya estaba registrada la licencia:'.$licencia.'"}';
		 		$continuar = false;
		 	}
		 }else if ($numResults > 0 && $modif == "false") {
		 	echo '{"success":"0", "msj":"Ya estaba registrada la licencia:'.$licencia.'"}';
		 	$continuar = false;
		 }
		 if($continuar == true){
		 	if ($id > 0) {
		 		// Ya tenia usuario, actualizar sus datos
		 		$con->EjecutarSQL("update Usuario set nombre = ".$nombre.", apellidos = ".$apellidos.", correo = ".$correo.", telefono = ".$telefono.", esTaxi = 1 where id = ".$id);
		 	}else{
		 		// no tenia usuario, crear uno nuevo
		 		$id = $con->EjecutarSQL("insert into Usuario (nombre, apellidos, correo, telefono, password, esTaxi) values  (".$nombre.", ".$apellidos.", ".$correo.", ".$telefono.", '".md5($licencia)."', 1)");
		 	}

			date_default_timezone_set('America/La_Paz');
			$date = date('Y-m-d');
			$fechaFin = strtotime("+30 days", strtotime($date));
			

			if(empty($_FILES['foto']['name']) || (!file_exists($_FILES['foto']['tmp_name'])) || !is_uploaded_file($_FILES['foto']['tmp_name'])) {
		    	// no upload
		    	if($modif == "true"){
		    		if(trim($fotoA) != ""){
		    			$name = $fotoA;
						$ext = end(explode(".", $name));
						$url = "fotos/".$licencia.".".$ext;
						if (file_exists($fotoA)) {
							rename($fotoA, $url);
						}
		    		}else{
		    			$url = null;
		    		}
		    	}else{
		    		$url = null;
		    	}
			}else{
				if($modif == "true"){
					//eliminar la foto anterior
					if(file_exists($fotoA))
						unlink($fotoA);
				}
				$name = basename($_FILES["foto"]["name"]);
				$coms = explode(".", $name);
				$ext = end($coms);
				$url = "fotos/".$licencia.".".$ext;
		    	move_uploaded_file( $_FILES["foto"]["tmp_name"], $url);
			}
			
			if($modif == "true"){
				$con->EjecutarSQL("update Taxista set licencia = ".$licencia.", foto = ".$con->validar($url)." where id = ".$idT);
				echo "{\"success\":\"2\", \"msj\":\"Actualizado correctamente\"}";
			}else{
				$id = $con->EjecutarSQL("insert into Taxista (id_usuario, licencia, fechaInicio, fechaFin, activo, foto) 
		 									values (".$id.", ".$licencia.", '".$date."', '".date("Y-m-d", $fechaFin)."', 1, ".$con->validar($url).")");
		 		echo '{"success":"1", "msj":"Registrado correctamente", "nombre":"'.$nombre.'", "apellidos":"'.$apellidos.
		 															'", "correo":"'.$correo.'", "telefono":"'.$telefono.'", 
		 															"licencia":"'.$licencia.'", "id":"'.$id.'"}';
			}
		 }
	}catch(Exception $e){
		echo '{"success":"0", "msj":"'.$e->getMessage().'"}';
	}
	$con->close();
?>