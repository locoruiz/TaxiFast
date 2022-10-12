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
		<p>
       		<a href="usuarios.php">Ver usuarios</a> <br />
       		<a href="carreras.php">Ver carreras en curso</a><br />
       		<a href="taxistas.php">Ver posicion de taxistas</a>
       	</p>
        <div class="cont">
            <button style="float:left;" onclick="agregarNuevo()" id="btnMostrar">Agregar nuevo</button>
            <div id="formNuevo" style="display:none;">
                <h3 id="titulo">Registrar nuevo Taxista</h3>
                <form id="myForm" action="javascript:crearNuevo();">
                <table style="margin:auto;">
                    <tr><td style="text-align:right;"><label for="correo">Correo:</label></td><td><input type="email" name="correo" id="correo" style="width:100%;" required /></td><td><button onclick="verificar();">Verificar</button></td></tr>
                    <tr><td style="text-align:right;"><label for="nombre">Nombre:</label></td><td><input name="nombre" id="nombre" style="width:100%;" required/></td></tr>
                    <tr><td style="text-align:right;"><label for="apellidos">Apellidos:</label></td><td><input name="apellidos" id="apellidos" style="width:100%;" required/></td></tr>
                    <tr><td style="text-align:right;"><label for="telefono">Teléfono:</label></td><td><input type="tel" name="telefono" id="telefono" style="width:100%;" required/></td><td></tr>
                    <tr><td style="text-align:right;"><label for="licencia">Licencia:</label></td><td><input name="licencia" id="licencia" required/></td></tr>
                    <tr><td style="text-align:right;"><label for="foto">Foto:</label></td><td><img src="" id="fotoP"></br><input name="foto" id="foto" type="file" /></td></tr>
                </table>
                <img src="" id="cargando" /></br>
                <input type="submit" value="Registrar"  />
                </form>
            </div>
            <h3>Taxistas registrados en el sistema</h3>
            <input style="margin-bottom:10px;" type="text" id="buscador" /> <button onclick="buscar();">Buscar</button></br>
            <table class="lista" id="lista">
            

<?php
		$result = $con->Select("SELECT t.foto, u.id as idu, t.id, u.nombre, u.apellidos, u.correo, u.telefono, t.licencia, t.activo FROM Usuario u, Taxista t where t.id_usuario = u.id");
        $i = 0;
		echo "<tr><th>Nombre</th><th>Apellidos</th><th>Correo</th><th>Telefono</th><th>Licencia</th></tr>";
		while($fila = $result->fetch(PDO::FETCH_ASSOC)){
            $i++;
			echo "<tr id='".$fila["id"]."'><td id='".$fila["id"]."nombre'>".$fila["nombre"].
                "</td><td id='".$fila["id"]."apellidos'>".$fila["apellidos"].
                "</td><td id='".$fila["id"]."correo'>".$fila["correo"].
                "</td><td id='".$fila["id"]."telefono'>".$fila["telefono"].
                "</td><td id='".$fila["id"]."licencia'>".$fila["licencia"].
                "</td><td><button onclick=\"modificar('".$fila["id"]."');\">Modificar</button>".
                "<button onclick=\"eliminar('".$fila["id"]."');\">Eliminar</button></td>".
                "<input type='hidden' id='".$fila["id"]."id' value='".$fila["idu"]."' />".
                "<input type='hidden' id='".$fila["id"]."foto' value='".$fila["foto"]."'/>";
            if ($fila["activo"] == 0) {
                echo "<td id='".$fila["id"]."td'><button onclick='habilitar(".$fila["id"].");'>Habilitar</button></td>";
            }
            echo "</tr>";
		}
        echo "</table>";
        if($i == 0)
            echo "<p>No existen taxistas registrados</p>";

    $con->close();
