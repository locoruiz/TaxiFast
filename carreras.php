<?php
	session_start();
	header('Cache-control: private');
	$usu = NULL;
	if (isset($_SESSION["usuario"])) {
		$usu = $_SESSION["usuario"];
	}else{
		header("Location: login.html");
	}
	include "Conexion.php";
	$con = ConexionDeFBE();
?>

<!DOCTYPE html>
<html>
    <head>
        <!-- este es un comentario -->
        <title>Taxi Fast</title>
        <meta charset="UTF-8">
	<link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
        <link rel="shortcut icon" href="">
        <script src="../jquery-3.0.0.js"></script>
        <link rel="stylesheet" href="../estilo/principal.css"/>
        <style type="text/css">
        .cont{
            border-radius: 10px;
            background-color: rgb(250, 250, 250);
            box-shadow: 3px 3px 3px grey;
            margin: auto;
            margin: 20px;
            padding: 20px;
        }
        table.lista {
            border-collapse: collapse;
            width: 100%;
        }

        .lista th, td {
            text-align: left;
            padding: 8px;
        }

        .lista tr:nth-child(even){background-color: #f2f2f2}

    th {
        background-color: #4CAF50;
        color: white;
    }
        </style>

    </head>
    <body style="text-align:center; background:url('img/logo.jpg') no-repeat left top fixed; background-size: 110px 110px;">
        <h1>Taxi Fast</h1>

        <p>Bienvenido <?php echo $usu; ?> <button onclick="logout()">Cerrar Sesión</button></p>
        <a href="main.php">Ver taxistas</a><br/>
		<a href="usuarios.php">Ver usuarios</a> <br />
		<a href="taxistas.php">Ver posicion de taxistas</a>
        <div class="cont">
            <h3>Carreras en curso</h3>
            <button onclick="recargar();" style="margin-bottom: 20px">Recargar</button></br>
            
            <table class="lista" id="lista">
<?php
		$result = $con->Select("SELECT c.id as carrera, c.hora, u.id, u.nombre, u.apellidos, u.correo, u.telefono, c.id_taxista, c.estado from Usuario u, Carrera c where c.id_usuario = u.id order by c.hora DESC");
        $i = 0;
		echo "<tr><th>Carrera</th><th>Fecha</th><th colspan='2'>Usuario</th><th>Correo</th><th>Telefono</th><th>Taxista</th><th>Estado</th></tr>";
		while($fila = $result->fetch(PDO::FETCH_ASSOC)){
            $i++;
			echo "<tr><td>".$fila["carrera"].
                "</td><td>".$fila["hora"].
                "</td><td>".$fila["id"].
                "</td><td>".$fila["nombre"]." ".$fila["apellidos"].
                "</td><td>".$fila["correo"].
                "</td><td>".$fila["telefono"]."</td>".
                "</td>";
            if ($fila["estado"] == "HA") {
                echo "<td></td><td>Esperando taxi</td>";
            }else if ($fila["estado"] == "ES") {
				$r = $con->Select("SELECT u.nombre, u.apellidos FROM Usuario u, Taxista t where t.id_usuario = u.id and t.id = ".$fila["id_taxista"]);
				$f =  $r->fetch(PDO::FETCH_ASSOC);
                echo "<td>".$f["nombre"]." ".$f["apellidos"]."</td><td>Taxista en camino</td>";
            }else if ($fila["estado"] == "TA") {
				$r = $con->Select("SELECT u.nombre, u.apellidos FROM Usuario u, Taxista t where t.id_usuario = u.id and t.id = ".$fila["id_taxista"]);
				$f =  $r->fetch(PDO::FETCH_ASSOC);
				echo "<td>".$f["nombre"]." ".$f["apellidos"]."</td><td>Taxista afuera</td>";
            }else if ($fila["estado"] == "AB") {
				$r = $con->Select("SELECT u.nombre, u.apellidos FROM Usuario u, Taxista t where t.id_usuario = u.id and t.id = ".$fila["id_taxista"]);
				$f =  $r->fetch(PDO::FETCH_ASSOC);
                echo "<td>".$f["nombre"]." ".$f["apellidos"]."</td><td>Cliente a Bordo</td>";
            }else if($fila["estado"] == "TE"){
				echo "<td></td><td>Carrera terminada</td>";
			}
            echo "</tr>";
		}
        echo "</table>";
        if($i == 0)
            echo "<p>No hay carreras en este momento</p>";
				
    $con->close();
				date_default_timezone_set("America/La_Paz");
				echo "<p>Ultima actualización a las ".date("h:i:sa")." </p>";
?>
       
        </div>

  	<script type="text/javascript">
		var csrf = '<?php echo $_SESSION["token"]; ?>';
        function recargar(){
			window.location.reload(false); 
		}
        $(document).ready(function(event){
            
        });
     </script>
    </body>
</html>