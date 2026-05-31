<?php
$apiKey = getenv('WEATHER_API_KEY');

$mensajeError = '';
$result = null;

// Validar si se envió el formulario
if (isset($_GET['ciudad'])) {

    $ciudad = trim($_GET['ciudad']);

    // Validación de campo vacío
    if (empty($ciudad)) {
        $mensajeError = "Debe ingresar una ciudad.";
    } else {

        $ciudad = htmlspecialchars($ciudad);

        function apiRequest(string $url): array
        {
            $ch = curl_init($url);

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_HTTPHEADER => [
                    'Accept: application/json'
                ]
            ]);

            $response = curl_exec($ch);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);

            curl_close($ch);

            if ($error) {
                return [
                    'error' => 'Error de conexión: ' . $error
                ];
            }

            return [
                'status' => $status,
                'data' => json_decode($response, true)
            ];
        }

        $url = "https://api.openweathermap.org/data/2.5/weather?q="
            . urlencode($ciudad)
            . "&appid=" . $apiKey
            . "&units=metric&lang=es";

        $result = apiRequest($url);

        // Manejo de errores HTTP
        if (!isset($result['error'])) {

            switch ($result['status']) {

                case 200:
                    break;

                case 401:
                    $mensajeError = "Error 401: API Key inválida o no autorizada.";
                    break;

                case 404:
                    $mensajeError = "Error 404: Ciudad no encontrada.";
                    break;

                case 500:
                    $mensajeError = "Error 500: Problema interno del servidor.";
                    break;

                default:
                    $mensajeError = "Error HTTP: " . $result['status'];
                    break;
            }
        } else {
            $mensajeError = $result['error'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Consulta de Clima</title>
</head>
<body>

<h2>Consultar clima</h2>

<form method="GET">
    <input type="text" name="ciudad" placeholder="Ingrese ciudad">
    <button type="submit">Buscar</button>
</form>

<?php if (!empty($mensajeError)): ?>
    <p style="color:red;">
        <?= $mensajeError ?>
    </p>

<?php elseif (
    isset($result['status']) &&
    $result['status'] == 200 &&
    isset($result['data']['main'])
): ?>

    <h3><?= $result['data']['name']; ?></h3>

    <p>
        Temperatura:
        <?= $result['data']['main']['temp']; ?> °C
    </p>

    <p>
        Humedad:
        <?= $result['data']['main']['humidity']; ?>%
    </p>

    <p>
        Descripción:
        <?= ucfirst($result['data']['weather'][0]['description']); ?>
    </p>

<?php endif; ?>

</body>
</html>