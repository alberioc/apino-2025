<?php
// gerar_cache.php

// Configurações do banco
$database_config = [
    'servername' => '192.185.209.152',
    'username' => 'hg3aco88_dev',
    'password' => '!q1w2e3r4%',
    'database' => 'hg3aco88_cliente-apino'
];

// Conecta ao MySQL
$conn = new mysqli(
    $database_config['servername'], 
    $database_config['username'], 
    $database_config['password'], 
    $database_config['database']
);
if ($conn->connect_error) {
    die("Erro na conexão MySQL: " . $conn->connect_error);
}

// Carrega aeroportos.dat
$airportFile = 'airports.dat';
$airports = file($airportFile);
$coords = [];

foreach ($airports as $line) {
    $parts = str_getcsv($line);
    if (count($parts) < 8) continue;
    $iata = trim($parts[4], '"');
    $lat  = (float) $parts[6];
    $lon  = (float) $parts[7];
    if ($iata) {
        $coords[$iata] = ['lat' => $lat, 'lon' => $lon];
    }
}

// Função para calcular distância entre aeroportos
function distancia_km($from, $to, $coords) {
    if (!isset($coords[$from]) || !isset($coords[$to])) {
        return false;
    }
    $lat1 = deg2rad($coords[$from]['lat']);
    $lon1 = deg2rad($coords[$from]['lon']);
    $lat2 = deg2rad($coords[$to]['lat']);
    $lon2 = deg2rad($coords[$to]['lon']);
    $earthRadius = 6371;
    $dlat = $lat2 - $lat1;
    $dlon = $lon2 - $lon1;
    $a = sin($dlat/2)**2 + cos($lat1)*cos($lat2)*sin($dlon/2)**2;
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $earthRadius * $c;
}

$distanciasBase = [];

// Consulta dados no banco
$sql = "SELECT passageiros, trechos, pagante, data_venda FROM vendas WHERE produto = 'Passagem Aérea' AND tipo_pessoa = 'J' AND trechos IS NOT NULL AND trechos != ''";

$result = $conn->query($sql);

if (!$result) {
    die("Erro na consulta MySQL: " . $conn->error);
}

while ($row = $result->fetch_assoc()) {
    $passageiros = $row['passageiros'];
    $trechoStr = $row['trechos'];
    $pagante = $row['pagante'];
    $dataVenda = $row['data_venda'];

    if (!$trechoStr) continue;

    // Normaliza trecho: substitui // por /SURFACE/ e usa hífen como separador
    $trechoLimpo = trim(str_replace("//", "-SURFACE-", strtoupper($trechoStr)));
    $partes = explode('-', $trechoLimpo);

    $distanciaTotal = 0;
    $erro = false;

    for ($i = 0; $i < count($partes) - 1; $i++) {
        $from = $partes[$i];
        $to = $partes[$i + 1];

        if (in_array('SURFACE', [$from, $to])) continue;

        $dist = distancia_km($from, $to, $coords);
        if ($dist === false) {
            $erro = "Aeroporto não encontrado: $from ou $to";
            break;
        }
        $distanciaTotal += $dist;
    }

    if ($erro) {
        echo "<strong><span style='color: red;'>Erro no trecho $trechoStr: $erro</span></strong><br>";
        continue;
    }

    // Conta passageiros (ex: "1,2" → 2 passageiros)
    $qtdPassageiros = substr_count($passageiros, ',') + 1;
    $trechoBase = implode('-', $partes);

    if (!isset($distanciasBase[$trechoBase])) {
        $co2 = ($distanciaTotal / 11.17) * 0.83;

        $distanciasBase[$trechoBase] = [
            'trecho' => $trechoBase,
            'distancia_km' => round($distanciaTotal, 2),
            'co2_kg' => round($co2, 2),
            'pagante' => $pagante,
            'data_venda' => $dataVenda,
            'passageiros' => $passageiros,
        ];

        echo "<pre>Trecho cacheado: $trechoBase | Distância: " . round($distanciaTotal, 2) . " km</pre>";

    }
}

if (empty($distanciasBase)) {
    echo "Nenhum trecho foi processado e armazenado.<br>";
    exit;
}

file_put_contents('distancias_base.json', json_encode(array_values($distanciasBase), JSON_PRETTY_PRINT));

echo "<br>Cache salvo com " . count($distanciasBase) . " trechos.<br>";

$conn->close();
