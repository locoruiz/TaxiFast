<?php

// ver historico del usuario
include 'inicio.php';

try{    
    
    $resultado = array();
    $res = $con->Select("select * from Historico where id_usuario = ".$con->validar($_POST["id_usuario"]));
    $j = 0;
    while($fila = $res->fetch(PDO::FETCH_ASSOC)){
        $resultado[$j++] = array('direccion' => $fila["direccionOrigen"],
                            'latitud' => doubleval($fila["latOrigen"]),
                            'longitud' => doubleval($fila["longOrigen"]),
                            'referencia' => $fila["referencia"],
                            );
    }
    $r = array('success' => 1,
                'mensaje' => 'Carreras cargadas correctamente',
                'id_usuario' => $_POST["id_usuario"],
                'carreras' => $resultado);
    echo json_encode($r);
}catch(Exception $e){
    $resultado = array('success' => 0,
                        'mensaje' => 'Hubo un error. '.$e->getMessage());
    $con->close();
    echo json_encode($resultado);
}
$con->close();
?>