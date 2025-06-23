<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Configuración para Docker
    $servername = "db";           // Antes era "localhost"
    $username = "usuario";        // Antes era "root"
    $password = "clave";           // Antes era "TU_CONTRASEÑA"
    $database = "dss_db";  // Antes era "sismos_mexico"

    // Función para manejar errores de conexión
    function handleDatabaseError($connection, $errorMessage) {
        if ($connection->connect_error) {
            die($errorMessage . ": " . $connection->connect_error);
        }
    }

// Conexión a la base de datos de sismos
$conn_sismos = new mysqli($servername, $username, $password, $database);
// Conexión a la base de datos de población
$conn_poblacion = new mysqli($servername, $username, $password, $database);

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

// Consulta para obtener sismos por estado
$sql_sismos = "SELECT 
                SUBSTRING_INDEX(referencia_localizacion, ', ', -1) AS estado,
                COUNT(*) AS numero_sismos,
                AVG(magnitud) AS magnitud_promedio,
                MAX(magnitud) AS magnitud_maxima
              FROM 
                registros_sismos
              WHERE
                estatus = 'revisado'
              GROUP BY 
                estado
              ORDER BY 
                numero_sismos ASC";

$result_sismos = $conn_sismos->query($sql_sismos);

// Consulta para obtener población por entidad
$sql_poblacion = "SELECT 
                    entidad,
                    entidad_nombre,
                    SUM(poblacion_total) AS poblacion_total,
                    SUM(poblacion_masculina) AS poblacion_masculina,
                    SUM(poblacion_femenina) AS poblacion_femenina
                  FROM 
                    poblacion_inegi_reducida
                  WHERE
                    entidad > 0
                  GROUP BY 
                    entidad, entidad_nombre
                  ORDER BY 
                    poblacion_total DESC";

$result_poblacion = $conn_poblacion->query($sql_poblacion);

// Procesar datos de sismos
$estados_sismos = array();
if ($result_sismos->num_rows > 0) {
    while($row = $result_sismos->fetch_assoc()) {
        // Filtrar estados válidos
        if ($row["estado"] != "N" && array_key_exists($row["estado"], $estados_nombres)) {
            $estados_sismos[$row["estado"]] = array(
                "numero_sismos" => $row["numero_sismos"],
                "magnitud_promedio" => $row["magnitud_promedio"],
                "magnitud_maxima" => $row["magnitud_maxima"]
            );
        }
    }
}

// Procesar datos de población
$estados_poblacion = array();
if ($result_poblacion->num_rows > 0) {
    while($row = $result_poblacion->fetch_assoc()) {
        $entidad_nombre = $row["entidad_nombre"];
        $clave_estado = null;
        
        // Encontrar la clave del estado desde el nombre
        foreach ($estados_nombres as $clave => $datos) {
            if (strtoupper($datos['nombre']) == strtoupper($entidad_nombre)) {
                $clave_estado = $clave;
                break;
            }
        }
        
        if ($clave_estado) {
            $estados_poblacion[$clave_estado] = array(
                "poblacion_total" => $row["poblacion_total"],
                "poblacion_masculina" => $row["poblacion_masculina"],
                "poblacion_femenina" => $row["poblacion_femenina"]
            );
        }
    }
}

// Calcular pesos solo para estados con datos de sismos
$max_sismos = 0;
if (!empty($estados_sismos)) {
    $max_sismos = max(array_column($estados_sismos, "numero_sismos"));
}

// Calcular pesos solo para estados con datos de población
$max_poblacion = 0;
if (!empty($estados_poblacion)) {
    $max_poblacion = max(array_column($estados_poblacion, "poblacion_total"));
}

// Crear array combinado con TODOS los estados
$estados_combinados = array();
foreach ($estados_nombres as $estado => $datos) {
    // Datos de sismos (si existen)
    $numero_sismos = isset($estados_sismos[$estado]) ? $estados_sismos[$estado]["numero_sismos"] : 0;
    $magnitud_promedio = isset($estados_sismos[$estado]) ? $estados_sismos[$estado]["magnitud_promedio"] : 0;
    $magnitud_maxima = isset($estados_sismos[$estado]) ? $estados_sismos[$estado]["magnitud_maxima"] : 0;
    
    // Calcular peso sísmico (inversamente proporcional al número de sismos)
    $peso_sismos = 0;
    if ($max_sismos > 0 && $numero_sismos > 0) {
        $peso_sismos = 100 - ($numero_sismos / $max_sismos * 100);
    } elseif ($numero_sismos == 0) {
        // Si no hay sismos registrados, le damos el peso máximo (es lo mejor para construcción)
        $peso_sismos = 100;
    }
    
    // Datos de población (si existen)
    $poblacion_total = isset($estados_poblacion[$estado]) ? $estados_poblacion[$estado]["poblacion_total"] : 0;
    
    // Calcular peso poblacional (directamente proporcional a la población)
    $peso_poblacion = 0;
    if ($max_poblacion > 0 && $poblacion_total > 0) {
        $peso_poblacion = ($poblacion_total / $max_poblacion) * 100;
    }
    
    // Fórmula ponderada: 40% importancia a pocos sismos, 60% a alta población
    $puntaje_final = ($peso_sismos * 0.4) + ($peso_poblacion * 0.6);
    
    $estados_combinados[$estado] = array(
        "id" => $datos['id'],
        "clave" => $estado,
        "nombre" => $datos['nombre'],
        "numero_sismos" => $numero_sismos,
        "magnitud_promedio" => $magnitud_promedio,
        "magnitud_maxima" => $magnitud_maxima,
        "peso_sismos" => $peso_sismos,
        "poblacion_total" => $poblacion_total,
        "peso_poblacion" => $peso_poblacion,
        "puntaje_final" => $puntaje_final
    );
}

