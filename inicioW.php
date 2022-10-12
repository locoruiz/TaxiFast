<?
session_start();
header('Cache-control: private');
if(!isset($_SESSION["usuario"])){
	$resultado = array('success' => 0,
                        'mensaje' => 'Sesión expirada');
    echo json_encode($resultado);
    exit() or die();
}else{
	 if (isset($_POST["token"]) && $_POST["token"] == $_SESSION["token"]) {
       // todo bien
    }else{
		$resultado = array('success' => 0,
                        'mensaje' => 'Sesión expirada');
		echo json_encode($resultado);
		exit() or die();
	}
}

include 'Conexion.php';
$con = ConexionDeFBE();
if(!$con){
    $resultado = array('success' => 0,
                        'mensaje' => 'No pudo conectar'.$pdo_error);
    echo json_encode($resultado);
    exit() or die();
}
?>