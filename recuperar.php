<?php
	include 'Conexion.php';
	$con = ConexionDeFBE();
	if(!$con){
	    $resultado = array('success' => 0,
	                        'mensaje' => 'No pudo conectar'.$pdo_error);
	    echo json_encode($resultado);
	    exit() or die();
	}

	try{
		$correo = $_POST["correo"];
	
		$result = $con->Select("select id from Usuario where correo = ".$con->validar($correo));
		$resultado;
		if ($result->rowCount() > 0) {
			$rand = substr(md5(microtime()),rand(0,26), 10);
			$fila = $result->fetch(PDO::FETCH_ASSOC);
			$id = $fila["id"];

			date_default_timezone_set('America/La_Paz');

			$date = date('Y-m-d');
			$fechaFin = strtotime("+3 days", strtotime($date));

			$con->EjecutarSQL("insert into rec_p values(".$id.", '".$rand."', '".date("Y-m-d", $fechaFin)."')");

			//SMTP needs accurate times, and the PHP time zone MUST be set
			//This should be done in your php.ini, but this is how to do it if you don't have access to that


			require "class.phpmailer.php";
			//Create a new PHPMailer instance
			$mail = new PHPMailer;
			$mail->CharSet = 'UTF-8';
			//Tell PHPMailer to use SMTP
			$mail->isSMTP();

			//Enable SMTP debugging
			// 0 = off (for production use)
			// 1 = client messages
			// 2 = client and server messages
			$mail->SMTPDebug = 0;

			//Ask for HTML-friendly debug output
			$mail->Debugoutput = 'html';

			//Set the hostname of the mail server
			$mail->Host = 'a2plcpnl0515.prod.iad2.secureserver.net';
			
			$mail->Port = 465;

			//Set the encryption system to use - ssl (deprecated) or tls
			$mail->SMTPSecure = 'ssl';
			$mail->SMTPAutoTLS = false;
			
			//Whether to use SMTP authentication
			$mail->SMTPAuth = true;

			//Username to use for SMTP authentication - use full email address for gmail
			$mail->Username = "taxifast@roscosoft.com";

			//Password to use for SMTP authentication
			$mail->Password = "Taxi123";

			$mail->From = "taxifast@roscosoft.com";
			$mail->FromName = "Taxi Fast";

			
			//Set who the message is to be sent to
			$mail->addAddress($correo);

			//Set the subject line
			$mail->Subject = 'Recuperacion de contraseña';

			
			//Replace the plain text body with one created manually
			$mail->Body = '<!DOCTYPE html>
							<html>
			    				<head>
			        				<title>Taxi Fast</title>
			        				<meta charset="UTF-8">
			        			</head>
			        			<body>
			        				<div style="text-alignment:center"><p>Para recuperar su contraseña entre al seiguiente link, si recibió esto por error ignore el mensaje.</p>
									<a href="http://roscosoft.com/TaxiFast/cambiarpassword.php?codigo='.$rand.'&id='.$id.'">Recuperar contraseña</a>
									</div>
								</body>
								</html>';

			$mail->IsHTML(true);

			//send the message, check for errors
			if (!$mail->send()) {
			    $resultado = array('success' => 0, 'mensaje' => "Mailer Error: " . $mail->ErrorInfo);
			} else {
				$resultado = array('success' => 1, 'mensaje' => 'Se envio un correo con los pasos a seguir para recuperar la contraseña. El codigo enviado es vaildo por tres dias');
			}
		}else{
			$resultado = array('success' => 0, 'mensaje' => 'Este correo no tiene cuenta en Taxi Fast!!');
		}
		
		echo json_encode($resultado);
	}catch(Exception $e){
		$resultado = array('success' => 0,
	                        'mensaje' => 'Hubo un error. '.$e->getMessage());
		echo json_encode($resultado);
	}
	$con->close();
?>