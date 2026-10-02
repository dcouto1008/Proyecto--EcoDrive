<?php
declare(strict_types=1);

require_once 'procesador.php';

$lineasReserva = [
    [
        'vehiculo' => 'Renault',
        'precio_dia' => 40.0
    ],
    [
        'vehiculo' => 'Citroën ë-C4',
        'precio_dia' => 50.0
    ],
];

try {

    $total = procesarReserva($lineasReserva, $dias);
    $categoria = categoriaReserva($total);
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo 'Error: ' . htmlspecialchars(
        $e->getMessage(),
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
    exit;
}


// Mostrar todos los errores durante el desarrollo
error_reporting(E_ALL);
ini_set('display_errors', '1');

header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');

// BLOQUE 3: Catálogo con tildes y caracteres especiales
$flota = [
['nombre' => 'Renault Zoé', 'categoria' => 'económico', 'autonomia' => 395, 'descuento' => 10, 'extras' => 'Cargador'],
['nombre' => 'Citroën ë-C4', 'categoria' => 'COMPACTO', 'autonomia' => 350, 'descuento' => null, 'extras' => null],
['nombre' => 'Tesla Model Y', 'categoria' => 'ÚLTIMA GAMA', 'autonomia' => 533], // sin claves descuento/extras
['nombre' => 'Dacia Spring', 'categoria' => 'urbano', 'autonomia' => 230, 'descuento' => 5, 'extras' => null],
];

// Normalización multibyte
foreach ($flota as &$coche) {//aqui lo que hacemos es recorrer el array y modificandolo
//aqui lo pasa a titulo 
$coche['categoria'] = mb_convert_case($coche['categoria'], MB_CASE_TITLE, 'UTF-8');
//aqui lo pasa a mayuscula respetndo las tildes
$coche['nombre_mayus'] = mb_strtoupper($coche['nombre'], 'UTF-8');
//aqui cuenta las caracteristicas que tiene
$coche['longitud'] = mb_strlen($coche['nombre'], 'UTF-8');
}
unset($coche);//aqui borramos coche del array

// aqui ordenamos de mayor a menor con comparación de tres (<=>)
usort($flota, function (array $a, array $b): int {
// lo que devuelve es -1, 0 o 1 y al poner $b primero, el orden es descendente.
return $b['autonomia'] <=> $a['autonomia'];
});

// BLOQUE 4: Generar el reporte en memoria (bufer)

ob_start(); // A partir de aquí, el echo NO sale al navegador

echo '<table border="1" cellpadding="6">';//crea la tabla
echo '<tr><th>Modelo</th><th>Categoría</th><th>Autonomía (km)</th><th>Longitud</th><th>isset(descuento)</th><th>array_key_exists(descuento)</th></tr>';

foreach ($flota as $coche) {    //aqui recorre cada coche de flota 
// comprueba si hay o no descuento
$conIsset = isset($coche['descuento']) ? 'sí' : 'no';
// aqui si exite o no la clave 
$conKey = array_key_exists('descuento', $coche) ? 'sí' : 'no';

// htmlspecialchars evita XSS en el HTML
echo '<tr>';
echo '<td>' . htmlspecialchars($coche['nombre_mayus'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</td>';
echo '<td>' . htmlspecialchars($coche['categoria'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</td>';
echo '<td>' . (int) $coche['autonomia'] . '</td>';
echo '<td>' . $coche['longitud'] . '</td>';
echo '<td>' . $conIsset . '</td>';
echo '<td>' . $conKey . '</td>';
echo '</tr>';
}
echo '</table>';

$reporte = ob_get_clean(); // Guardamos el HTML en reporte y cerramos el bufer

// aqui preparamos la informacion para usarla en JS
$datosJs = json_encode(
array_column($flota, 'autonomia', 'nombre'),
JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte de flota EcoDrive</title>
</head>
<body>
<h1>Reporte de flota EcoDrive</h1>
<h2>Inspección técnica</h2>

<pre>
<?php
var_dump($diasRecibidos);//aqui vemos la informacion que tienen
var_dump($dias);
?>
</pre>


<?= $reporte ?>

<script>
// objeto JS con {nombre: autonomía}
const autonomias = <?= $datosJs ?>;
console.log('Autonomías de la flota:', autonomias);
</script>
</body>
</html>

<?php

$html = ob_get_clean();

echo $html;
