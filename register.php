<?php
	include 'Conexion.php';
	$con = ConexionDeFBE();
	if(!$con){
	    $resultado = array('success' => 0,
	                        'mensaje' => 'No pudo conectar'.$pdo_error);
	    echo json_encode($resultado);
	    exit() or die();
	}
	
	$telefono = trim($_POST["telefono"]);
	$nombre = trim($_POST["nombre"]);
	$apellidos = trim($_POST["apellidos"]);
	$correo = trim($_POST["email"]);
	$password = trim($_POST["password"]);
	$token = "";
	if(isset($_POST["token"]))
		$token = $_POST["token"];
	$dispositivo = 1; // android
	if(isset($_POST["dispositivo"]))
		$dispositivo = $_POST["dispositivo"];
	
	try{
		if (isset($_POST["editando"])) {
			// editando, que hacemo
			session_id($_POST["sess_id"]);
			session_start();

			if(isset($_SESSION["cliente"])){
			    // todo bien
			    $id = $_SESSION['cliente'];

			    $r = $con->Select("select session_id, bloqueado from Usuario where id = ".$con->validar($id));
			    $f = $r->fetch(PDO::FETCH_ASSOC);

			    if(trim(session_id()) == trim($f["session_id"])){
			    	// son iguales supuestamente
			    }else{
			    	$resultado = array('success' => 3,
			                        'mensaje' => 'Usuario activo en otro dispositivo');
			    	session_destroy();
			    	echo json_encode($resultado);
			    	exit() or die();
			    }
				if((int)$f["bloqueado"] == 1){
					$resultado = array('success' => 3,
									'mensaje' => 'Este usuario fue bloqueado!!');
					session_destroy();
					echo json_encode($resultado);
					exit() or die();
				}
			}else{
			    $resultado = array('success' => 3,
			                        'mensaje' => 'Session expirada');
			    session_destroy();
			    echo json_encode($resultado);
			    exit() or die();
			}


			$update = "update Usuario set ";
			
			$update .= "correo = ".$con->validar($correo);

			$passwordViejo = trim($_POST["passwordViejo"]);
			$continuar = true;
			if (!empty($passwordViejo)) {
				//  editando el password
				$result = $con->Select("select id from Usuario where password = ".$con->validar(md5($passwordViejo))." and id = ".$con->validar($id));
				if ($result->rowCount() > 0) {
					$continuar = true;

					$update .= ", password = ".$con->validar(md5($password));

				}else{
					$continuar = false;
					$respuesta = array('success' => 0,
								'mensaje' => 'La cantraseña anterior es incorrecta!!!');
					echo json_encode($respuesta);
				}
			}

			if ($continuar) {
				$update .= ", ";
				$update .= " nombre = ".$con->validar($nombre).", apellidos = ".$con->validar($apellidos).", telefono = ".$con->validar($telefono)." where id = ".$id;
				
				$con->EjecutarSQL($update);
				$respuesta = array('success' => 1,
								'nombre' => $nombre,
								'apellidos' => $apellidos,
								'telefono' => $telefono,
								'correo' => $correo);
				echo json_encode($respuesta);
			}
		}else{
			session_start();
			$result = $con->Select("Select id from Usuario where correo = ".$con->validar($correo));
			$numResults = $result->rowCount();
			
			if ($numResults > 0) {
				$respuesta = array('success' => 2,
									'mensaje' => 'Ya esta registrado este correo!!!');
				echo json_encode($respuesta);
			}else{
				$sql = "insert into Usuario (nombre, apellidos, correo, telefono, password, esTaxi, session_id, token, dispositivo) values  (".
										$con->validar($nombre).", ".$con->validar($apellidos).", ".$con->validar($correo).", ".
										$con->validar($telefono).", ".$con->validar(md5($password)).", 0, '".session_id()."', '".$token."', ".$dispositivo.")";

				
				$id = $con->EjecutarSQL($sql);
		 		
		 		$respuesta = array('success' => 1,
									'mensaje' => 'Bienvenido '.$nombre,
									'id' => $id,
									'sess_id' => session_id());
				echo json_encode($respuesta);
				$_SESSION["cliente"] = $id;
			}
		}
	}catch(Exception $e){
		$resultado = array('success' => 0,
	                        'mensaje' => 'Hubo un error. '.$e->getMessage());
		$con->close();
	    echo json_encode($resultado);
	}
	$con->close();
?>