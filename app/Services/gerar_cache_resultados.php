<?php
// Configurações de conexão
$database_config = [
    'host' => '192.185.209.152',
    'dbname' => 'hg3aco88_cliente-apino',
    'username' => 'hg3aco88_dev',
    'password' => '!q1w2e3r4%',
];

// Constantes
define('KM_MINUTO', 670 / 60);  // 11,17 km/min
define('CO2_MINUTO', 50 / 60);  // 0,83 kg/min

// Carrega aeroportos
function carregarAeroportos(string $filename): array {
    $aeroportos = [];
    $handle = fopen($filename, 'r');
    if (!$handle) die("Erro ao abrir arquivo de aeroportos.");

    while (($line = fgets($handle)) !== false) {
        $parts = str_getcsv(trim($line));
        if (count($parts) < 8) continue;

        $iata = strtoupper(trim($parts[4] ?? ''));
        $lat = floatval($parts[6] ?? 0);
        $lon = floatval($parts[7] ?? 0);

        if ($iata !== '') {
            $aeroportos[$iata] = ['lat' => $lat, 'lon' => $lon];
        }
    }
    fclose($handle);
    return $aeroportos;
}

// Distância entre 2 pontos
function distanciaEntrePontos(float $lat1, float $lon1, float $lat2, float $lon2): float {
    $rad = pi() / 180;
    $dLat = ($lat2 - $lat1) * $rad;
    $dLon = ($lon2 - $lon1) * $rad;
    $a = sin($dLat / 2) ** 2 +
         cos($lat1 * $rad) * cos($lat2 * $rad) * sin($dLon / 2) ** 2;
    return 6371 * 2 * atan2(sqrt($a), sqrt(1 - $a));
}

// Distância total de um trecho
function distanciaTrecho(string $trecho, array $aeroportos): ?float {
    $trecho = strtoupper(trim($trecho));
    $trecho = str_replace(['//', '-'], ['/SURFACE/', '/'], $trecho);
    $pontos = preg_split('/\/+|SURFACE/', $trecho);
    $pontos = array_filter(array_map('trim', $pontos));

    $distancia = 0.0;
    $ultimo = null;
    foreach ($pontos as $p) {
        if (!isset($aeroportos[$p])) return null;
        if ($ultimo !== null) {
            $distancia += distanciaEntrePontos(
                $aeroportos[$ultimo]['lat'], $aeroportos[$ultimo]['lon'],
                $aeroportos[$p]['lat'], $aeroportos[$p]['lon']
            );
        }
        $ultimo = $p;
    }
    return $distancia;
}

// Carrega aeroportos
$aeroportos = carregarAeroportos('airports.dat');
echo "Base de aeroportos carregada: " . count($aeroportos) . " aeroportos.\n";

// Conecta ao banco
try {
    $pdo = new PDO(
        "mysql:host={$database_config['host']};dbname={$database_config['dbname']};charset=utf8mb4",
        $database_config['username'],
        $database_config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Erro na conexão com o banco: " . $e->getMessage());
}

// Busca dados da tabela
$sql = "SELECT id, passageiros, trechos, pagante, data_venda FROM vendas WHERE tipo_pessoa = 'J' AND trechos IS NOT NULL AND trechos <> ''";
$stmt = $pdo->query($sql);
$linhas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$resultados = [];

foreach ($linhas as $linha) {
    $id = $linha['id'];
    $trechoStr = $linha['trechos'];
    $passageiros = $linha['passageiros'];

    $trechoCorrigido = strtoupper(trim(str_replace(['//'], ['/SURFACE/'], $trechoStr)));

    // Contagem de passageiros
    $qtdPassageiros = 1;
    if (is_string($passageiros)) {
        $qtdPassageiros = substr_count($passageiros, ',') + 1;
    } elseif (is_numeric($passageiros)) {
        $qtdPassageiros = intval($passageiros);
    }

    $distancia = distanciaTrecho($trechoCorrigido, $aeroportos);
    if ($distancia === null) {
        $resultados[] = [
            'linha' => $id,
            'erro' => "Aeroporto não encontrado em trecho: $trechoCorrigido",
            'trecho' => $trechoCorrigido,
            'passageiros' => $qtdPassageiros
        ];
        continue;
    }

    $co2UmPassageiro = ($distancia / KM_MINUTO) * CO2_MINUTO;
    $co2Total = $co2UmPassageiro * $qtdPassageiros;

    $resultados[] = [
        'linha' => $id,
        'trecho' => $trechoCorrigido,
        'qtpassageiros' => $qtdPassageiros,
        'passageiros' => $passageiros,
        'distancia_km' => round($distancia, 2),
        'co2_kg' => round($co2Total, 2),
        'pagante' => $linha['pagante'] ?? '',
        'data_venda' => $linha['data_venda'] ?? ''
    ];
}

// Salva JSON
file_put_contents('cache_resultados.json', json_encode($resultados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Cache de resultados gerado com " . count($resultados) . " linhas.\n";
