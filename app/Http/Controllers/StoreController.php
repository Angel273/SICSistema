<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoreController extends Controller
{
    public function index()
    {
        $rows = DB::table('stores')->orderByDesc('id')->paginate(12);
        return view('stores.index', compact('rows'));
    }

    public function create()
    {
        return view('stores.create');
    }

    public function store(Request $r)
    {
        $r->validate([
            'code' => 'required|string|max:20|unique:stores,code',
            'name' => 'required|string|max:100',
        ]);

        DB::table('stores')->insert([
            'code' => $r->code,
            'name' => $r->name,
            'address' => $r->input('address'),
        ]);

        return redirect()->route('dashboard')->with('ok','Tienda creada');
    }

    public function edit($id)
    {
        $store = DB::table('stores')->find($id);
        abort_if(!$store, 404);
        return view('stores.edit', compact('store'));
    }

    public function update(Request $r, $id)
    {
        $r->validate([
            'code' => "required|string|max:20|unique:stores,code,$id",
            'name' => 'required|string|max:100',
        ]);

        DB::table('stores')->where('id',$id)->update([
            'code' => $r->code,
            'name' => $r->name,
            'address' => $r->input('address'),
        ]);

        return redirect()->route('dashboard')->with('ok','Tienda actualizada');
    }

    public function destroy($id)
    {
        DB::table('stores')->where('id',$id)->delete();
        return back()->with('ok','Tienda eliminada');
    }
}

