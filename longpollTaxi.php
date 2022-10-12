<?php
include 'inicio.php';

    // How often to poll, in microseconds (1,000,000 μs equals 1 s)
define('MESSAGE_POLL_MICROSECONDS', 500000);
 
// How long to keep the Long Poll open, in seconds
define('MESSAGE_TIMEOUT_SECONDS', 30);
 
// Timeout padding in seconds, to avoid a premature timeout in case the last call in the loop is taking a while
define('MESSAGE_TIMEOUT_SECONDS_BUFFER', 5);
 
// Hold on to any session data you might need now, since we need to close the session before entering the sleep loop
//$id = $_POST['id'];

try{
	$id_pedido = 0;
    if (isset($_POST['id_pedido']))
        $id_pedido = (int)trim($_POST['id_pedido']);
	else
		throw new Exception("No hay el id de la carrera!");

    // Close the session prematurely to avoid usleep() from locking other requests
    session_write_close();
     
    // Automatically die after timeout (plus buffer)
    set_time_limit(MESSAGE_TIMEOUT_SECONDS+MESSAGE_TIMEOUT_SECONDS_BUFFER);
     
    // Counter to manually keep track of time elapsed (PHP's set_time_limit() is unrealiable while sleeping)
    $counter = MESSAGE_TIMEOUT_SECONDS;
    
	if(!isset($_POST["estado"]))
		throw new Exception("No enviaron el estado!!");
	
    $estado = $_POST["estado"];

	if(!isset($_POST["id_taxista"]))
		throw new Exception("No enviaron el id del taxista!");
	$id_taxista = $_POST["id_taxista"];
	
	$f;
    // Poll for messages and hang if nothing is found, until the timeout is exhausted
    while($counter > 0)
    {
		// ver si hay mensajes nuevos
		$id_msj = 0;
		if(isset($_POST["id_msj"]))
			$id_msj = (int)$_POST["id_msj"];
		$res = $con->Select("select * from Chat where id_envia <> ".$id."  and id_carrera = ".$con->validar($id_pedido)." and id > ".$id_msj);
		$i = 0;
		$array = array();
        while($fila = $res->fetch(PDO::FETCH_ASSOC)){
			$array[$i]["id"] = $fila["id"];
			$array[$i]["mensaje"] = $fila["mensaje"];
			$array[$i]["fecha"] = $fila["fecha"];
			$i++;
		}
		
		// ver si se movio el taxista
        $result = $con->Select("select * from Carrera where id = ".$con->validar($id_pedido));
		if ($result->rowCount() <= 0) {
			# Pedido cancelado!
			$resultado = array('success' => 4,
	                        'mensaje' => 'El usuario canceló el pedido');
			$con->close();
		    echo json_encode($resultado);
		    exit() or die();
		}
		$f = $result->fetch(PDO::FETCH_ASSOC);
		if ($f["id_taxista"] != null && $f["id_taxista"] != $id_taxista) {
			# otro taxi ya esta en camino

			// si estamos mas cerca y todavia hay tiempo
			$hora = date('H:i');
			$horaPedido = strtotime($f["hora"]);
			if(($hora - $horaPedido)/60 <= 1){
				// tiene tiempo de ir, al moverse le asigna
			}else{
				$resultado = array('success' => 4,
	                        'mensaje' => 'Otro taxi ya esta en camino');
				$con->close();
			    echo json_encode($resultado);
			    exit() or die();
			}
		}
		if($f["estado"] == "TE"){ // Carrera terminada

			$sql = "delete from Carrera where id = ".$con->validar($_POST["id_pedido"]);
			$result = $con->EjecutarSQL($sql);
			
			$sql = "delete from Chat where id_carrera = ".$con->validar($_POST["id_pedido"]);
			$result = $con->EjecutarSQL($sql);
			
			$resultado = array('success' => 4,
	                        'mensaje' => 'Carrera Finalizada');
			$con->close();
		    echo json_encode($resultado);
		    exit() or die();
		}
        if ( $f["estado"] != $estado || $i > 0) { // cambio el estado o hay mensajes
			
            $data = "vamos bien";
            break;
        }
        else
        {
            // Otherwise, sleep for the specified time, after which the loop runs again
            usleep(MESSAGE_POLL_MICROSECONDS);
     
            // Decrement seconds from counter (the interval was set in μs, see above)
            $counter -= MESSAGE_POLL_MICROSECONDS / 1000000;
        }
    }
     
    // If we've made it this far, we've either timed out or have some data to deliver to the client
    if(isset($data))
    {
        $resultado = array('success' => 2,
						   'mensajes' => $array,
						   'estado' => $f["estado"],
                           'mensaje' => "Actualizado el taxista");
        echo json_encode($resultado);
    }else{
        $resultado = array('success' => 2,
							  'mensaje' => "No hay novedades...");

        echo json_encode($resultado);
    }
}catch(Exception $e){
    $resultado = array('success' => 0,
                        'mensaje' => $e->getMessage());
    echo json_encode($resultado);
}

$con->close();
?>