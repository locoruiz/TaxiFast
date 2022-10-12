<?php
	include 'inicio.php';

	define( 'API_ACCESS_KEY', 'access key' );
	
	$resultado = array('success' => 1,
	                        'mensaje' => 'Posicion actualizada');
	try{
		$con->EjecutarSQL("update Taxista set 
								latitud = ".$con->validar($_POST["latTaxi"])." , 
								longitud = ".$con->validar($_POST["longTaxi"]).", ".
								"fechaPos = CURRENT_TIMESTAMP where id = ".$con->validar($_POST["id_taxista"]));

		$result = $con->Select("select * from Carrera where id = ".$con->validar($_POST["id_pedido"]));
		if ($result->rowCount() <= 0) {
			# Pedido cancelado!
			$resultado = array('success' => 4,
	                        'mensaje' => 'El usuario canceló el pedido');
			$con->close();
		    echo json_encode($resultado);
		    exit() or die();
		}
		$f = $result->fetch(PDO::FETCH_ASSOC);
		if ($f["id_taxista"] != null && $f["id_taxista"] != $_POST["id_taxista"]) {
			# otro taxi ya esta en camino

			// si estamos mas cerca y todavia hay tiempo
			$hora = date('H:i');
			$horaPedido = strtotime($f["hora"]);
			if(($hora - $horaPedido)/60 <= 1){
				// tiene tiempo de ir
				$diffLat = abs(doubleval($f["latOrigen"]) - doubleval($f["latTaxi"]));
				$diffLong = abs(doubleval($f["longOrigen"]) - doubleval($f["longTaxi"]));
				$NdiffLat = abs(doubleval($f["latOrigen"]) - doubleval($_POST["latTaxi"]));
				$NdiffLong = abs(doubleval($f["longOrigen"]) - doubleval($_POST["longTaxi"]));
				if($NdiffLong < $diffLong && $NdiffLat < $diffLat){
					// esta mas cerca!
					$result = $con->EjecutarSQL("update Carrera set 
								estado = 'ES', 
								id_taxista = ".$con->validar($_POST["id_taxista"]).", 
								latTaxi = ".$con->validar($_POST["latTaxi"])." , 
								longTaxi = ".$con->validar($_POST["longTaxi"])." where id = ".$con->validar($_POST["id_pedido"]));
					
				if(isset($_POST["id_taxista"]) && isset($_POST["id_pedido"]))
					$con->EjecutarSQL("update CarreraH set 
								id_taxista = ".$con->validar($_POST["id_taxista"])." where id = ".$con->validar($_POST["id_pedido"]));
					
					$resultado = array('success' => 1,
									   'request' => 0,
	                        'mensaje' => 'Pedido aceptado');
				}else{
					$resultado = array('success' => 4,
	                        	'mensaje' => 'Otro taxi ya esta en camino');
					$con->close();
				    echo json_encode($resultado);
				    exit() or die();
				}
			}else{
				$resultado = array('success' => 4,
	                        'mensaje' => 'Otro taxi ya esta en camino, tarde');
				$con->close();
			    echo json_encode($resultado);
			    exit() or die();
			}
		}
		if ($f["estado"] == 'HA') {
			$id_carrera = $_POST["id_pedido"];
			$result = $con->Select("select u.token, u.dispositivo, c.latOrigen, c.longOrigen from Usuario u, Carrera c where u.id = c.id_usuario and c.id = ".$con->validar($id_carrera));
			$f = $result->fetch(PDO::FETCH_ASSOC);
			$token = $f["token"];

			$sql = "SELECT t.id, t.foto, u.nombre, u.apellidos, u.telefono, t.licencia FROM 
                            Usuario u, Taxista t where t.id_usuario = u.id and t.id = ".$con->validar($_POST["id_taxista"]);

			$result = $con->Select($sql);


			$taxista = $result->fetch(PDO::FETCH_ASSOC);

			$result = $con->EjecutarSQL("update Carrera set 
								estado = 'ES', 
								id_taxista = ".$con->validar($_POST["id_taxista"]).", 
								latTaxi = ".$con->validar($_POST["latTaxi"])." , 
								longTaxi = ".$con->validar($_POST["longTaxi"])." where id = ".$con->validar($_POST["id_pedido"]));

			if(isset($_POST["id_taxista"]) && isset($_POST["id_pedido"]))
				$con->EjecutarSQL("update CarreraH set 
								id_taxista = ".$con->validar($_POST["id_taxista"])." where id = ".$con->validar($_POST["id_pedido"]));


			$fields = array();
			if($f["dispositivo"] == "1"){ // android
				$registrationIds = array( $token );
				// prep the bundle
				$msg = array
				(
					'body' 	=> 'El taxi ya está en camino!',
					'title'		=> 'Taxi en camino',
					'accion' => 1,
					'id_pedido' => $_POST["id_pedido"],
	                'latTaxi' => doubleval($_POST["latTaxi"]),
	                'longTaxi' => doubleval($_POST["longTaxi"]),
	                'latPedido' => $f["latOrigen"],
	                'longPedido' => $f["longOrigen"],
	                'tieneTaxista' => 1,
	                'id_taxista' => $taxista["id"],
	                'nombre' => $taxista["nombre"]." ".$taxista["apellidos"],
	                'licencia' => $taxista["licencia"],
	                'foto' => $taxista['foto'],
	                'telefono' => $taxista['telefono']
				    
				);
				$fields = array
				(
				    'registration_ids' => $registrationIds,
					'data'	=> $msg
				);
			}else{
				// prep the bundle
				$msg = array
				(
					'body' 	=> 'Un taxi aceptó su pedido!',
					'title'		=> 'Taxi en camino',
					"content_available" => 1,
				    "sound" => "default"
				);
				$fields = array
				(
				    'to' => $token,
					'notification'	=> $msg,
					"priority" => "high"
				);
			}
			 
			$headers = array
			(
			    'Content-Type: application/json',
				'Authorization: key=' . API_ACCESS_KEY
			);
			 
			$ch = curl_init();
			curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
			curl_setopt( $ch,CURLOPT_POST, true );
			curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
			curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
			
			$curl = curl_exec($ch );
			$resultado["request"] = 0;
			$resultado["mensaje"] = "Notificaion enviada";
		}else if($f["estado"] == "TE"){ // Carrera terminada

			$sql = "delete from Carrera where id = ".$con->validar($_POST["id_pedido"]);
			$result = $con->EjecutarSQL($sql);
			
			$sql = "delete from Chat where id_carrera = ".$con->validar($_POST["id_pedido"]);
			$result = $con->EjecutarSQL($sql);
			
			$resultado = array('success' => 4,
	                        'mensaje' => 'Carrera Finalizada');
			$con->close();
		    echo json_encode($resultado);
		    exit() or die();
		}else {
			$result = $con->EjecutarSQL("update Carrera set 
								latTaxi = ".$con->validar($_POST["latTaxi"])." , 
								longTaxi = ".$con->validar($_POST["longTaxi"])." where id = ".$con->validar($_POST["id_pedido"]));
			$resultado["request"] = 2;
			$resultado["estado"] = $f["estado"];
		}
		
	    echo json_encode($resultado);
	}catch(Exception $e){
		$resultado = array('success' => 0,
	                        'mensaje' => 'Hubo un error. '.$e->getMessage());
		
	    echo json_encode($resultado);
	}
	$con->close();
?>