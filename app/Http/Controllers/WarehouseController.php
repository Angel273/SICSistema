<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseController extends Controller
{
    public function index()
    {
        $rows = DB::table('warehouses as w')
            ->leftJoin('stores as s','s.id','=','w.store_id')
            ->select('w.*','s.name as store_name','s.code as store_code')
            ->orderByDesc('w.id')
            ->paginate(12);

        return view('warehouses.index', compact('rows'));
    }

    public function create()
    {
        $stores = DB::table('stores')->orderBy('name')->get();
        return view('warehouses.create', compact('stores'));
    }

    public function store(Request $r)
    {
        $r->validate([
            'store_id' => 'required|integer|exists:stores,id',
            'code'     => 'required|string|max:20|unique:warehouses,code',
            'name'     => 'required|string|max:100',
        ]);

        DB::table('warehouses')->insert([
            'store_id' => $r->store_id,
            'code'     => $r->code,
            'name'     => $r->name,
            'address'  => $r->input('address'),
        ]);

        return redirect()->route('dashboard')->with('ok','Bodega creada');
    }

    public function edit($id)
    {
        $warehouse = DB::table('warehouses')->find($id);
        abort_if(!$warehouse, 404);
        $stores = DB::table('stores')->orderBy('name')->get();
        return view('warehouses.edit', compact('warehouse','stores'));
    }

    public function update(Request $r, $id)
    {
        $r->validate([
            'store_id' => 'required|integer|exists:stores,id',
            'code'     => "required|string|max:20|unique:warehouses,code,$id",
            'name'     => 'required|string|max:100',
        ]);

        DB::table('warehouses')->where('id',$id)->update([
            'store_id' => $r->store_id,
            'code'     => $r->code,
            'name'     => $r->name,
            'address'  => $r->input('address'),
        ]);

        return redirect()->route('dashboard')->with('ok','Bodega actualizada');
    }

    public function destroy($id)
    {
        // Nota: si ya hay stocks vinculados, podrías bloquear borrado.
        DB::table('warehouses')->where('id',$id)->delete();
        return back()->with('ok','Bodega eliminada');
    }
}