?>

            <p><b>Nota: </b>Los taxistas habilitados podrán empeszar a trabajar automaticamente.</p>
        </div>


        <script type="text/javascript">

		var csrf = '<?php echo $_SESSION["token"]; ?>';
        var formdata;
        var id = -1;
        var mos = false;
        var modif = false;
        var fotoA;
        var idT;

        function modificar(idp){
            mos = true;
            if(mos){
                $("#btnMostrar").html("Ocultar");
            }
            $("#formNuevo").slideDown(1000);
            modif = true;
            $("#titulo").html("Modificar Taxista");
            $("#correo").val($("#"+idp+"correo").html());
            $("#nombre").val($("#"+idp+"nombre").html());
            $("#apellidos").val($("#"+idp+"apellidos").html());
            $("#telefono").val($("#"+idp+"telefono").html());
            $("#licencia").val($("#"+idp+"licencia").html());

            id = $("#"+idp+"id").val();
            idT = idp;

            var img  = document.getElementById("fotoP");
            img.width = "100";
            img.height = "100";
            img.alt = "No tiene foto";
            img.src = $("#"+idp+"foto").val();
            fotoA = $("#"+idp+"foto").val();
        }
        function eliminar(id){

            if (confirm("¿Seguro que quiere eliminar este registro?")) {
                $.post("eliminar.php", 
                {
                    id:id,
                    fotoA:$("#"+id+"foto").val(),
					token:csrf					
                }, 
                function(data, status){
                    if (status == "success") {
                        if (data.length == 0) {
                            // todo ok
                            $("#"+id).hide(900);
                        }else{
                            alert("hubo un error:\n"+data);
                        }
                    }else{
                        alert("hubo un error: "+status);
                    }
                });
            };
        }

        function habilitar(id){
        	$.post("habilitar.php", 
                {
                    id:id,
					token:csrf
                }, 
                function(data, status){
                    if (status == "success") {
                        if (data.length == 0) {
                            // todo ok                        
                            $("#"+id+"td").hide(900);
                        }else{
                            alert("hubo un error:\n"+data);
                        }
                    }else{
                        alert("hubo un error: "+status);
                    }
                });
        }
        function logout(){
        	window.location.replace("logout.php?csrf="+csrf);
        }

        function agregarNuevo(){            
            mos = !mos;
            if(mos){
                $("#btnMostrar").html("Ocultar");
                limpiar();
            }else{
                $("#btnMostrar").html("Registrar Nuevo");
            }
            $("#formNuevo").slideToggle(1000);
        }
        function verificar(){
            //verificar si existe un usuario con ese correo, si existe preguntar si es él, si no crear nuevo usuario
            var carg = document.getElementById("cargando");
            carg.src = "img/indicator.gif";
            carg.width = "50";
            carg.height = "50";
            $("#btn").prop("disabled",true);


            formd = new FormData();
            formd.append("correo", $("#correo").val());
            formd.append("token", csrf);

            $.ajax({
            url: "verificar.php",
            type: "POST",
            data:formd,
            processData: false,  
            contentType: false,  
            success: function (res) {
                carg.width=0;
                carg.height=0;
                $("#btn").prop("disabled",false);
                var obj = JSON.parse(res);
                if (obj.success == 1) {

                    if (obj.esTaxi == true) {
                        alert("Este correo ya tiene cuenta de taxista registrado");
                        limpiar();
                        carg.width=0;
                        carg.height=0;
                        $("#btn").prop("disabled",false);
                        return;
                    }
                    // ya estaba creado
                    $("#nombre").val(obj.nombre);
                    $("#apellidos").val(obj.apellidos);
                    $("#telefono").val(obj.telefono);
                    $("#licencia").focus();
                    id = obj.id;
                }else if(obj.success == 0){
                    alert(obj.msj);
                }else{
                    $("#nombre").focus();
                }
            }
            });
        }

        function limpiar(){
            id = -1;
            $("#telefono").val("");
            $("#nombre").val("");
            $("#apellidos").val("");
            $("#licencia").val("");
            $("#foto").val("");
            $("#correo").val("");
            $("#correo").focus();
            formdata = new FormData();
            var img  = document.getElementById("fotoP");
            img.width = 0;
            img.height = 0;
            modif = false;
            $("#titulo").html("Registrar nuevo Taxista");
        }

        function crearNuevo(){

            var $myForm = $('#myForm');
            if (!$myForm[0].checkValidity()) {
                // If the form is invalid, submit it. The form won't actually submit;
                // this will just cause the browser to display the native HTML5 error messages.
                $myForm.find(':submit').click();
                //alert("form no valido");
                return;
            }


            //enviar el formulario y agregar un nuevo taxi
            formdata.append("nombre", $("#nombre").val());
            formdata.append("apellidos", $("#apellidos").val());
            formdata.append("licencia", $("#licencia").val());
            formdata.append("telefono", $("#telefono").val());
            formdata.append("correo", $("#correo").val());
            formdata.append("modif", modif);
            formdata.append("fotoA", fotoA);
            formdata.append("id", id);
            formdata.append("idT", idT);
			formdata.append("token", csrf);

            var carg = document.getElementById("cargando");
            carg.src = "img/indicator.gif";
            carg.width = "50";
            carg.height = "50";
            $("#btn").prop("disabled",true);
            $.ajax({
            url: "registrarTaxista.php",
            type: "POST",
            data:formdata,
            processData: false,  
            contentType: false,  
            success: function (res) {
                carg.width=0;
                carg.height=0;
				try{
					var obj = $.parseJSON(res);
					if (obj.success > 0) {
						// todo ok
						// agregar a la tabla
						//$("#lista").append("<tr id="+obj.id+"><td>"+obj.nombre+"</td><td>"+obj.apellidos+"</td><td>"+obj.correo+"</td><td>"+obj.telefono+"</td><td>"+obj.licencia+"</td>");
						alert(obj.msj);
						window.location.reload(false); 
						limpiar();
						$("#btn").prop("disabled",false);
					}else{
						alert(obj.msj);
					}
				}catch(e){
					alert(res);
				}
            }
            });
        }

        function buscar(){
            var busq = $("#buscador").val();
            $.post("buscar.php", 
                {
                    buscar:busq,
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
            if (window.FormData){
                formdata = new FormData();
            }
            var input = document.getElementById("foto");
            if(input.addEventListener){
                input.addEventListener("change", function(evt){
                    var i = 0, len = this.files.length, img, reader, file;  
                    for (; i< len; i++){
                        file = this.files[i];
                    if (!!file.type.match(/image.*/)) {  
                        if (window.FileReader){
                            reader = new FileReader();
                            reader.onloadend = function (e) {
                                showUploadedItem(e.target.result);
                            };
                            reader.readAsDataURL(file);
                        }
                        if (formdata){
                            formdata.append("foto", file);  
                        }
                     }
                    }
                }, false);
            }
        });

        function showUploadedItem (source) {  
            img  = document.getElementById("fotoP");
            img.width = 100;
            img.height = 100;
            img.src = source;
        }

        </script>
    </body>
</html>