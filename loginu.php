<?php
	include 'Conexion.php';
	$con = ConexionDeFBE();
	if(!$con){
	    $resultado = array('success' => 0,
	                        'mensaje' => 'No pudo conectar'.$pdo_error);
	    echo json_encode($resultado);
	    exit() or die();
	}

	session_start();

	$dispositivo = 1; // android
	if(isset($_POST["dispositivo"]))
		$dispositivo = $_POST["dispositivo"];
	
	if(isset($_POST["id"])){
		$id = $_POST["id"];
		$con->EjecutarSQL("update Usuario set session_id = '".session_id()."', token = ".$con->validar($_POST["token"]).", dispositivo = ".$dispositivo." where id = ".$con->validar($id));
		$result = $con->Select("Select id, activo from Taxista where id_usuario = ".$con->validar($id));
		$activo = 0;
		$id_taxista = 0;
		if($result->rowCount() > 0){
			// es taxista
			$f = $result->fetch(PDO::FETCH_ASSOC);
			$activo = $f["activo"];
			$id_taxista = $f["id"];
		}

		// Ver si estaba en carrera!!
		$result = $con->Select("select * from Carrera where id_usuario = ".$con->validar($id)." and estado != 'TE'");
		$enCamino = false;
		$f = "";
		if($result->rowCount() > 0){ // estaba esperando un taxi!
			$enCamino = true;
			$f = $result->fetch(PDO::FETCH_ASSOC);
			if((int)$f["id_taxista"] > 0){
				$res = $con->Select("SELECT t.id, t.foto, u.nombre, u.apellidos, u.telefono, t.licencia FROM ".
									" Usuario u, Taxista t where t.id_usuario = u.id and t.id = ".$f["id_taxista"]);
                $taxista = $res->fetch(PDO::FETCH_ASSOC);
                $f["taxista"] = $taxista;
			}
			$_SESSION["id_carrera"] = $f["id"];
		}
		$conPedido = false;
		if(!$enCamino && $id_taxista > 0 && $dispositivo == 1){
			// Ver si estaba en carrera como taxista!
			$result = $con->Select("select * from Carrera where id_taxista = ".$id_taxista." and estado != 'TE'");
			$enCamino = false;
			$f = "";
			if($result->rowCount() > 0){ // estaba con pedido
				$enCamino = true;
				$conPedido = true;
				$f = $result->fetch(PDO::FETCH_ASSOC);
				$_SESSION["id_carrera"] = $f["id"];
			}
		}


		$respuesta = array('success' => 1,
							'id' => $id,
							'sess_id' => session_id(),
							'activo' => $activo,
							'enCamino' => $enCamino,
							'conPedido' => $conPedido,
							'datos' => $f
							);
		echo json_encode($respuesta);
		$_SESSION["cliente"] = $id;
	}else{
		if (isset($_POST["facebook"])) {
			if ($_POST["facebook"] == 1) {
				// hizo login desde facebook, que hacemo

			$uid = $_POST["uid"]; // id de facebook

			$result = $con->Select("select id from Usuario where uid = ".$con->validar($uid));
			$fila = NULL;
			if ($result->rowCount() > 0) {
				$fila = $result->fetch(PDO::FETCH_ASSOC);
				$id = $fila["id"];
				$con->EjecutarSQL("update Usuario set session_id = '".session_id()."', token = ".$con->validar($_POST["token"]).", dispositivo = ".$dispositivo." where id = ".$id);
			}else{
				$id = $con->EjecutarSQL("insert into Usuario (nombre, apellidos, uid, session_id, token, dispositivo) values (".
										$con->validar($_POST["nombre"]).", ".
										$con->validar($_POST["apellidos"]).", ".
										$con->validar($uid).", '".
										session_id()."', ".
										$con->validar($_POST["token"]).", ".$dispositivo.")");
			}

			// Ver si estaba en carrera!!
			$result = $con->Select("select * from Carrera where id_usuario = ".$con->validar($id)." and estado != 'TE'");
			$enCamino = false;
			$f = "";
			if($result->rowCount() > 0){ // estaba esperando un taxi!
				$enCamino = true;
				$f = $result->fetch(PDO::FETCH_ASSOC);
				if((int)$f["id_taxista"] > 0){
					$res = $con->Select("SELECT t.id, t.foto, u.nombre, u.apellidos, u.telefono, t.licencia FROM ".
										" Usuario u, Taxista t where t.id_usuario = u.id and t.id = ".$f["id_taxista"]);
	                $taxista = $res->fetch(PDO::FETCH_ASSOC);
	                $f["taxista"] = $taxista;
				}
				$_SESSION["id_carrera"] = $f["id"];
			}
			// Log in con facebook no puede ser taxista

			$respuesta = array('success' => 1,
								'id' => $id,
								'sess_id' => session_id(),
								'activo' => 0,
								'enCamino' => $enCamino,
								'datos' => $f
								);
				echo json_encode($respuesta);
				$_SESSION["cliente"] = $id;
			}
		}else{
			$correo = $_POST["email"];
			$password = $_POST["password"];

			$result = $con->Select("Select * from Usuario where correo = ".$con->validar($correo)." and password = ".$con->validar(md5($password)));
			$numResults = $result->rowCount();

			if ($numResults == 0) {
				$respuesta = array('success' => 0,
									'mensaje' => 'Usuario y password no coinciden');
				echo json_encode($respuesta);
			}else{
	 			$fila = $result->fetch(PDO::FETCH_ASSOC);
	 			$id = $fila["id"];
	 			$activo = 0;
	 			$id_taxista = 0;
	 			if($fila["esTaxi"] > 0){
	 				$r = $con->Select("select id, activo from Taxista where id_usuario = ".$id);
	 				$f = $r->fetch(PDO::FETCH_ASSOC);
	 				$idTaxista = $f["id"];
	 				$activo = $f["activo"];
	 				$id_carrera = $f["id"];
	 			}else
	 				$idTaxista = 0;

	 			$con->EjecutarSQL("update Usuario set session_id = '".session_id()."', token = ".$con->validar($_POST["token"]).", dispositivo = ".$dispositivo." where id = ".$id);

	 			// Ver si estaba en carrera!!
				$result = $con->Select("select * from Carrera where id_usuario = ".$con->validar($id)." and estado != 'TE'");
				$enCamino = false;
				$f = "";
				if($result->rowCount() > 0){ // estaba esperando un taxi!
					$enCamino = true;
					$f = $result->fetch(PDO::FETCH_ASSOC);
					if((int)$f["id_taxista"] > 0){
						$res = $con->Select("SELECT t.id, t.foto, u.nombre, u.apellidos, u.telefono, t.licencia FROM ".
											" Usuario u, Taxista t where t.id_usuario = u.id and t.id = ".$f["id_taxista"]);
		                $taxista = $res->fetch(PDO::FETCH_ASSOC);
		                $f["taxista"] = $taxista;
					}
					$_SESSION["id_carrera"] = $f["id"];
				}

				$conPedido = false;
				if(!$enCamino && $id_taxista > 0 && $dispositivo == 1){
					// Ver si estaba en carrera como taxista!
					$result = $con->Select("select * from Carrera where id_taxista = ".$id_taxista." and estado != 'TE'");
					$enCamino = false;
					$f = "";
					if($result->rowCount() > 0){ // estaba con pedido
						$enCamino = true;
						$conPedido = true;
						$f = $result->fetch(PDO::FETCH_ASSOC);
						$_SESSION["id_carrera"] = $f["id"];
					}
				}
	 			$respuesta = array('success' => 1,
									'id' => $id,
									'nombre' => $fila["nombre"],
									'apellidos' => $fila["apellidos"],
									'telefono' => $fila["telefono"],
									'esTaxi' => $fila["esTaxi"],
									'idTaxista' => $idTaxista,
									'activo' => $activo,
									'sess_id' => session_id(),
									'enCamino' => $enCamino,
									'conPedido' => $conPedido,
									'datos' => $f);
				echo json_encode($respuesta);
				$_SESSION["cliente"] = $id;
			}
		}
	}
	$con->close();
?>