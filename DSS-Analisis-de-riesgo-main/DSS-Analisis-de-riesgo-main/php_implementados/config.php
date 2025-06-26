<?php
// Configurar codificación UTF-8 al inicio
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

// Configuración para Docker
$servername = "db";           // Antes: "localhost"
$username = "usuario";        // Antes: "root"
$password = "clave";           // Antes: "TU_CONTRASEÑA"
$database = "dss_db";           // El nombre de la base de datos donde importaste todas las tablas

// Crear conexión para verificar si es necesario
$conn = new mysqli($servername, $username, $password, $database);

// Verificación de conexión
if ($conn->connect_error) {
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

// IMPORTANTE: Configurar charset UTF-8 para MySQL
$conn->set_charset("utf8");

// Mapa de nombres completos de estados y sus IDs
$estados_nombres = array(
    'AGS' => array('id' => 1, 'nombre' => 'Aguascalientes'),
    'BC' => array('id' => 2, 'nombre' => 'Baja California'),
    'BCS' => array('id' => 3, 'nombre' => 'Baja California Sur'),
    'CAMP' => array('id' => 4, 'nombre' => 'Campeche'),
    'CHIS' => array('id' => 5, 'nombre' => 'Chiapas'),
    'CHIH' => array('id' => 6, 'nombre' => 'Chihuahua'),
    'COAH' => array('id' => 7, 'nombre' => 'Coahuila'),
    'COL' => array('id' => 8, 'nombre' => 'Colima'),
    'CDMX' => array('id' => 9, 'nombre' => 'Ciudad de México'),
    'DGO' => array('id' => 10, 'nombre' => 'Durango'),
    'GTO' => array('id' => 11, 'nombre' => 'Guanajuato'),
    'GRO' => array('id' => 12, 'nombre' => 'Guerrero'),
    'HGO' => array('id' => 13, 'nombre' => 'Hidalgo'),
    'JAL' => array('id' => 14, 'nombre' => 'Jalisco'),
    'MEX' => array('id' => 15, 'nombre' => 'Estado de México'),
    'MICH' => array('id' => 16, 'nombre' => 'Michoacán'),
    'MOR' => array('id' => 17, 'nombre' => 'Morelos'),
    'NAY' => array('id' => 18, 'nombre' => 'Nayarit'),
    'NL' => array('id' => 19, 'nombre' => 'Nuevo León'),
    'OAX' => array('id' => 20, 'nombre' => 'Oaxaca'),
    'PUE' => array('id' => 21, 'nombre' => 'Puebla'),
    'QRO' => array('id' => 22, 'nombre' => 'Querétaro'),
    'QR' => array('id' => 23, 'nombre' => 'Quintana Roo'),
    'SLP' => array('id' => 24, 'nombre' => 'San Luis Potosí'),
    'SIN' => array('id' => 25, 'nombre' => 'Sinaloa'),
    'SON' => array('id' => 26, 'nombre' => 'Sonora'),
    'TAB' => array('id' => 27, 'nombre' => 'Tabasco'),
    'TAMS' => array('id' => 28, 'nombre' => 'Tamaulipas'),
    'TLAX' => array('id' => 29, 'nombre' => 'Tlaxcala'),
    'VER' => array('id' => 30, 'nombre' => 'Veracruz'),
    'YUC' => array('id' => 31, 'nombre' => 'Yucatán'),
    'ZAC' => array('id' => 32, 'nombre' => 'Zacatecas')
);

// Mapeo de nombres alternativos para consultas SQL (EXPANDIDO con versiones corruptas)
$mapeo_nombres_inegi = array(
    // Nombres correctos
    'CIUDAD DE MÉXICO' => 'CDMX',
    'Ciudad de México' => 'CDMX',
    'DISTRITO FEDERAL' => 'CDMX',
    'MÉXICO' => 'MEX',
    'ESTADO DE MÉXICO' => 'MEX',
    'MICHOACÁN DE OCAMPO' => 'MICH',
    'MICHOACÁN' => 'MICH',
    'VERACRUZ DE IGNACIO DE LA LLAVE' => 'VER',
    'VERACRUZ' => 'VER',
    'COAHUILA DE ZARAGOZA' => 'COAH',
    'COAHUILA' => 'COAH',
    'NUEVO LEÓN' => 'NL',
    'SAN LUIS POTOSÍ' => 'SLP',
    'QUINTANA ROO' => 'QR',
    'QUERÉTARO' => 'QRO',
    'YUCATÁN' => 'YUC',
    
    // NOMBRES CORRUPTOS POR UTF-8 (estos son los que fallan)
    'CIUDAD DE MÉXICO' => 'CDMX',
    'Ciudad de MÃ©xico' => 'CDMX',
    'CIUDAD DE MÃ©XICO' => 'CDMX',
    'MÃ©XICO' => 'MEX',
    'MÃ©xico' => 'MEX',
    'ESTADO DE MÃ©XICO' => 'MEX',
    'MICHOACÃ¡N DE OCAMPO' => 'MICH',
    'MichoacÃ¡n de Ocampo' => 'MICH',
    'MICHOACÃ¡N' => 'MICH',
    'NUEVO LEÃ³N' => 'NL',
    'Nuevo LeÃ³n' => 'NL',
    'NUEVO LEÃN' => 'NL',
    'SAN LUIS POTOSÃ­' => 'SLP',
    'San Luis PotosÃ­' => 'SLP',
    'SAN LUIS POTOSÍ' => 'SLP',
    'QUERÃ©TARO' => 'QRO',
    'QuerÃ©taro' => 'QRO',
    'QUERÉTARO' => 'QRO',
    'YUCATÃ¡N' => 'YUC',
    'YucatÃ¡n' => 'YUC',
    'YUCATÁN' => 'YUC',
    
    // Otras variaciones posibles
    'DF' => 'CDMX',
    'D.F.' => 'CDMX'
);

// Función mejorada para normalizar nombres de estados
function normalizar_nombre_estado($nombre) {
    global $mapeo_nombres_inegi;
    
    // Limpiar el nombre
    $nombre = trim($nombre);
    
    // Intentar con el nombre original
    if (isset($mapeo_nombres_inegi[$nombre])) {
        return $mapeo_nombres_inegi[$nombre];
    }
    
    // Intentar con mayúsculas
    $nombre_upper = strtoupper($nombre);
    if (isset($mapeo_nombres_inegi[$nombre_upper])) {
        return $mapeo_nombres_inegi[$nombre_upper];
    }
    
    // Intentar corregir caracteres UTF-8 mal codificados
    $nombre_corregido = html_entity_decode($nombre, ENT_QUOTES, 'UTF-8');
    if (isset($mapeo_nombres_inegi[$nombre_corregido])) {
        return $mapeo_nombres_inegi[$nombre_corregido];
    }
    
    // Si no se encuentra, intentar una búsqueda parcial
    foreach ($mapeo_nombres_inegi as $patron => $clave) {
        if (stripos($patron, $nombre) !== false || stripos($nombre, $patron) !== false) {
            return $clave;
        }
    }
    
    // Si todo falla, devolver null
    return null;
}

// Función adicional para debug - ver qué nombres no se están mapeando
function debug_nombre_estado($nombre) {
    echo "DEBUG: Procesando nombre: '$nombre'\n";
    echo "DEBUG: Longitud: " . strlen($nombre) . "\n";
    echo "DEBUG: Bytes: " . bin2hex($nombre) . "\n";
    echo "DEBUG: Resultado normalización: " . normalizar_nombre_estado($nombre) . "\n";
    echo "---\n";
}

// Función para limpiar y convertir encoding si es necesario
function limpiar_encoding($texto) {
    // Detectar encoding
    $encoding = mb_detect_encoding($texto, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
    
    if ($encoding && $encoding !== 'UTF-8') {
        $texto = mb_convert_encoding($texto, 'UTF-8', $encoding);
    }
    
    return $texto;
}

?>