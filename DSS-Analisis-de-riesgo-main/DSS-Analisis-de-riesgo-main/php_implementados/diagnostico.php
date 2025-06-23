<?php
// Script de diagnóstico para identificar por qué no aparecen ciertos estados
// Incluir archivos de datos
require_once 'config.php';
require_once 'sismos_data.php';
require_once 'poblacion_data.php';
require_once 'economia_data.php';
require_once 'riesgo_data.php';

// Obtener datos de cada fuente
$estados_sismos = obtener_datos_sismos();
$estados_poblacion = obtener_datos_poblacion();
$estados_economia = obtener_datos_economia();
$estados_delincuencia = obtener_datos_delincuencia();

echo "<h2>🔍 DIAGNÓSTICO DE ESTADOS FALTANTES</h2>";

// Estados que buscamos específicamente
$estados_buscados = array("CDMX", "NL", "MICH", "MEX"); // Agregué MEX por si CDMX está como MEX

echo "<h3>📋 Estados que estamos buscando:</h3>";
foreach ($estados_buscados as $estado) {
    echo "- $estado<br>";
}

echo "<hr>";

// 1. Verificar si existe $estados_nombres
echo "<h3>1️⃣ Verificando array \$estados_nombres:</h3>";
if (isset($estados_nombres)) {
    echo "✅ El array \$estados_nombres existe<br>";
    echo "📊 Total de estados en \$estados_nombres: " . count($estados_nombres) . "<br><br>";
    
    echo "<strong>🗂️ Claves disponibles en \$estados_nombres:</strong><br>";
    foreach ($estados_nombres as $clave => $datos) {
        $highlight = in_array($clave, $estados_buscados) ? "style='background-color: yellow;'" : "";
        echo "<span $highlight>$clave => " . $datos['nombre'] . "</span><br>";
    }
    echo "<br>";
    
    // Verificar estados específicos
    foreach ($estados_buscados as $estado) {
        if (isset($estados_nombres[$estado])) {
            echo "✅ $estado encontrado en \$estados_nombres: " . $estados_nombres[$estado]['nombre'] . "<br>";
        } else {
            echo "❌ $estado NO encontrado en \$estados_nombres<br>";
        }
    }
} else {
    echo "❌ El array \$estados_nombres NO existe o no está definido<br>";
}

echo "<hr>";

// 2. Verificar datos de sismos
echo "<h3>2️⃣ Verificando datos de sismos:</h3>";
echo "📊 Total de estados en sismos: " . count($estados_sismos) . "<br><br>";

echo "<strong>🗂️ Claves disponibles en datos de sismos:</strong><br>";
foreach ($estados_sismos as $clave => $datos) {
    $highlight = in_array($clave, $estados_buscados) ? "style='background-color: yellow;'" : "";
    echo "<span $highlight>$clave</span><br>";
}
echo "<br>";

foreach ($estados_buscados as $estado) {
    if (isset($estados_sismos[$estado])) {
        echo "✅ $estado encontrado en datos de sismos<br>";
    } else {
        echo "❌ $estado NO encontrado en datos de sismos<br>";
    }
}

echo "<hr>";

// 3. Verificar datos de población
echo "<h3>3️⃣ Verificando datos de población:</h3>";
echo "📊 Total de estados en población: " . count($estados_poblacion) . "<br><br>";

echo "<strong>🗂️ Claves disponibles en datos de población:</strong><br>";
foreach ($estados_poblacion as $clave => $datos) {
    $highlight = in_array($clave, $estados_buscados) ? "style='background-color: yellow;'" : "";
    echo "<span $highlight>$clave</span><br>";
}
echo "<br>";

foreach ($estados_buscados as $estado) {
    if (isset($estados_poblacion[$estado])) {
        echo "✅ $estado encontrado en datos de población<br>";
    } else {
        echo "❌ $estado NO encontrado en datos de población<br>";
    }
}

echo "<hr>";

// 4. Verificar datos de economía
echo "<h3>4️⃣ Verificando datos de economía:</h3>";
echo "📊 Total de estados en economía: " . count($estados_economia) . "<br><br>";

echo "<strong>🗂️ Claves disponibles en datos de economía:</strong><br>";
foreach ($estados_economia as $clave => $datos) {
    $highlight = in_array($clave, $estados_buscados) ? "style='background-color: yellow;'" : "";
    echo "<span $highlight>$clave</span><br>";
}
echo "<br>";

foreach ($estados_buscados as $estado) {
    if (isset($estados_economia[$estado])) {
        echo "✅ $estado encontrado en datos de economía<br>";
    } else {
        echo "❌ $estado NO encontrado en datos de economía<br>";
    }
}

echo "<hr>";

// 5. Verificar datos de delincuencia
echo "<h3>5️⃣ Verificando datos de delincuencia:</h3>";
echo "📊 Total de estados en delincuencia: " . count($estados_delincuencia) . "<br><br>";

