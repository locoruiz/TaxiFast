<?php
	include "Conexion.php";
	$con = ConexionDeFBE();

?>

<!DOCTYPE html>
<html>
    <head>
        <!-- este es un comentario -->
        <title>Taxi Fast</title>
        <meta charset="UTF-8">
        <link rel="shortcut icon" href="">
        <script src="../jquery-3.0.0.js"></script>

        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="http://www.w3schools.com/lib/w3.css">

        <link rel="stylesheet" href="../estilo/principal.css"/>
        <style type="text/css">
        
        .cont{
            border-radius: 10px;
            background-color: rgb(250, 250, 250);
            box-shadow: 3px 3px 3px grey;
            width: 400px;
            max-width: 400px;
            margin: auto;
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
    <body style="text-align:center;">
        <div class="w3-container w3-lime">
            <img src="img/logo.jpg" width="60px" height="60px" >
            <h1>Taxi Fast</h1>
        </div>
        <div class="w3-container">
            <div id="formNuevo">
                <h3 id="titulo">Recuperación de Contraseña</h3>
                <?php
                if (isset($_GET["id"]) && isset($_GET["codigo"])) {
                    $result = $con->Select("select id from rec_p where id = ".$con->validar($_GET["id"])." and codigo = ".$con->validar($_GET["codigo"]));
                    if ($result->rowCount() > 0) {
                ?>
                <form id="myForm" action="javascript:crearNuevo();">
                <table style="margin:auto;">
                    <tr><td style="text-align:right;"><label for="password">Nueva Contraseña:</label></td><td><input class="w3-input" type="password" name="password" id="password" style="width:100%;" onkeyup="checkSize()" required /></td><label style="color:red" id="error1"></label></tr>
                    <tr><td style="text-align:right;"><label for="passwordr">Repita la contraseña:</label></td><td><input class="w3-input" type="password" name="passwordr" id="passwordr" style="width:100%;" onkeyup="revisar()" required/><label style="color:red" id="error"></label></td></tr>
                </table>
                <img src="" id="cargando" /></br>
                <input type="submit" value="enviar"  id="btn" class="w3-btn"/>
                </form>
                <?php
                    }else{
                        echo "<h3>Este link ya expiró</h3>";
                    }
                }else{
                ?>
                <h3>Hubo un error, no se puede recuperar la contraseña</h3>
                <?php
                }
                ?>
            </div>           
        </div>

        <script type="text/javascript">

        var formdata;
        var id = -1;
        var mos = false;
        var modif = false;
        var fotoA;
        var idT;
        var tam = false;
        var iguales = false;

        function checkSize(){
            if ($("#password").val().length <= 4) {
                $("#error1").html("Las contraseña es muy pequeña!!");
                tam = false;
            }else{
                $("#error1").html("");
                tam = true;
            }
            actualizar();
        }

        function revisar(){
            actualizar();
        }
        function actualizar(){
            

            if ($("#password").val() != $("#passwordr").val()) {
                $("#error").html("Las contraseñas no son iguales!!");
                iguales = false;
            }else{
                $("#error").html("");
                iguales = true;
            }

            if (tam && iguales) {
                $("#btn").prop("disabled",false);
            }else
                $("#btn").prop("disabled",true);
        }

        function crearNuevo(){

			if ($("#password").val() != $("#passwordr").val()) {
        		$("#error").html("Las contraseñas no son iguales!!");
        		return;
        	}else{
        		$("#error").html("");
        	}

            var $myForm = $('#myForm');
            if (!$myForm[0].checkValidity()) {
                // If the form is invalid, submit it. The form won't actually submit;
                // this will just cause the browser to display the native HTML5 error messages.
                

                $myForm.find(':submit').click();
                //alert("form no valido");
                return;
            }


            //enviar el formulario y agregar un nuevo taxi
            formdata.append("password", $("#password").val());
            formdata.append("id", <?php echo htmlspecialchars($_GET["id"]); ?>);
            formdata.append("codigo", "<?php echo htmlspecialchars($_GET['codigo']); $con->close() ?>"); 


            var carg = document.getElementById("cargando");
            carg.src = "img/indicator.gif";
            carg.width = "50";
            carg.height = "50";
            $("#btn").prop("disabled",true);
            $.ajax({
            url: "cambiarpasswordserver.php",
            type: "POST",
            data:formdata,
            processData: false,  
            contentType: false,  
            success: function (res) {
                //alert(res);
                carg.width=0;
                carg.height=0;
                $("#btn").prop("disabled",false);
                var obj = JSON.parse(res);
                if (obj.success == 1) {
                    // todo ok
                    alert("Password Cambiado correctamente");
                    close();
                    //$("#formNuevo").hide(1000);
                }else{
                    alert(obj.mensaje);
                }
            }
            });
        }

        $(document).ready(function(event){
            actualizar();
            if (window.FormData){
                formdata = new FormData();
            }            
        });

        </script>
    </body>
</html>