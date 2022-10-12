<?php

// ver si hay nuevos pedidos
include 'inicio.php';

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
        $lat = $con->validar($_POST["latTaxi"]);
        $lng = $con->validar($_POST["longTaxi"]);

        $con->EjecutarSQL("update Taxista set 
                                latitud = ".$con->validar($_POST["latTaxi"])." , 
                                longitud = ".$con->validar($_POST["longTaxi"]).", ".
                                "fechaPos = CURRENT_TIMESTAMP where id = ".$con->validar($_POST["id_taxista"]));

        // Check for new data (not illustrated)
        $res = $con->Select("select *, ( 6371 * acos( cos( radians(" . $lat . ") ) * ".
                                        " cos( radians( latOrigen ) ) * cos( radians( longOrigen ) - ".
                                        " radians(" . $lng . ") ) + sin( radians(" . $lat .
                                         ") ) * sin( radians( latOrigen ) ) ) ) AS distance, ".
                            " TIMESTAMPDIFF(SECOND, hora, CURRENT_TIMESTAMP) as tiempo from Carrera having distance < 5 ");
        $j = 0;
        while($fila = $res->fetch(PDO::FETCH_ASSOC)){
            if($fila["estado"] == "HA" || $fila["tiempo"] <= 30){
                if (isset($_POST["pedido_viejo"]) && $_POST["pedido_viejo"] == $fila["id"]) {
                    continue;
                }
                $resultado[$j++] = array('success' => 1,
                                'idPedido' => $fila["id"],
                                'idUsuario' => $fila["id_usuario"],
                                'direccionOrigen' => $fila["direccionOrigen"],
                                'latOrigen' => doubleval($fila["latOrigen"]),
                                'longOrigen' => doubleval($fila["longOrigen"]),
                                'referencia' => $fila["referencia"],
                                'direccionDestino' => $fila["direccionDestino"],
                                'latDestino' => doubleval($fila["latDestino"]),
                                'longDestino' => doubleval($fila["longDestino"]));
            }
        }
        if ($j > 0) {
            $data = "Habemus DAtos!";
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
                    'mensaje' => "Hay nuevos pedidos!",
                    'pedidos' => $resultado);
                
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