echo "<strong>🗂️ Claves disponibles en datos de delincuencia:</strong><br>";
foreach ($estados_delincuencia as $clave => $datos) {
    $highlight = in_array($clave, $estados_buscados) ? "style='background-color: yellow;'" : "";
    echo "<span $highlight>$clave</span><br>";
}
echo "<br>";

foreach ($estados_buscados as $estado) {
    if (isset($estados_delincuencia[$estado])) {
        echo "✅ $estado encontrado en datos de delincuencia<br>";
    } else {
        echo "❌ $estado NO encontrado en datos de delincuencia<br>";
    }
}

echo "<hr>";

// 6. Verificar la condición del bucle principal
echo "<h3>6️⃣ Simulando el bucle principal:</h3>";
echo "El bucle principal requiere que el estado exista en:<br>";
echo "- \$estados_nombres ✓<br>";
echo "- \$estados_sismos ✓<br>";
echo "- \$estados_poblacion ✓<br>";
echo "<br>";

$estados_validos = 0;
if (isset($estados_nombres)) {
    foreach ($estados_nombres as $estado => $datos) {
        $en_sismos = isset($estados_sismos[$estado]);
        $en_poblacion = isset($estados_poblacion[$estado]);
        
        if ($en_sismos && $en_poblacion) {
            $estados_validos++;
            if (in_array($estado, $estados_buscados)) {
                echo "✅ $estado PASARÍA el filtro del bucle principal<br>";
            }
        } else {
            if (in_array($estado, $estados_buscados)) {
                echo "❌ $estado NO pasaría el filtro del bucle principal<br>";
                echo "&nbsp;&nbsp;&nbsp;- En sismos: " . ($en_sismos ? "✅" : "❌") . "<br>";
                echo "&nbsp;&nbsp;&nbsp;- En población: " . ($en_poblacion ? "✅" : "❌") . "<br>";
            }
        }
    }
}

echo "<br>📊 Total de estados que pasarían el filtro: $estados_validos<br>";

echo "<hr>";

// 7. Posibles variaciones de nombres
echo "<h3>7️⃣ Posibles variaciones de nombres para CDMX:</h3>";
$posibles_cdmx = array("CDMX", "MEX", "DF", "CIUDAD_DE_MEXICO", "MEXICO_DF", "DISTRITO_FEDERAL");

foreach ($posibles_cdmx as $variacion) {
    $encontrado = false;
    if (isset($estados_nombres) && isset($estados_nombres[$variacion])) {
        echo "✅ Encontrado como '$variacion' en estados_nombres<br>";
        $encontrado = true;
    }
    if (isset($estados_sismos[$variacion])) {
        echo "✅ Encontrado como '$variacion' en sismos<br>";
        $encontrado = true;
    }
    if (isset($estados_poblacion[$variacion])) {
        echo "✅ Encontrado como '$variacion' en población<br>";
        $encontrado = true;
    }
    if (!$encontrado) {
        echo "❌ '$variacion' no encontrado<br>";
    }
}

echo "<hr>";

echo "<h3>8️⃣ Posibles variaciones de nombres para Nuevo León:</h3>";
$posibles_nl = array("NL", "NUEVO_LEON", "NUEVOLEON", "N_L", "25");

foreach ($posibles_nl as $variacion) {
    $encontrado = false;
    if (isset($estados_nombres) && isset($estados_nombres[$variacion])) {
        echo "✅ Encontrado como '$variacion' en estados_nombres<br>";
        $encontrado = true;
    }
    if (isset($estados_sismos[$variacion])) {
        echo "✅ Encontrado como '$variacion' en sismos<br>";
        $encontrado = true;
    }
    if (isset($estados_poblacion[$variacion])) {
        echo "✅ Encontrado como '$variacion' en población<br>";
        $encontrado = true;
    }
    if (!$encontrado) {
        echo "❌ '$variacion' no encontrado<br>";
    }
}

echo "<hr>";

echo "<h3>9️⃣ Posibles variaciones de nombres para Michoacán:</h3>";
$posibles_mich = array("MICH", "MICHOACAN", "MICHOACÁN", "MICH_OCAMPO", "16");

foreach ($posibles_mich as $variacion) {
    $encontrado = false;
    if (isset($estados_nombres) && isset($estados_nombres[$variacion])) {
        echo "✅ Encontrado como '$variacion' en estados_nombres<br>";
        $encontrado = true;
    }
    if (isset($estados_sismos[$variacion])) {
        echo "✅ Encontrado como '$variacion' en sismos<br>";
        $encontrado = true;
    }
    if (isset($estados_poblacion[$variacion])) {
        echo "✅ Encontrado como '$variacion' en población<br>";
        $encontrado = true;
    }
    if (!$encontrado) {
        echo "❌ '$variacion' no encontrado<br>";
    }
}

echo "<hr>";
echo "<h3>🎯 RESUMEN Y RECOMENDACIONES:</h3>";
echo "Guarda este archivo como 'diagnostico.php' y ejecútalo para ver exactamente qué está pasando con tus datos.<br>";
echo "Una vez que identifiques las inconsistencias, podrás corregir las claves de los estados en tus archivos de datos.";
?>