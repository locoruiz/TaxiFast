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
        <meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
        <link rel="shortcut icon" href="">
		
        <link rel="stylesheet" href="../estilo/principal.css"/>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
        <style type="text/css">
			
			.sidenav {
				height: calc(100% - 150px);
				width: 0;
				position: absolute;
				z-index: 99;
				top: 90px;
				left: 0;
				background-color: #e7e7e7;
				overflow-x: hidden;
				transition: 0.5s;
				padding: 10px;
				padding-left: 3px;
				padding-right: 0px;
				border-top-right-radius: 10px;
				border-bottom-right-radius: 10px;
			}

			.sidenav .closebtn {
				position: absolute;
				top: 0;
				right: 20px;
				font-size: 36px;
				margin-left: 50px;
			}
			.burger{
				border-radius: 5px;
				padding-top: 5px;
				padding-bottom: 5px;
				padding-right: 10px;
				padding-left: 10px;
				color: #e7e7e7;
				background-color: black;
				font-size:20px;
				cursor:pointer;
				position: absolute;
				top: 90px; left: 20px;
				z-index: 98;
			}

			@media screen and (max-height: 450px) {
			  .sidenav {padding-top: 15px;}
			  .sidenav a {font-size: 18px;}
			}
			
			 /* Always set the map height explicitly to define the size of the div
		    * element that contains the map. */
		   #map {
			 height: calc(100% - 80px);
		   }
			
			#mensaje {
				font-size: 13px;
				text-align: left;
				padding: 5px;
				color: red;
				background-color: #e7e7e7;
			}
		  /* Optional: Makes the sample page fill the window. */
		  html, body {
			height: 100%;
			margin: 0;
			padding: 0;
		  }
        </style>

    </head>
    <body >
    	<nav class="navbar navbar-default" style="margin-bottom: 0px">
		  <div class="container-fluid">
			<div class="navbar-header">
		  	  <a class="navbar-left" href="#"><img src="img/logo.png"/>&nbsp;&nbsp;</a>
		  	  <a class="navbar-brand" href="#">Taxi Fast</a>
			</div>
			<ul class="nav navbar-nav">
			  <li><a href="main.php">Inicio</a></li>
			  <li><a href="usuarios.php">Usuarios</a></li>
			  <li><a href="carreras.php">Carreras</a></li>
			  <li class="active"><a href="#">Mapa</a></li>
			</ul>
			<ul class="nav navbar-nav navbar-right">
			  <li><a href="#"><span class="glyphicon glyphicon-user"></span> Bienvenido <?php echo $usu; ?></a></li>
			  <li><a href="#" onclick="logout()"><span class="glyphicon glyphicon-log-out"></span> Cerrar Sesion</a></li>
			</ul>
		  </div>
		</nav>
        <div id="map"></div>
		<div id="mySidenav" class="sidenav" style="text-align: left">
		  <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
			<b style="padding-left: 20px">Taxistas</b>
		  <div class="list-group" id="listaTaxis" style="height: calc(100% - 60px);margin-top: 20px; width: 100%; overflow: auto">
		  </div>
		</div>
       <span class="burger" onclick="openNav()">&#9776;</span>
        <div id="mensaje">
        </div>
  	<script type="text/javascript">
		var csrf = '<?php echo $_SESSION["token"]; ?>';
		var map;
		var taxistas = [];
		var markers = [];
		var indices = {}; // id taxista llave, valor indice en el array
		var trayendoDatos = false;
		var infowindow;
		function openNav() {
			document.getElementById("mySidenav").style.width = "250px";
		}

		function closeNav() {
			document.getElementById("mySidenav").style.width = "0";
		}
		function logout(){
        	window.location.replace("logout.php?csrf="+csrf);
        }
        function recargar(){
			window.location.reload(false); 
		}
		function initMap(){
			map = new google.maps.Map(document.getElementById('map'), {
			  center: {lat: -21.533875205624717, lng: -64.73419189453125},
			  zoom: 15,
				mapTypeControl:false,
				streetViewControl:false
			});
			infowindow = new google.maps.InfoWindow();
			empezarAtraerDatos();
		}
		function formatDate(date) {
		  var monthNames = [
			"Enero", "Febrero", "Marzo",
			"Abril", "Mayo", "Junio", "Julio",
			"Agosto", "Septiembre", "Octubre",
			"Noviembre", "Diciembre"
		  ];

		  var day = date.getDate();
		  var monthIndex = date.getMonth();
		  var year = date.getFullYear();
			
			var hour = date.getHours();
			var minute = date.getMinutes();
			var second = date.getSeconds();
			
		  return 'El ' + day + ' de ' + monthNames[monthIndex] + ' de ' + year + ' a las ' + hour + ':' +minute+ ':' + second;
		}
		
		function infoWindowContent(taxi){
			var estado = "";
			switch(taxi.estado){
				case "HA":
					estado = "Libre";
					break;
				case "ES":
					estado = "Yendo a recoger a alguien";
					break;
				case "TA":
					estado = "Esperando que salga el cliente";
					break;
				case "AB":
					estado = "Llevando a un pasajero";
					break;
				default:
					estado = "Libre";
			}
			var aux = taxi.fechaPos.split(' ').join('T');
			var fecha = new Date(aux);
			fecha.setTime(fecha.getTime() + (3*60*60*1000)); // tres horas mas

			fecha = formatDate(fecha);

			var content = "";
			if(taxi.foto && taxi.foto != ""){
				content = '<div style="width:90%"><div class="row">'+
								'<div class="col-md-4">'+
									'<img class="img-thumbnail" width="100" src="'+taxi.foto+'">'+
								'</div>'+
								'<div class="col-md-8">'+
									'<div class="row">'+
										'<h3 class="col-md-12 text-left">'+taxi.nombre+'</h3>'+
									'</div>'+
									'<div class="row">'+
										'<b class="col-md-4">Licencia:</b><span class="col-md-8 text-left">'+taxi.licencia +'</span>'+
									'</div>'+
									'<div class="row">'+
										'<b class="col-md-4">Telefono:</b><span class="col-md-8 text-left">'+taxi.telefono +'</span>'+
									'</div>'+
									'<div class="row">'+
										'<b class="col-md-4">Estado:</b><span class="col-md-8 text-left">'+estado +'</span>'+
									'</div>'+
								'</div>'+
							'</div>'+
							'<div class="row">'+
								'<b class="col-md-5">Ultima conexion:</b><span class="col-md-7 text-left">'+fecha +'</span>'+
							'</div></div>';
			}else{
				content = '<div style="width:90%"><div class="row">'+
								'<div class="col-md-12">'+
									'<div class="row">'+
										'<h3 class="col-md-12 text-left">'+taxi.nombre+'</h3>'+
									'</div>'+
									'<div class="row">'+
										'<b class="col-md-4">Licencia:</b><span class="col-md-8 text-left">'+taxi.licencia +'</span>'+
									'</div>'+
									'<div class="row">'+
										'<b class="col-md-4">Telefono:</b><span class="col-md-8 text-left">'+taxi.telefono +'</span>'+
									'</div>'+
									'<div class="row">'+
										'<b class="col-md-4">Estado:</b><span class="col-md-8 text-left">'+estado +'</span>'+
									'</div>'+
								'</div>'+
							'</div>'+
							'<div class="row">'+
								'<b class="col-md-5">Ultima conexion:</b><span class="col-md-7 text-left">'+fecha +'</span>'+
							'</div></div>';
			}
			return content;
		}
		function animarAposicion(marker, newPosition){
			var frames = [];
			var fromLat = marker.position.latitude;
			var fromLng = marker.position.longitude;
			var toLat = marker.position.latitude;
			var toLng = marker.position.longitude;
			  for (var percent = 0; percent < 1; percent += 0.01) {
				curLat = fromLat + percent * (toLat - fromLat);
				curLng = fromLng + percent * (toLng - fromLng);
				frames.push(new google.maps.LatLng(curLat, curLng));
			  }
			  this.move = function(marker, latlngs, index, wait, newDestination) {
				  var me = this;
				marker.setPosition(latlngs[index]);
				if(index != latlngs.length-1) {
				  // call the next "frame" of the animation
				  setTimeout(function() { 
					me.move(marker, latlngs, index+1, wait, newDestination); 
				  }, wait);
				}
				else {
				  // assign new route
				  marker.position = newPosition;
				}
			  }

			  // begin animation, send back to origin after completion
			  this.move(marker, frames, 0, 20, marker.position);
		}
		function seleccionarTaxi(ind){
			google.maps.event.trigger(markers[ind], 'click');
			map.panTo(markers[ind].position);
		}
		function empezarAtraerDatos(){
			var date = new Date();
			console.log("trayendo datos " + date.getHours() + ":" + date.getMinutes() + ":" + date.getSeconds()+ ":" + date.getMilliseconds());
			if(trayendoDatos)
				return; // solo va a traer y enviar una a la vez
			var datos = {
				token : csrf,
				taxistas: taxistas
			};
			trayendoDatos = true;
			$("#mensaje").html("");
			$.post("taxistas_s.php", datos, function(data, status){
				trayendoDatos = false;
				var date = new Date();
				console.log("recibio datos " + date.getHours() + ":" + date.getMinutes() + ":" + date.getSeconds()+ ":" + date.getMilliseconds());
				if (status == "success") {
					try{
						var obj = $.parseJSON(data);
						if (obj.success > 0) {
							
							$.each(obj.taxistas, function(index, taxi){
								if(indices.hasOwnProperty(taxi.id)){
									var taxista = taxistas[indices[taxi.id]];
									taxista.latitud = taxi.latitud;
									taxista.latitud = taxi.latitud;
									taxista.fechaPos = taxi.fechaPos;
									taxista.estado = taxi.estado;
									
									var marker = markers[indices[taxi.id]];
									var newPosition = new google.maps.LatLng(taxi.latitud, taxi.longitud);
									//animarAposicion(marker, newPosition);
									marker.position = new google.maps.LatLng(taxi.latitud, taxi.longitud);
									console.log("actualizando taxista "+taxi.id);
								}else{ // en esta parte elegir si traer o no los viejos
									taxistas.push(taxi);
									indices[taxi.id] = taxistas.length - 1;
									var icon = {
										url: "img/pin.png", // url
										scaledSize: new google.maps.Size(50, 50), // scaled size
										origin: new google.maps.Point(0,0), // origin
										anchor: new google.maps.Point(0, 0) // anchor
									};
									
									var marker = new google.maps.Marker({
										title:taxi.nombre,
										position: new google.maps.LatLng(taxi.latitud, taxi.longitud),
										icon:icon,
										map:map
									});
									marker.indice = taxistas.length - 1;
									google.maps.event.addListener(marker,'click', (function(marker){ 
										return function() {
										   var taxista = taxistas[marker.indice];
										   infowindow.setContent(infoWindowContent(taxista));
										   infowindow.open(map,marker);
										};
									})(marker));
									markers.push(marker);
									
									$("#listaTaxis").append(
										'<a href="#" onclick="seleccionarTaxi('+marker.indice+')" class="list-group-item">'+taxi.nombre+'</a>'
									);
								}
							});
							if(obj.taxistas.length > 0){
								$("#mensaje").html(obj.taxistas.length+" taxistas actualizados");
							}
							setTimeout(function(){
								empezarAtraerDatos();
							}, 5000);
						}else{
							$("#mensaje").html(obj.mensaje);
						}
					}catch(e){
						console.log("error :"+e);
						console.log(data);
					}
				}else{
					/*
					setTimeout(function(){
						empezarAtraerDatos();
					}, 5000);*/
				}
			});
		}
     </script>
     <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_KEY&callback=initMap"
    async defer></script>
    </body>
</html>