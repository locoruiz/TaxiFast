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
$id_pedido = 0;

try{
    if (isset($_POST['id_pedido'])) {
        $id_pedido = (int)trim($_POST['id_pedido']);
    }else{
        
        //throw new Exception("Señor usuario, Taxi Fast estara disponible para su uso en marzo de 2017");
		
        $repetido = 0;
        if(isset($_POST["repetido"])) $repetido = $_POST["repetido"];
        $consulta = "insert into Carrera (id_usuario, latOrigen, longOrigen, direccionOrigen, referencia, direccionDestino, latDestino, longDestino, repetida) 
                                        values (".$id.", ".$con->validar($_POST["latOrigen"]).", ".$con->validar($_POST["longOrigen"]).", ".$con->validar($_POST["direccionOrigen"]).", ".$con->validar($_POST["referencia"]).",
                                                ".$con->validar($_POST["direccionDestino"]).", ".$con->validar($_POST["latDestino"]).", ".$con->validar($_POST["longDestino"]).", ".$con->validar($repetido).")";
        $id_pedido = $con->EjecutarSQL($consulta);
		
		$sql = "insert into CarreraH (id, id_usuario, latitud, longitud, direccion, referencia) values (".$id_pedido.", ".
																											$id.", ".
																											$con->validar($_POST["latOrigen"]).", ".
																											$con->validar($_POST["longOrigen"]).", ".
																											$con->validar($_POST["direccionOrigen"]).", ".
																											$con->validar($_POST["referencia"]).")";
		$con->EjecutarSQL($sql);
        $_SESSION["id_carrera"] = $id_pedido;
    }

    // Close the session prematurely to avoid usleep() from locking other requests
    session_write_close();
     
    // Automatically die after timeout (plus buffer)
    set_time_limit(MESSAGE_TIMEOUT_SECONDS+MESSAGE_TIMEOUT_SECONDS_BUFFER);
     
    // Counter to manually keep track of time elapsed (PHP's set_time_limit() is unrealiable while sleeping)
    $counter = MESSAGE_TIMEOUT_SECONDS;
     
    $latTaxi = 0.0;
    $longTaxi = 0.0;
    
    $estado = "HA";

    if(isset($_POST["latTaxi"]) && isset($_POST["longTaxi"])){
        $latTaxi = doubleval($_POST["latTaxi"]);
        $longTaxi = doubleval($_POST["longTaxi"]);
    }
    if(isset($_POST["estado"]))
        $estado = $_POST["estado"];

    // Poll for messages and hang if nothing is found, until the timeout is exhausted
    while($counter > 0)
    {
		// ver si hay mensajes nuevos
		$id_msj = 0;
		if(isset($_POST["id_msj"]))
			$id_msj = (int)$_POST["id_msj"];
		$res = $con->Select("select * from Chat where id_envia <> ".$id." and id_carrera = ".$con->validar($id_pedido)." and id > ".$id_msj);
		$i = 0;
		$array = array();
        while($fila = $res->fetch(PDO::FETCH_ASSOC)){
			$array[$i]["id"] = $fila["id"];
			$array[$i]["mensaje"] = $fila["mensaje"];
			$array[$i]["fecha"] = $fila["fecha"];
			$i++;
		}
		
		// ver si se movio el taxista
        $res = $con->Select("select estado, latTaxi, longTaxi, id_taxista from Carrera where id = ".$con->validar($id_pedido));
		
		if($res->rowCount() <= 0){
			$mensaje = ($estado == "AB") ? 'Carrera finalizada por el taxista' : 'Lo sentimos, el taxista tuvo problemas y no podra ir a recogerlo.';
			$resultado = array('success' => 4,
                    'mensaje' => $mensaje);
			$con->close();
			echo json_encode($resultado);
			exit() or die();
		}
		
        $fila = $res->fetch(PDO::FETCH_ASSOC);
		
        $epsilon = 0.00001; // diferencia en metros
        if ( $fila["estado"] != $estado || (abs($latTaxi - doubleval($fila["latTaxi"])) > $epsilon ||
                                         abs($longTaxi - doubleval($fila["longTaxi"])) > $epsilon ) || $i > 0) { // Taxi elegido y en camino
            

            if($estado == 'AB' && $fila["estado"] == 'TA'){
                $con->EjecutarSQL("update Carrera set estado = 'AB'
                                where id = ".$con->validar($_POST["id_pedido"]));
            }

            if (isset($_POST["taxista"]) && $_POST["taxista"] == $fila["id_taxista"]) {
                // no mandarlo de nuevo
            }else if($fila["id_taxista"]){
                $res = $con->Select("SELECT t.id, t.foto, u.nombre, u.apellidos, u.telefono, t.licencia FROM 
                            Usuario u, Taxista t where t.id_usuario = u.id and t.id = ".$fila["id_taxista"]);
                $taxista = $res->fetch(PDO::FETCH_ASSOC);
            }
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
        if (isset($taxista)) {
            $resultado = array('success' => 1,
                            'id_pedido' => $id_pedido,
                            'latTaxi' => doubleval($fila["latTaxi"]),
                            'longTaxi' => doubleval($fila["longTaxi"]), 
                            'tieneTaxista' => 1,
                            'id_taxista' => $taxista["id"],
                            'nombre' => $taxista["nombre"]." ".$taxista["apellidos"],
                            'licencia' => $taxista["licencia"],
                            'foto' => $taxista['foto'],
                            'telefono' => $taxista['telefono'],
							  'mensajes' => $array,
							  'mensaje' => "Devolvio taxista");
        }else{
            $resultado = array('success' => 1,
                            'id_pedido' => $id_pedido,
                            'id_taxista' => $fila["id_taxista"],
                            'latTaxi' => doubleval($fila["latTaxi"]),
                            'longTaxi' => doubleval($fila["longTaxi"]),
                            'tieneTaxista' => 0,
                            'estado'  => $fila["estado"],
							  'mensajes' => $array,
							  'mensaje' => "Devolvio posicion o chat");
        }
        
        echo json_encode($resultado);
    }else{
        $resultado = array('success' => 2,
                            'id_pedido' => $id_pedido,
							  'mensaje' => "No hay mensajes ni taxista");

        echo json_encode($resultado);
    }
}catch(Exception $e){
    $resultado = array('success' => 0,
                        'mensaje' => $e->getMessage());
    echo json_encode($resultado);
}

$con->close();
?>