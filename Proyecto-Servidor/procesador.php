<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

declare(strict_types=1); // esto es el tipado estricto 

// Mostrar todos los errores durante el desarrollo
error_reporting(E_ALL);
ini_set('display_errors', '1');

// BLOQUE 1: Validación de la reserva (GET)
// Ejemplo de uso: procesador.php?dias=3

$diasRecibidos = $_GET['dias'] ?? null;

// FILTER_VALIDATE_INT comprueba que sea un numero entero;
$dias = filter_var($diasRecibidos, FILTER_VALIDATE_INT, [
'options' => ['min_range' => 1]// aqui hacemos que el numero sea minimo 1
]);


if ($dias === false) {
http_response_code(400); // Petición incorrecta
echo 'Error 400: el parámetro "dias" debe ser un número entero positivo.';
exit; // Finaliza el proceso
}



// BLOQUE 2: Función de facturación, excepciones y categorías

/**
* Calcula el total de una reserva sumando las líneas de vehículos.
*
* @param array<int, array{vehiculo: string, precio_dia: float}> $lineas
* Lista de vehículos reservados con su precio por día.
* @param int $dias Número de días de alquiler (mayor que 0).
*
* @return float Importe total de la reserva.
*
* @throws InvalidArgumentException Si la lista de líneas está vacía.
*/

function procesarReserva(array $lineas, int $dias): float
{
//no se puede facturar una reserva sin elementos
if (empty($lineas)) {
throw new InvalidArgumentException('La reserva no contiene vehículos.');
}

$total = 0.0;

foreach ($lineas as $linea) {//aqui calculamos el precio de dias * dias de cada elemento del array
$total += $linea['precio_dia'] * $dias;
}

return $total;
}

/**
* Determina la categoría de descuento o suplemento según el total.
*
* @param float $total Importe total de la reserva.
*
* @return string Nombre de la categoría aplicada.
*/

function categoriaReserva(float $total): string
{
// match evalua cada condicion de arriba abajo 
return match (true) {
$total >= 1000 => 'Descuento flota premium (15%)',
$total >= 500 => 'Descuento cliente frecuente (8%)',
$total >= 100 => 'Tarifa estándar',
default => 'Suplemento reserva corta (+5%)',
};
}