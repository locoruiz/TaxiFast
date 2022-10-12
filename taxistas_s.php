<?php
	$usu = NULL;
	include 'inicioW.php';
    // How often to poll, in microseconds (1,000,000 μs equals 1 s)
define('MESSAGE_POLL_MICROSECONDS', 500000);
 
// How long to keep the Long Poll open, in seconds
define('MESSAGE_TIMEOUT_SECONDS', 30);
 
// Timeout padding in seconds, to avoid a premature timeout in case the last call in the loop is taking a while
define('MESSAGE_TIMEOUT_SECONDS_BUFFER', 5);
 

// Close the session prematurely to avoid usleep() from locking other requests
session_write_close();
 
// Automatically die after timeout (plus buffer)
set_time_limit(MESSAGE_TIMEOUT_SECONDS+MESSAGE_TIMEOUT_SECONDS_BUFFER);
 
// Counter to manually keep track of time elapsed (PHP's set_time_limit() is unrealiable while sleeping)
$counter = MESSAGE_TIMEOUT_SECONDS;
 
// Poll for messages and hang if nothing is found, until the timeout is exhausted
try{
    $res;
    $resultado;
    while($counter > 0)
    {
        
        // Check for new data (not illustrated)
        $res = $con->Select("SELECT t.foto, u.id as idu, t.id, u.nombre, u.apellidos, u.telefono, t.licencia, t.latitud, t.longitud, t.fechaPos FROM Usuario u, Taxista t where t.id_usuario = u.id and t.activo = true");
        $j = 0;
		$taxistas = isset($_POST["taxistas"]) ? $_POST["taxistas"] : array();
        while($fila = $res->fetch(PDO::FETCH_ASSOC)){
			$entro = false;
			$estado = "HA";
			$result = $con->Select("SELECT estado FROM Carrera where id_taxista = ".$fila["id"]." and estado <> 'TE'");
			while($f = $result->fetch(PDO::FETCH_ASSOC)){
				$estado = $f["estado"]; // con esto podemos traer mas datos, cliente y ubicacion
			}
			for($i = 0; $i < count($taxistas); $i++){
				$taxista = $taxistas[$i];
				if($taxista["id"] == $fila["id"]){
					$entro = true;
					$date1 = strtotime($taxista["fechaPos"]);
					$date2 = strtotime($fila["fechaPos"]);
					if($date2 > $date1){
						$resultado[$j++] = array('success' => 1,
										'id' => $fila["id"],
										'nombre' => $fila["nombre"]." ".$fila["apellidos"],
										'telefono' => $fila["telefono"],
										'licencia' => $fila["licencia"],
										'latitud' => doubleval($fila["latitud"]),
										'longitud' => doubleval($fila["longitud"]),
										'fechaPos' => $fila["fechaPos"],
										'estado' => $estado
										);
					}
				}
			}
			if(!$entro){ // No habia, hay que agregarlo
				$resultado[$j++] = array('success' => 1,
										'id' => $fila["id"],
										'nombre' => $fila["nombre"]." ".$fila["apellidos"],
										'telefono' => $fila["telefono"],
										'licencia' => $fila["licencia"],
										'foto' => $fila["foto"],
										'latitud' => doubleval($fila["latitud"]),
										'longitud' => doubleval($fila["longitud"]),
										'fechaPos' => $fila["fechaPos"],
										'estado' => $estado
										);
			}
        }
        if ($j > 0) {
            $data = "Habemus Datos!";
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
        
        $r = array('success' => 1,
                    'mensaje' => "Actualizando taxistas!",
                    'taxistas' => $resultado);
        echo json_encode($r);
    }else{
        $resultado = array('success' => 2);
        echo json_encode($resultado);
    }
}catch(Exception $e){
    $resultado = array('success' => 0,
                        'mensaje' => 'Hubo un error. '.$e->getMessage());
    $con->close();
    echo json_encode($resultado);
}
	$con->close();
?>