// Ordenar por puntaje final (de mayor a menor)
uasort($estados_combinados, function($a, $b) {
    return $b["puntaje_final"] <=> $a["puntaje_final"];
});

// Mostrar resultados
echo "<h2>Análisis Combinado - Estados Óptimos para Construcción de Torres</h2>";
echo "<p><strong>Total de estados analizados:</strong> " . count($estados_combinados) . "</p>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background-color: #f2f2f2;'>
        <th>Posición</th>
        <th>ID</th>
        <th>Clave</th>
        <th>Nombre</th>
        <th>Número de Sismos</th>
        <th>Magnitud Promedio</th>
        <th>Magnitud Máxima</th>
        <th>Peso Sismos (40%)</th>
        <th>Población Total</th>
        <th>Peso Población (60%)</th>
        <th>Puntaje Final</th>
      </tr>";

$posicion = 1;
foreach ($estados_combinados as $estado => $datos) {
    // Destacar los 3 mejores estados
    $estilo = "";
    if ($posicion <= 3) {
        $estilo = "background-color: #d4edda; font-weight: bold;";
    } elseif ($datos["numero_sismos"] == 0 && $datos["poblacion_total"] == 0) {
        // Destacar estados sin datos
        $estilo = "background-color: #f8d7da; color: #721c24;";
    }
    
    echo "<tr style='".$estilo."'>";
    echo "<td style='text-align: center;'>" . $posicion . "</td>";
    echo "<td style='text-align: center;'>" . $datos["id"] . "</td>";
    echo "<td style='text-align: center;'>" . $datos["clave"] . "</td>";
    echo "<td>" . $datos["nombre"] . "</td>";
    echo "<td style='text-align: center;'>" . ($datos["numero_sismos"] > 0 ? $datos["numero_sismos"] : "Sin datos") . "</td>";
    echo "<td style='text-align: center;'>" . ($datos["magnitud_promedio"] > 0 ? number_format($datos["magnitud_promedio"], 2) : "Sin datos") . "</td>";
    echo "<td style='text-align: center;'>" . ($datos["magnitud_maxima"] > 0 ? $datos["magnitud_maxima"] : "Sin datos") . "</td>";
    echo "<td style='text-align: center;'>" . number_format($datos["peso_sismos"], 2) . "</td>";
    echo "<td style='text-align: right;'>" . ($datos["poblacion_total"] > 0 ? number_format($datos["poblacion_total"]) : "Sin datos") . "</td>";
    echo "<td style='text-align: center;'>" . number_format($datos["peso_poblacion"], 2) . "</td>";
    echo "<td style='text-align: center;'><strong>" . number_format($datos["puntaje_final"], 2) . "</strong></td>";
    echo "</tr>";
    $posicion++;
}
echo "</table>";

// Destacar los 3 mejores estados para la construcción
echo "<h3>Los 3 estados óptimos para la construcción de torres:</h3>";
echo "<ol>";
$contador = 0;
foreach ($estados_combinados as $estado => $datos) {
    echo "<li><strong>" . $datos["nombre"] . " (" . $datos["clave"] . ")</strong> - Puntaje: " . 
         number_format($datos["puntaje_final"], 2);
    
    // Agregar información adicional
    if ($datos["numero_sismos"] == 0) {
        echo " <em>(Sin sismos registrados)</em>";
    }
    if ($datos["poblacion_total"] == 0) {
        echo " <em>(Sin datos de población)</em>";
    }
    echo "</li>";
    
    $contador++;
    if ($contador >= 3) break;
}
echo "</ol>";

// Estadísticas adicionales
echo "<h3>Estadísticas del Análisis:</h3>";
$estados_con_sismos = count(array_filter($estados_combinados, function($estado) { return $estado["numero_sismos"] > 0; }));
$estados_con_poblacion = count(array_filter($estados_combinados, function($estado) { return $estado["poblacion_total"] > 0; }));
$estados_completos = count(array_filter($estados_combinados, function($estado) { return $estado["numero_sismos"] > 0 && $estado["poblacion_total"] > 0; }));

echo "<ul>";
echo "<li>Estados con datos de sismos: " . $estados_con_sismos . " de " . count($estados_combinados) . "</li>";
echo "<li>Estados con datos de población: " . $estados_con_poblacion . " de " . count($estados_combinados) . "</li>";
echo "<li>Estados con datos completos: " . $estados_completos . " de " . count($estados_combinados) . "</li>";
echo "</ul>";

// Cerrar conexiones
$conn_sismos->close();
$conn_poblacion->close();
?>