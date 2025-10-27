<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    public function index()
    {
        $rows = DB::table('suppliers')->orderByDesc('id')->paginate(12);
        return view('suppliers.index', compact('rows'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $r)
    {
        $r->validate([
            'name'  => 'required|string|max:120',
            'email' => 'nullable|email|max:120|unique:suppliers,email',
            'phone' => 'nullable|string|max:40',
        ]);

        DB::table('suppliers')->insert([
            'name'    => $r->name,
            'email'   => $r->input('email'),
            'phone'   => $r->input('phone'),
            'address' => $r->input('address'),
            'tax_id'  => $r->input('tax_id'),
        ]);

        return redirect()->route('dashboard')->with('ok','Proveedor creado');
    }

    public function edit($id)
    {
        $supplier = DB::table('suppliers')->find($id);
        abort_if(!$supplier, 404);
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $r, $id)
    {
        $r->validate([
            'name'  => 'required|string|max:120',
            'email' => "nullable|email|max:120|unique:suppliers,email,$id",
            'phone' => 'nullable|string|max:40',
        ]);

        DB::table('suppliers')->where('id',$id)->update([
            'name'    => $r->name,
            'email'   => $r->input('email'),
            'phone'   => $r->input('phone'),
            'address' => $r->input('address'),
            'tax_id'  => $r->input('tax_id'),
        ]);

        return redirect()->route('dashboard')->with('ok','Proveedor actualizado');
    }

    public function destroy($id)
    {
        DB::table('suppliers')->where('id',$id)->delete();
        return back()->with('ok','Proveedor eliminado');
    }
}
