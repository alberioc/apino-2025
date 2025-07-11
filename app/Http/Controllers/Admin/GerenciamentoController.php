<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class GerenciamentoController extends Controller
{
    public function index()
    {
        return view('admin.gerenciamento');
    }
}
