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
       		<a href="carreras.php">Ver carreras en curso</a><br />
       		<a href="taxistas.php">Ver posicion de taxistas</a>
        <div class="cont">
            <h3>Usuarios Registrados en el Sistema</h3>
            <input style="margin-bottom:10px;" type="text" id="buscador" /> <button onclick="buscar();">Buscar</button></br>
            <table class="lista" id="lista">
<?php
		$result = $con->Select("SELECT * from Usuario order by cancelados DESC");
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
        echo "</table>";
        if($i == 0)
            echo "<p>No existen usuarios registrados</p>";
    $con->close();
?>

            <p><b>Nota: </b>Los usuarios bloqueados no podran realizar pedidos!.</p>
        </div>

  	<script type="text/javascript">
		var csrf = '<?php echo $_SESSION["token"]; ?>';
        var formdata;
        var mos = false;
        var modif = false;
        var idT;

        function bloquear(id, valor){
			$.post("bloquear.php", 
			{
				id:id,
				valor:valor,
				token:csrf					
			},
			function(data, status){
				if (status == "success") {
					if ($.trim(data.length) == 0) {
						// todo ok
						var html;
						valor = parseInt(valor);
						if(valor == 0){
							html = "<button onclick='bloquear("+id+", 1);'>Bloquear</button>";
						}else
							html = "<button onclick='bloquear("+id+", 0);'>Desloquear</button>";
						$("#"+id+"td").html(html);
					}else{
						alert("hubo un error:\n"+data);
					}
				}else{
					alert("No pudo cargar. Status: "+status);
				}
			});
        }
        function logout(){
        	window.location.replace("logout.php?csrf="+csrf);
        }

        function buscar(){
            var busq = $("#buscador").val();
            $.post("buscar.php", 
                {
                    buscar:busq,
					usuarios:1,
					token:csrf
                }, 
                function(data, status){
                    if (status == "success") {
                        $("#lista").html(data);
                    }else{
                        alert("hubo un error: "+status);
                    }
                });
        }
			
        $(document).ready(function(event){
            
        });
     </script>
    </body>
</html>