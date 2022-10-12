<?php
	include 'inicioW.php';
	if (isset($_SESSION["usuario"])) {
		//esta logeado, puede eliminar
		$id = $_POST["id"];
		$fotoA = $_POST["fotoA"];
		
		try{
			$con->EjecutarSQL("delete from Taxista where id = ".$con->validar($id));
			if(file_exists($fotoA))
					unlink($fotoA);	
		}
		catch(Exception $e){
		    echo 'Hubo un error. '.$e->getMessage();
		}
	}else
		echo "debe estar logueado para eliminar un taxi";
	$con->close();
?>