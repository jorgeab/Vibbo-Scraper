<html>
<title>Vibbo Scraper</title>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1' />
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
<link rel="stylesheet" href="./style.css">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>
</head>
<body>

<div class=" vertical-center">
  <div class="container text-center" >
  <div><img src="./img/titulo.png"></img></div>
    <font color="white">
    <form enctype="multipart/form-data" method="POST">
    
            <div><h4>    Numero de paginas: 
                <div>
                    <input  type = "text" style="margin-top:14;width: 128px;" name="pagina_inicial" id="pagina_inicial" placeholder="1" value="<?php echo @htmlspecialchars($_POST['pagina_inicial']); ?>" required="true">
                      -
                    <input  type = "text" style="margin-top:14;width: 128px;" name="pagina_final" placeholder="580" value="<?php echo @htmlspecialchars($_POST['pagina_final']); ?>" required="true">
                </div></h4>
			</div><center>
			<?php	(isset($_POST["opcionVehiculo"])) ? $opcion = $_POST["opcionVehiculo"] : $opcion="Coches";?>
			<select class="selectpicker"  style="display: block; width: 280px; margin-top:10" id="opcionVehiculo" name="opcionVehiculo">
			  <option value="Coches" <?php if( $opcion=="Coches"){echo "selected";}?>>Coches</option>
			  <option value="Motos" <?php if( $opcion=="Motos"){echo "selected";}?>>Motos</option>
			</select>

            <button id = "enviar" name = "enviar" class="btn btn-primary" style="display: block; width: 280px; margin-top:10">Scrapear</button>
            <div id="cancelar"></div>
            <div class="progress" id="progress" style="margin-top:10;background-color:gray;width: 280px;"></center>
				<!-- Progress information -->
				<div id="information" style="color:white"></div>
				<div id="information2" style="color:white"></div>
			</div>
    </form>
<?php

	ini_set("user_agent", "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1");
	ini_set('max_execution_time', 99999999999);

	function main() {
		
		$pagina_final = $_POST['pagina_final'];
		$pagina_inicial = $_POST['pagina_inicial'];
		$opcionVehiculo = $_POST['opcionVehiculo'];
		$total = $pagina_final - $pagina_inicial;
		$contador = 0;
		
		if($opcionVehiculo == "Coches"){
				include 'script_datos_coche.php';
				$html = @file_get_html("http://www.vibbo.com/coches-de-segunda-mano-toda-espana/coche.htm?o=$pagina_inicial");
		}else{
				include 'script_datos_moto.php';
				$html = @file_get_html("http://www.vibbo.com/motos-de-segunda-mano-toda-espana/moto.htm?o=$pagina_inicial"); 
		}
		
		if ($pagina_inicial > $pagina_final) {
			print "<script>alert('Rango de páginas incorrecto.');</script>";
			exit;
		}
		
		for ($pagina_inicial; $pagina_inicial <= $pagina_final; $pagina_inicial++) {
			/*Barra de progreso*/
			$percent = intval($contador / $total * 100) . "%";
			$num_anuncios = $contador * 24;
			$num_anuncios_tot = $total * 24;
			echo '<script language="javascript">
			document.getElementById("cancelar").innerHTML = "<button class=\"btn btn-danger\" style=\"display: block; width: 280px; margin-top:6;\">stop</button>";
			document.getElementById("progress").innerHTML="<div style=\"width:' . $percent . ';background-color:#458B00;\" class=\"progress-bar progress-bar-striped progress-bar-animated\">&nbsp;</div>";        
			document.getElementById("information").innerHTML="Pagina: ' . $contador . '  de ' . $total . ' | ' . $percent . '";
			document.getElementById("information2").innerHTML="Anuncios: ' . $num_anuncios . '  de ' . $num_anuncios_tot . '";
			 </script>';
			echo str_repeat(' ', 1024 * 64);
			flush();
			sleep(1);

			if ($html != "") {
				$items_no_dup = array_unique(buscar_links($html));
				$links_pagina = limpiar_provincias($items_no_dup);
				
				if ($links_pagina != "") {
					foreach ($links_pagina as $link) {
						script_datos($link);
					}
				}
			}
			$contador++;
		}
		print "<script>alert('¡Extracción finalizada!.');</script>";
	}

	/*******BUSCAR_PROVINCIA*******/
	function buscar_provincia($prov) {
		$provincias = array('albacete', 'alicante', 'almeria', 'araba', 'asturias', 'avila', 'badajoz', 'balears', 'barcelona',
										'bizkaia', 'burgos', 'caceres', 'cadiz', 'cantabria', 'castellon', 'ciudad real', 'cordoba', 'a coruna',
										'cuenca', 'gipuzkoa', 'girona', 'granada', 'guadalajara', 'huelva', 'huesca', 'jaen', 'leon', 'lleida',
										'lugo', 'madrid', 'malaga', 'murcia', 'navarra', 'ourense', 'palencia', 'las palmas', 'pontevedra',
										'la rioja', 'salamanca', 'santa cruz de tenerife', 'segovia', 'sevilla', 'soria', 'tarragona', 'teruel',
										'toledo', 'valencia', 'valladolid', 'zamora', 'zaragoza', 'ceuta', 'melilla');
		
		foreach ($provincias as $provincia) {
			if (stripos($prov, $provincia)) {
				return true;
			}
		}
		return false;
	}

	/*******BUSCAR_LINKS*******/
	function buscar_links($html) {
		$i = -1;
		foreach ($html->find('a') as $element) {
			$items[$i++] = $element->href;
		}
		return $items;
	}

	/*******LIMPIAR_PROVINCIAS*******/
	function limpiar_provincias($items_no_dup) {
		$i = -1;
		foreach ($items_no_dup as $element) {
			
			if (buscar_provincia($element)) {
				$links[$i++] = $element;
			}
		}
		return $links;
	}

	if (array_key_exists('enviar', $_POST)) {
		main();
	} else if (array_key_exists('stop', $_POST)) {
		exit;
	}

?>
</div>
</div>
</body>
</html>