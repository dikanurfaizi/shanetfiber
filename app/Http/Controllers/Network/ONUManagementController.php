<?php

namespace App\Http\Controllers\Network;

use App\Http\Controllers\Controller;
use App\Models\Network\Onu as ONU;
use Illuminate\Http\Request;

class ONUManagementController extends Controller
{
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show(ONU $onu, Request $request) {}
    public function edit(ONU $onu) {}
    public function update(Request $request, ONU $onu) {}
    public function delete(ONU $onu) {}
    public function destroy(ONU $onu) {}
}
