<?php
include 'Conexion.php';
$con = ConexionDeFBE();
if(!$con){
    $resultado = array('success' => 0,
                        'mensaje' => 'No pudo conectar'.$pdo_error);
    echo json_encode($resultado);
    exit() or die();
}
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
if (!isset($_POST["funcion"]) || empty($_POST["funcion"])) {
    $resultado = array('success' => 0,
                        'mensaje' => 'Metodo no especificado');
    echo json_encode($resultado);
    exit() or die();
}
call_user_func($_POST["funcion"]);
?>