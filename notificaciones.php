<?php
include 'inicio.php';
// API access key from Google API's Console
define( 'API_ACCESS_KEY', 'Api key' );

$id_carrera = $_POST["id_carrera"];

$result = $con->Select("select u.token, u.dispositivo, c.estado from Usuario u, Carrera c where u.id = c.id_usuario and c.id = ".$con->validar($id_carrera));

if ($result->rowCount() <= 0) {
	# Pedido cancelado!
	$resultado = array('success' => 4,
                    'mensaje' => 'El usuario canceló el pedido');
	$con->close();
    echo json_encode($resultado);
    exit() or die();
}

$f = $result->fetch(PDO::FETCH_ASSOC);

$token = $f["token"];
$dispositivo = $f["dispositivo"];

$sys_func = $_POST["sys_func"];

$sys_func($token, $dispositivo);

$con->close();


function yaLlegue($token, $dispositivo){
	global $con;
	global $id_carrera;
	$result = (object)array();
	$result->success = 1;
	$result->request = 1;
	$result->mensaje = 'vamos bien';

	$result = $con->Select("select * from Carrera where id = ".$con->validar($_POST["id_carrera"]));
		if ($result->rowCount() <= 0) {
			# Pedido cancelado!
			$resultado = array('success' => 4,
	                        'mensaje' => 'El usuario canceló el pedido');
			$con->close();
		    echo json_encode($resultado);
		    exit() or die();
		}
		$f = $result->fetch(PDO::FETCH_ASSOC);
		
		if ($f["estado"] == 'ES') {
			$sql = "update Carrera set estado = 'TA' where id = ".$con->validar($id_carrera);
			$res = $con->EjecutarSQL($sql);
		}else if($f["estado"] == 'AB'){
			$resultado = array('success' => 1,
								'request' => 2,
								'estado' => 'AB',
	                        'mensaje' => 'Usuario abordo');
			$con->close();
		    echo json_encode($resultado);
		    return;
		}else if($f["estado"] == "TE"){ // Carrera terminada

			$sql = "delete from Carrera where id = ".$con->validar($_POST["id_pedido"]);
			$result = $con->EjecutarSQL($sql);
			

			$resultado = array('success' => 4,
	                        'mensaje' => 'Carrera Finalizada');
			$con->close();
		    echo json_encode($resultado);
		    exit() or die();
		}
	$fields = array();
	if($dispositivo == "1"){ // android, enviar como data message
		$registrationIds = array( $token );
		// prep the bundle
		$msg = array
		(
			'body' 	=> 'El taxi ya está afuera!',
			'title'		=> 'Taxi Afuera',
			'accion' => 2
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
			'body' 	=> 'El taxi ya está afuera!',
			'title'		=> 'Taxi Afuera',
			"content_available" => 1,
		    "sound" => "sonido.wav"
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
	
	$result = (object)array();
	$result->success = 1;
	$result->request = 1;
	$result->mensaje = 'notificacion enviada';

	$result->curl = curl_exec($ch );
	curl_close( $ch );
	echo json_encode($result);
}
function yaLlegamos(){
	global $con;
	global $id_carrera;
	$sql = "delete from Carrera where id = ".$con->validar($id_carrera);
	$con->EjecutarSQL($sql);
	$resultado = array('success' => 4,
						'estado' => "HA",
						'mensaje' => "Carrera concluida correctamente");
	echo json_encode($resultado);
}
function cancelar(){
	global $con;
	global $id_carrera;
	$sql = "delete from Carrera where id = ".$con->validar($id_carrera);
	$con->EjecutarSQL($sql);
	$resultado = array('success' => 4,
						'estado' => "HA",
						'mensaje' => "Carrera cancelada correctamente");
	echo json_encode($resultado);
}

function mensaje($token){
	$registrationIds = array( $token );
	// prep the bundle
	$msg = array
	(
		'body' 	=> $_POST["mensaje"],
		'title'		=> $_POST["titulo"]
	    
	);
	$fields = array
	(
	    'to' => $token,
		'data'			=> $msg
	);
	 
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
	$result = curl_exec($ch );
	curl_close( $ch );
	echo $result;
}