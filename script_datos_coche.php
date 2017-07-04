<?php
	include 'scrap.php';
	ini_set("user_agent", "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1");
	ini_set('max_execution_time', 99999999999);
		
	function script_datos($link){
		$html = "";
		$link = limpiar_link($link);
		
		$link = "http:" .$link; //Para link de motos
		$html = @file_get_contents($link);

		if($html != ""){		
			$html_limpio = limpiar($html);
			$array = preg_split('/\s+/', $html_limpio); //Separar por tab, espacios..
			list ($id, $nombre, $telefono, $tipo_usuario, $pais) = generar_datos_usuario($array);
			list($id_anuncio, $tipo_vehiculo, $titulo_anuncio, $fecha_creacion, $hora_creacion, $fecha_publicacion, $hora_publicacion, $marca, $modelo, $version, $anyo, $precio, $km) = generar_datos_anuncio($array);
			$provincia = get_provincia($link);
			if ($id != "" || $nombre != "" || $telefono != "" || $tipo_usuario != "" || $pais != "" || 
				$id_anuncio != "" || $tipo_vehiculo != "" || $titulo_anuncio != "" || $fecha_creacion != "" || 
				$hora_creacion != "" || $fecha_publicacion != "" || $hora_publicacion != "" || $marca != "" || 
				$modelo != "" || $version != "" || $anyo != "" || $precio != "" || $km != "" || $link != "" || $provincia != ""){
					
					guardar_datos($id, $nombre, $telefono, $tipo_usuario, $pais, $provincia, $id_anuncio, $tipo_vehiculo, $titulo_anuncio, $fecha_creacion, $hora_creacion, $fecha_publicacion, $hora_publicacion, $marca, $modelo, $version, $anyo, $precio, $km, $link);
			}
		}
	}


	function guardar_datos($id, $nombre, $telefono, $tipo_usuario, $pais, $provincia, $id_anuncio, $tipo_vehiculo, $titulo_anuncio, $fecha_creacion, $hora_creacion, $fecha_publicacion, $hora_publicacion, $marca, $modelo, $version, $anyo, $precio, $km, $link){
		
		$datos = $id. ";" .$nombre. ";" .$telefono. ";" .$tipo_usuario. ";" .$pais. ";" .$provincia. ";" .$id_anuncio. ";" .$tipo_vehiculo. ";" .$titulo_anuncio. ";" .$fecha_creacion. ";" .$hora_creacion. ";" .$fecha_publicacion. ";" .$hora_publicacion. ";" .$marca. ";" .$modelo. ";" .$version. ";" .$anyo. ";" .$precio. ";" .$km. ";" .$link. PHP_EOL; //PHP_EOL -> Nueva linea
		$archivo = @fopen("VIBBO_BIGDATA.csv", "a+");

		if (!$archivo) {  
			print "<script>alert('Error en el archivo generado.');</script>";exit;  
		}else{
			fwrite($archivo, utf8_decode(utf8_encode($datos)));
		}
		fclose($archivo);
	}

	function generar_datos_usuario($array){
		
		$nombre = "";
		$nombre_completo = 0;
		$telefono = "000000000";
		
		for($c=1; $c<count($array); $c++){
			
			/****ID****/
			if (strlen(strstr($array[$c] ,"adUserId"))>0) {
				//$array_id = explode("=", $array[$c+1]);
				$id = $array[$c+2];
			}

			/****NOMBRE****/
			if ( $array[$c-1] == "company:" || $nombre_completo != 0) {
				$nombre = $nombre . $array[$c] ;// . " ";
				$nombre_completo = 1;
				
				if ($array[$c+1] == "site:"){
					$nombre_completo = 0;
				}else{
					$nombre = $nombre . " ";
				}
			}
		
			/****TELEFONO****/
			if (strlen(strstr($array[$c] ,"tel:"))>0) {
				$array_tel = explode(":", $array[$c]);
				$telefono = $array_tel[1];
			}

			/****TIPO****/
			if (strlen(strstr($array[$c] ,"ad_publisher_type:"))>0) {
				$tipo_usuario = $array[$c+1];
			}

			/****PAIS****/
			if (strlen(strstr($array[$c] ,"country:"))>0) {
				$pais = $array[$c+1];
				if ($pais == "espana"){$pais = "España";}
			}
		}
		/*
		for($c=0; $c<count($array); $c++){
			if (stripos($array[$c], "matriculacion:")) {
				echo $array[$c+1];
			}
		}
		*/
		//echo $id; echo $nombre; echo $telefono; echo $tipo_usuario; echo $pais;
		return array($id, $nombre, $telefono, $tipo_usuario, $pais);
	}

	function limpiar($html){
		$no_permitidas= array ("á","é","í","ó","ú","Á","É","Í","Ó","Ú","ñ","À","Ã","Ì","Ò","Ù","Ã™","Ã ","Ã¨","Ã¬","Ã²","Ã¹","ç","Ç","Ã¢","ê","Ã®","Ã´","Ã»","Ã‚","ÃŠ","ÃŽ","Ã”","Ã›","ü","Ã¶","Ã–","Ã¯","Ã¤","«","Ò","Ã","Ã„","Ã‹");
		$permitidas= array ("a","e","i","o","u","A","E","I","O","U","n","N","A","E","I","O","U","a","e","i","o","u","c","C","a","e","i","o","u","A","E","I","O","U","u","o","O","i","a","e","U","I","A","E");
		$html = str_replace($no_permitidas, $permitidas ,$html);
		//$html = strip_tags($html); //eliminar etiquetas html
		$html = str_replace('"',"",$html); //quitar comillas
		$html = str_replace(';',"",$html); //quitar comillas
		$html = str_replace("&aacute;","a",$html);
		$html = str_replace("&Aacute;","A",$html);
		$html = str_replace("&eacute;","e",$html);
		$html = str_replace("&Eacute;","E",$html);
		$html = str_replace("&iacute;","i",$html);
		$html = str_replace("&Iacute;","I",$html);
		$html = str_replace("&oacute;","o",$html);
		$html = str_replace("&Oacute;","O",$html);
		$html = str_replace("&uacute;","u",$html);
		$html = str_replace("&Uacute;","U",$html);
		$html = str_replace(",","",$html);
		
		return $html;
	}

	function generar_datos_anuncio($array){
		
		$nombre_completo = 0;
		$modelo_completo = 0;
		$version_completa = 0;
		$titulo_anuncio= "";
		$modelo = "";
		$version = "";
		$marca = "";
		$km = "";
		$id_anuncio="";$tipo_vehiculo="";$fecha_creacion="";$hora_creacion="";$fecha_publicacion="";$hora_publicacion="";$anyo="";$precio="";
		
		for($c=1; $c<count($array); $c++){
			
			/****ID_ANUNCIO****/
			if ( $array[$c] == "ad_id:") {
				if(strlen(strstr($array[$c+1] ,"sm-"))){
					$id_tmp = explode("-", $array[$c+1]);
					$id_anuncio =  $id_tmp[1];
				}else{
					$id_anuncio = $array[$c+1];
				}
			}

			/****TIPO_VEHICULO****/
			if ( $array[$c] == "subcategory1_id:" ) {
				if($array[$c+1] == "4"){
					$tipo_vehiculo = "coche";
				}else{
					$tipo_vehiculo = "moto";
				}
			}
			
			/****TITULO_ANUNCIO****/
			if ( $array[$c-1] == "ad_title:" || $nombre_completo != 0) {
				$titulo_anuncio = $titulo_anuncio . $array[$c] ;// . " ";
				$nombre_completo = 1;
				
				if ($array[$c+1] == "country:"){
					$nombre_completo = 0;
				}else{
					$titulo_anuncio = $titulo_anuncio . " ";
				}
			}

			/****FECHA Y HORA CREACION****/
			if ($array[$c] == "create_date:") {
				$fecha_creacion = $array[$c+1];
				$hora_creacion = $array[$c+2];
			}
			
			/****FECHA Y HORA PUBLICACION****/
			if ($array[$c] == "publish_date:") {
				$fecha_publicacion = $array[$c+1];
				$hora_publicacion = $array[$c+2];
			}
			
			/****MARCA_COCHE****/
			if ($array[$c] == "brand_id:") {
				$marca = get_marca($array[$c+1]);
			}
			
			/****MODELO_COCHE****/
			if ( ($array[$c] == "modelo" && $array[$c+1] == "=")|| $modelo_completo != 0) {
				$modelo_tmp = $array[$c+2];
				$modelo_completo = 1;
				
				if (strlen(strstr($array[$c+2] ,"</script>"))){
					$modelo_completo = 0;
				}else{
					$modelo = $modelo . " ". $modelo_tmp;
				}
				//$modelo = strip_tags($modelo);
			}
			
			/****VERSION_COCHE****/
			if ( $array[$c-1] == "version:" || $version_completa != 0) {
				$version = $version . $array[$c] ;
				$version_completa = 1;
				
				if ($array[$c+1] == "is_msite:"){
					$version_completa = 0;
				}else{
					$version = $version . " ";
				}
			}
			
			/****AÑO_COCHE****/
			if ($array[$c] == "year:") {
				$anyo =  $array[$c+1];
			}

			/****PRECIO_COCHE****/
			if ($array[$c] == "price:") {
				$precio =  $array[$c+1];
			}
		
		
			/****KILOMETROS_COCHE****/
			if ($array[$c] =="km:") {
				//if (strlen(strstr($array[$c] ,"Km:"))) {
				//if (strlen(strstr($array[$c+1] ,"Mas"))){
					//$km = "200000";
				//}else{
					//echo $array[$c+1]. " | ";
				//$km = str_replace(".","", $array[$c+1]);
				//$km = strip_tags($km);
				//$entrar=$entrar+1;
				
				$km = str_replace(".","", $array[$c+1]);
				$km = strip_tags($km);	
				$km = $array[$c+1];	
			}

		}
		
		if ($marca == ""){$tit_temp = explode(" ", $titulo_anuncio); $marca = $tit_temp[0];}
		if ($km == ""){$km = "200000";}
		
		return array($id_anuncio, $tipo_vehiculo, $titulo_anuncio, $fecha_creacion, $hora_creacion, $fecha_publicacion, $hora_publicacion, $marca, $modelo, $version, $anyo, $precio, $km);
		
		//echo $id_anuncio. " " .$tipo_vehiculo. " " .$titulo_anuncio. " " .$fecha_creacion. " " .$hora_creacion. " " .$fecha_publicacion. " " .$hora_publicacion. " " .$marca. " " .$modelo. " "  .$version. " " .$anyo. " " .$precio. " " .$km;
		
	}

	function limpiar_link($link){
		$link1 = array("");
		$link1 = explode("/", $link);
		return $link_limpio = $link1[0]. "/".$link1[1]."/".$link1[2]."/".$link1[3]."/".$link1[4]."/".$link1[5]."/";
	}

	function get_marca($id_marca){
		$marcas= array("158","1331","1332","1333","161","1334","1335","1336","159","163","1337",
									"1338","162","1339","87","1340","1341","1342","1343","1344","1330","1",
									"238","1326","2","3","4","111","1321","6","241","7","8","9","10","11","1327",
									"1011","12","102","13","145","173","146","14","15","16","191","69","234",
									"18","1025","185","19","126","103","20","21","22","153","243","23","24",
									"128","25","147","246","26","1323","27","28","29","222","30","149","31",
									"32","33","112","34","35","36","37","38","1328","39","40","41","42","43",
									"44","156","45","46","1324","1325","47","48","186","4","7","11","15","69",
									"28","31","32","33","35","39","46","47","48");
		$nombre_marcas = array("AIXAM","BELLIER ","BUNKER-TRIKE ","CASALINI","CHATENET ","ERAD ",
												  "GRECAV ","ITAL CAR ","JDM","LIGIER ","MEGA ","MELEX ","MICROCAR",
												  "PGO SCOOTERS","PIAGGIO ","POLARIS","QUOVIS ","TASSO","VIDI",
												  "ZEST","ABARTH ","ALFA ROMEO","ARO ","ASIA ","ASIA MOTORS",
												  "ASTON MARTIN ","AUDI ","AUSTIN ","AUVERLAND","BENTLEY ","BERTONE",
												  "BMW","CADILLAC","CHEVROLET ","CHRYSLER ","CITROEN ","CORVETTE",
												  "DACIA ","DAEWOO ","DAF ","DAIHATSU ","DAIMLER","DODGE","FERRARI",
												  "FIAT ","FORD","GALLOPER","GMC ","HONDA ","HUMMER ","HYUNDAI ",
												  "INFINITI","INNOCENTI","ISUZU ","IVECO","IVECO-PEGASO","JAGUAR ",
												  "JEEP ","KIA ","LADA ","LAMBORGHINI ","LANCIA ","LAND-ROVER","LDV ",
												  "LEXUS ","LOTUS ","MAHINDRA ","MASERATI ","MAYBACH ","MAZDA ",
												  "MERCEDES-BENZ ","MG ","MINI ","MITSUBISHI ","MORGAN ","NISSAN ",
												  "OPEL ","PEUGEOT ","PONTIAC ","PORSCHE ","RENAULT ","ROLLS-ROYCE ",
												  "ROVER ","SAAB ","SANTANA ","SEAT ","SKODA ","SMART ","SSANGYONG ",
												  "SUBARU ","SUZUKI ","TALBOT ","TATA ","TOYOTA ","UMM ","VAZ ","VOLKSWAGEN ",
												  "VOLVO ","WARTBURG ","AUDI ","BMW ","CITROEN ","FORD ","HONDA ",
												  "MERCEDES-BENZ ","NISSAN ","OPEL ","PEUGEOT ","RENAULT ","SEAT ","TOYOTA ",
												  "VOLKSWAGEN ","VOLVO");
		$posicion = array_search($id_marca, $marcas);
		
		return $nombre_marcas[$posicion];
	}

	function get_provincia($link){
		$provincias = array('albacete', 'alicante', 'almeria', 'araba', 'asturias', 'avila', 'badajoz', 'balears', 'barcelona', 'bizkaia', 'burgos', 'caceres', 'cadiz', 'cantabria', 'castellon', 'ciudad real', 'cordoba', 'a coruna', 'cuenca', 'gipuzkoa', 'girona', 'granada', 'guadalajara', 'huelva', 'huesca', 'jaen', 'leon', 'lleida', 'lugo', 'madrid', 'malaga', 'murcia', 'navarra', 'ourense', 'palencia', 'las palmas', 'pontevedra', 'la rioja', 'salamanca', 'santa cruz de tenerife', 'segovia', 'sevilla', 'soria', 'tarragona', 'teruel', 'toledo', 'valencia', 'valladolid', 'zamora', 'zaragoza', 'ceuta', 'melilla');

		foreach ($provincias as $provincia) {
			if (stripos($link, $provincia)) {
				return $provincia;
			}
		}
	}

?>