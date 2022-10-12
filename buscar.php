<?php
	include 'inicioW.php';
	try{
		$busq = $_POST["buscar"];
		$busq = "%".$busq."%";
		$busq = $con->validar($busq);
		if(!isset($_POST["usuarios"])){
			$result = $con->Select("SELECT t.foto, u.id as idu, t.id, u.nombre, u.apellidos, u.correo, u.telefono, t.licencia, t.activo  
									FROM Usuario u, Taxista t 
									WHERE t.id_usuario = u.id and (u.nombre like ".$busq." || u.apellidos like ".$busq.
										" || u.telefono like ".$busq." || t.licencia like ".$busq." )");
			$i = 0;
			echo "<tr><th>Nombre</th><th>Apellidos</th><th>Correo</th><th>Telefono</th><th>Licencia</th></tr>";
			while($fila = $result->fetch(PDO::FETCH_ASSOC)){
				$i++;
				echo "<tr id='".$fila["id"]."'>".
					  "<td id='".$fila["id"]."nombre'>".$fila["nombre"].
					  "</td><td id='".$fila["id"]."apellidos'>".$fila["apellidos"].
					  "</td><td id='".$fila["id"]."correo'>".$fila["correo"].
					  "</td><td id='".$fila["id"]."telefono'>".$fila["telefono"].
					  "</td><td id='".$fila["id"]."licencia'>".$fila["licencia"].
					  "</td><td><button onclick=\"modificar('".$fila["id"]."');\">Modificar</button><button onclick=\"eliminar('".
						$fila["id"]."');\">Eliminar</button></td><input type='hidden' id='".$fila["id"].
						"id' value='".$fila["idu"]."' /><input type='hidden' id='".$fila["id"]."foto' value='".$fila["foto"]."'/>";
				if ($fila["activo"] == 0) {
					echo "<td id='".$fila["id"]."td'><button onclick='habilitar(".$fila["id"].");'>Habilitar</button></td>";
				}
				echo "</tr>";
			}
			if ($i == 0) {
				echo "No se encontraron resultados de la busqueda";
			}
		}else{
			$result = $con->Select("SELECT * 
									FROM Usuario 
									WHERE nombre like ".$busq." || apellidos like ".$busq.
										" || telefono like ".$busq);
			$i = 0;
			echo "<tr><th>Nombre</th><th>Apellidos</th><th>Correo</th><th>Telefono</th><th>Cancelados</th></tr>";
			while($fila = $result->fetch(PDO::FETCH_ASSOC)){
				$i++;
				echo "<tr id='".$fila["id"]."'><td id='".$fila["id"]."nombre'>".$fila["nombre"].
					"</td><td id='".$fila["id"]."apellidos'>".$fila["apellidos"].
					"</td><td id='".$fila["id"]."correo'>".$fila["correo"].
					"</td><td id='".$fila["id"]."telefono'>".$fila["telefono"]."</td>".
					"</td><td id='".$fila["id"]."cancelados'>".$fila["cancelados"]."</td>";
				if ($fila["bloqueado"] == 0) {
					echo "<td id='".$fila["id"]."td'><button onclick='bloquear(".$fila["id"].", 1);'>Bloquear</button></td>";
				}else{
					echo "<td id='".$fila["id"]."td'><button onclick='bloquear(".$fila["id"].", 0);'>Desbloquear</button></td>";
				}
				echo "</tr>";
			}
			if ($i == 0) {
				echo "No se encontraron resultados de la busqueda";
			}
		}
	}catch(Exception $e){
	    echo 'Hubo un error. '.$e->getMessage();
	}
	$con->close();
?>