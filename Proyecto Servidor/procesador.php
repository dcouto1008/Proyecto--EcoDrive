<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

/**
 * Procesa las lineas de una reserva y calcula el importe total.
 *
 * @param array<int, array{
 *     modelo: string,
 *     dias: int,
 *     precioDia: float
 * }> $reservas Lineas de vehículos incluidas en la reserva.
 *
 * @return float Importe total de la reserva.
 *
 * @throws InvalidArgumentException Si el listado de reservas esta vacío 
 */
function facturarReserva(array $reservas): float
{
    if ($reservas === []) {//comparamos el tipo de dato y el valor
        throw new InvalidArgumentException(//lanzamos una exception 
            'No se puede procesar una reserva sin vehículos.'
        );
    }

    $total = 0.0;

    foreach ($reservas as $reserva) {
        if (
            !isset($reserva['modelo']) ||
            !isset($reserva['dias']) ||
            !isset($reserva['precioDia']) ||
            $reserva['dias'] <= 0 ||
            $reserva['precioDia'] < 0
        ) {
            throw new InvalidArgumentException(
                'Una de las líneas de la reserva contiene datos no válidos.'
            );
        }

        $total += $reserva['dias'] * $reserva['precioDia'];
    }

    return $total;
}

/**
 * Envía una respuesta HTTP 400 y finaliza la ejecucio.
 *
 * @param string $mensaje Mensaje que vera al cliente.
 *
 * @return never
 */
function responderError400(string $mensaje): never
{
    http_response_code(400);

    echo '<!DOCTYPE html>';
    echo '<html lang="es">';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<title>Error 400</title>';
    echo '</head>';
    echo '<body>';
    echo '<h1>Error 400 - Solicitud incorrecta</h1>';
    echo '<p>' . htmlspecialchars($mensaje, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
    echo '</body>';
    echo '</html>';

    exit;
}

/*
 * BLOQUE 1 - VALIDACION DE ENTRADA
 *
 * Se admite:
 * ?dias=5
 *
 * ?unidades=5
 *
 * La prioridad sera dias y, si no existe, unidades.
 */

$entrada = $_GET['dias'] ?? $_GET['unidades'] ?? null; //lo hacemos con get ya que no recibimos nada

if ($entrada === null) {//usamos el comparador triple para saber si es null o true
    responderError400(
        'Debe indicar una cantidad mediante el parámetro "dias" o "unidades".'
    );
}

/*
 * FILTER_VALIDATE_INT permite comprobar que realmente
 * estamos recibiendo un número entero.
 */
$cantidad = filter_var(
    $entrada,
    FILTER_VALIDATE_INT,
    [
        'options' => [
            'min_range' => 1
        ]
    ]
);

if ($cantidad === false) {
    responderError400(
        'La cantidad debe ser un número entero positivo.'
    );
}

/*
 * INSPECCION TECNICA
 * 
 */

$datosCapturados = [
    'entrada_original' => $entrada,
    'tipo_entrada' => gettype($entrada),
    'cantidad_validada' => $cantidad,
    'tipo_cantidad' => gettype($cantidad),
];

echo '<!DOCTYPE html>';
echo '<html lang="es">';
echo '<head>';
echo '<meta charset="UTF-8">';
echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
echo '<title>EcoDrive - Procesador de reservas</title>';
echo '<style>';
echo 'body { font-family: Arial, sans-serif; margin: 40px; background: #f3f7f4; color: #1d3326; }';
echo 'pre { background: #17221b; color: #d9ffd9; padding: 20px; border-radius: 8px; }';
echo '.resultado { background: white; padding: 20px; border-radius: 8px; margin-top: 20px; }';
echo '</style>';
echo '</head>';
echo '<body>';

echo '<h1>EcoDrive - Procesador de reservas</h1>';

echo '<h2>Inspección técnica de datos</h2>';
echo '<pre>';
print_r($datosCapturados);
echo '</pre>';