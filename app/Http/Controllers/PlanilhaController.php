<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PlanilhaController extends Controller
{
    public function index()
    {
        return view('planilhas.index');
    }

    public function editar()
    {
        $dados = session('planilha_dados', []);

        return view('planilhas.editar', compact('dados'));
    }

    public function processar(Request $request)
    {
        set_time_limit(300);

        $request->validate([
            'arquivo' => 'required|file|mimes:xlsx,xls',
        ]);

        $arquivo = $request->file('arquivo');
        $spreadsheet = IOFactory::load($arquivo->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        $airportsFile = storage_path('app/data/airports.dat');
        $airports = [];
        if (file_exists($airportsFile)) {
            $lines = file($airportsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $fields = str_getcsv($line);
                $iata = $fields[4] ?? null;
                $cidade = $fields[2] ?? null;
                if ($iata && $cidade) {
                    $airports[$iata] = $cidade;
                }
            }
        }

        $vendas = DB::table('vendas')->get();
        $vendasIndexadas = [];
        $documentosPassagem = [];
        $alugueisAgrupados = [];
        
        foreach ($vendas as $venda) {
            $chave = $venda->venda_numero . '|' . $venda->produto . '|' . $venda->fornecedor;
            $vendasIndexadas[$chave] = $venda;

            if (strtolower($venda->produto) === 'passagem aérea' && $venda->documento) {
                $documentosPassagem[$venda->documento] = $venda;
            }

            try {
                $dataKey = \Carbon\Carbon::parse($venda->data_inicio)->format('Y-m-d');
            } catch (\Exception $e) {
                $dataKey = null;
            }

            $produtoKey = strtolower(trim($venda->produto));
            if ($dataKey) {
                $alugueisAgrupados[$venda->venda_numero][$dataKey][$produtoKey] = $venda;
            }
        }

        $dados = [];

        for ($row = 2; $row <= $highestRow; $row++) {
            $vendaNumero = trim($sheet->getCell("A$row")->getValue());
            $produto = trim($sheet->getCell("C$row")->getValue());
            $documento = trim($sheet->getCell("D$row")->getValue());
            $fornecedor = trim($sheet->getCell("E$row")->getValue());
            $dataInicioExcel = $sheet->getCell("H$row")->getValue();

            $linha = [
                'venda_numero' => $vendaNumero,
                'data_venda' => $sheet->getCell("B$row")->getFormattedValue(),
                'produto' => $produto,
                'documento' => $documento,
                'fornecedor' => $fornecedor,
                'centro_custo' => $sheet->getCell("F$row")->getValue(),
                'valor' => $sheet->getCell("G$row")->getValue(),
                'data_inicio' => $sheet->getCell("H$row")->getFormattedValue(),
                'data_fim' => $sheet->getCell("I$row")->getFormattedValue(),
                'trecho' => '',
                'origem' => '',
                'destino' => '',
            ];

            $chave = $vendaNumero . '|' . $produto . '|' . $fornecedor;
            $venda = $vendasIndexadas[$chave] ?? null;
            $produtoLower = strtolower($produto);

            $extrairOrigemDestino = function ($trechos) use ($airports) {
                $origem = $destino = '';
                if (str_contains($trechos, '//')) {
                    $partes = explode('//', $trechos);
                    $primeiroTrecho = trim($partes[0] ?? '');
                    $codigos = preg_split('/[-x]/i', $primeiroTrecho);
                } else {
                    $codigos = preg_split('/[-x]/i', $trechos);
                }

                $codigos = array_filter(array_map('trim', $codigos));
                $codigos = array_values($codigos);

                $origemCodigo = $codigos[0] ?? '';
                $ultimoCodigo = end($codigos) ?? '';

                if (count($codigos) === 3 && $origemCodigo === $ultimoCodigo) {
                    $destinoCodigo = $codigos[1];
                } elseif (count($codigos) > 3 && $origemCodigo === $ultimoCodigo) {
                    $frequencias = [];
                    foreach ($codigos as $c) {
                        if ($c !== $origemCodigo) {
                            $frequencias[$c] = ($frequencias[$c] ?? 0) + 1;
                        }
                    }
                    arsort($frequencias);
                    $destinoCodigo = array_key_first($frequencias);
                } else {
                    $destinoCodigo = $ultimoCodigo;
                }

                return [
                    'origem' => $airports[$origemCodigo] ?? $origemCodigo,
                    'destino' => $airports[$destinoCodigo] ?? $destinoCodigo,
                ];
            };

            if ($produtoLower === 'passagem aérea' && $venda) {
                $linha['trecho'] = $venda->trechos ?? '';
                $resultado = $extrairOrigemDestino($linha['trecho']);
                $linha['origem'] = $resultado['origem'];
                $linha['destino'] = $resultado['destino'];

            } elseif (str_contains($produtoLower, 'diárias de hospedagem') && $venda) {
                $linha['destino'] = $venda->cidade_fornecedor;

            } elseif (in_array($produtoLower, ['assento conforto', 'bagagem extra'])) {
                if (isset($documentosPassagem[$documento])) {
                    $passagem = $documentosPassagem[$documento];
                    $linha['trecho'] = $passagem->trechos ?? '';
                    $resultado = $extrairOrigemDestino($linha['trecho']);
                    $linha['origem'] = $resultado['origem'];
                    $linha['destino'] = $resultado['destino'];
                }

            } elseif ($produtoLower === 'aluguel de carro') {
                try {
                    if (is_numeric($dataInicioExcel)) {
                        // Excel serial date
                        $dataInicio = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dataInicioExcel)->format('Y-m-d');
                    } else {
                        // String formato dd/mm/yyyy da planilha
                        $dataInicio = \Carbon\Carbon::createFromFormat('d/m/Y', $dataInicioExcel)->format('Y-m-d');
                    }
                } catch (\Exception $e) {
                    $dataInicio = null;
                }
                //dd($dataInicio, array_keys($alugueisAgrupados[$vendaNumero] ?? []));
                if ($vendaNumero && $dataInicio) {
                    $refHosp = $alugueisAgrupados[$vendaNumero][$dataInicio]['diárias de hospedagem'] ?? null;
                    $refPassagem = $alugueisAgrupados[$vendaNumero][$dataInicio]['passagem aérea'] ?? null;

                    if ($refHosp && $refHosp->cidade_fornecedor) {
                        $linha['destino'] = $refHosp->cidade_fornecedor;
                    } elseif ($refPassagem && $refPassagem->trechos) {
                        $linha['trecho'] = $refPassagem->trechos;
                        $resultado = $extrairOrigemDestino($linha['trecho']);
                        $linha['destino'] = $resultado['destino'];
                    }
                }
            }

            $dados[] = $linha;
        }

        session(['planilha_dados' => $dados]);
        return redirect()->route('planilhas.editar');
    }

    public function salvar(Request $request)
    {
        $dados = $request->input('dados', []);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Cabeçalhos
        $colunas = [
            'Venda Nº', 'Data Venda', 'Produto', 'Documento', 'Fornecedor',
            'Centro de Custo', 'Valor', 'Data Início', 'Data Fim',
            'Trecho', 'Origem', 'Destino'
        ];

        $sheet->fromArray($colunas, null, 'A1');

        // Linhas
        $rowNum = 2;
        foreach ($dados as $linha) {
            $linhaArray = [
                $linha['venda_numero'] ?? '',
                $linha['data_venda'] ?? '',
                $linha['produto'] ?? '',
                $linha['documento'] ?? '',
                $linha['fornecedor'] ?? '',
                $linha['centro_custo'] ?? '',
                $linha['valor'] ?? '',
                $linha['data_inicio'] ?? '',
                $linha['data_fim'] ?? '',
                $linha['trecho'] ?? '',
                $linha['origem'] ?? '',
                $linha['destino'] ?? '',
            ];
            $sheet->fromArray($linhaArray, null, 'A' . $rowNum);
            $rowNum++;
        }

        // Salva o arquivo em storage
        $nomeArquivo = 'planilha_editada_' . time() . '.xlsx';
        $caminho = 'planilhas_processadas/' . $nomeArquivo;

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save(storage_path("app/{$caminho}"));

        return response()->download(storage_path("app/{$caminho}"))->deleteFileAfterSend(true);
    }

    public function resultado(Request $request)
    {
        $arquivo = $request->get('arquivo');

        if (!$arquivo || !Storage::exists('planilhas_processadas/' . $arquivo)) {
            return redirect()->route('planilhas.index')->with('error', 'Arquivo não encontrado.');
        }

        return view('planilhas.resultado', compact('arquivo'));
    }

    public function download($arquivo)
    {
        $caminho = 'planilhas_processadas/' . $arquivo;

        if (!Storage::exists($caminho)) {
            return redirect()->route('planilhas.index')->with('error', 'Arquivo não encontrado.');
        }

        return Storage::download($caminho, $arquivo);
    }
}
