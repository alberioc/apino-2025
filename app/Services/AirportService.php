<?php
namespace App\Services;

class AirportService
{
    private $airports = [];

    public function __construct()
    {
        $this->loadAirports();
    }

    private function loadAirports()
    {
        $path = storage_path('app/data/airports.dat'); // coloque o arquivo aqui
        if (!file_exists($path)) {
            throw new \Exception("Arquivo de aeroportos não encontrado");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $data = str_getcsv($line);

            $iata = $data[4];
            $lat = floatval($data[6]);
            $lon = floatval($data[7]);

            $this->airports[$iata] = ['lat' => $lat, 'lon' => $lon];
        }
    }

    public function getCoordinates(string $iata): ?array
    {
        return $this->airports[$iata] ?? null;
    }

    public function haversineDistance(array $coord1, array $coord2): float
    {
        $earthRadius = 6371; // km

        $latFrom = deg2rad($coord1['lat']);
        $lonFrom = deg2rad($coord1['lon']);
        $latTo = deg2rad($coord2['lat']);
        $lonTo = deg2rad($coord2['lon']);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos($latFrom) * cos($latTo) *
            sin($lonDelta / 2) * sin($lonDelta / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
