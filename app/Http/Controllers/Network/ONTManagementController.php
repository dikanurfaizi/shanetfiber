<?php

namespace App\Http\Controllers\Network;

use App\Http\Controllers\Controller;
use App\Models\Network\Ont as ONT;
use Illuminate\Http\Request;

class ONTManagementController extends Controller
{
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show(ONT $ont, Request $request) {}
    public function edit(ONT $ont) {}
    public function update(Request $request, ONT $ont) {}
    public function delete(ONT $ont) {}
    public function destroy(ONT $ont) {}
}
