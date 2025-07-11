<?php
// airports_to_json.php

$inputFile = 'airports.dat';
$outputFile = 'airports.json';

if (!file_exists($inputFile)) {
    die("Arquivo $inputFile não encontrado.\n");
}

$aeroportos = [];

$handle = fopen($inputFile, 'r');
if (!$handle) {
    die("Erro ao abrir $inputFile.\n");
}

while (($line = fgets($handle)) !== false) {
    $cols = str_getcsv($line);

    // A coluna 4 é o código IATA do aeroporto (índice 4 começando em 0)
    // Colunas 6 e 7 são latitude e longitude
    // Ajuste se seu arquivo for diferente
    $iata = trim($cols[4], '"');
    $latitude = floatval($cols[6]);
    $longitude = floatval($cols[7]);

    if ($iata !== '') {
        $aeroportos[$iata] = [
            'latitude' => $latitude,
            'longitude' => $longitude
        ];
    }
}

fclose($handle);

// Salva JSON
file_put_contents($outputFile, json_encode($aeroportos, JSON_PRETTY_PRINT));

echo "Arquivo $outputFile gerado com " . count($aeroportos) . " aeroportos.\n";
