<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function index(Request $r)
    {
        $q     = trim((string)$r->input('q'));
        $type  = trim((string)$r->input('type'));

        $rows = DB::table('accounts')
            ->when($q, function ($t) use ($q) {
                $like = "%{$q}%";
                $t->where(function ($w) use ($like) {
                    $w->where('code','like',$like)
                      ->orWhere('name','like',$like);
                });
            })
            ->when($type !== '', fn($t)=>$t->where('type', $type))
            ->orderBy('code')
            ->paginate(15)
            ->appends($r->query());

        $types = ['ACTIVO','PASIVO','PATRIMONIO','INGRESO','GASTO','COSTO'];

        return view('accounts.index', compact('rows','q','type','types'));
    }

    public function create()
    {
        $types = ['ACTIVO','PASIVO','PATRIMONIO','INGRESO','GASTO','COSTO'];
        return view('accounts.create', compact('types'));
    }

    public function store(Request $r)
    {
        $r->validate([
            'code' => 'required|string|max:20|unique:accounts,code',
            'name' => 'required|string|max:120',
            'type' => 'required|in:ACTIVO,PASIVO,PATRIMONIO,INGRESO,GASTO,COSTO',
        ]);

        DB::table('accounts')->insert([
            'code' => trim($r->code),
            'name' => trim($r->name),
            'type' => $r->type,
        ]);

        return redirect()->route('accounts.index')->with('ok','Cuenta creada');
    }

    public function edit($id)
    {
        $row = DB::table('accounts')->where('id',$id)->first();
        abort_if(!$row, 404);
        $types = ['ACTIVO','PASIVO','PATRIMONIO','INGRESO','GASTO','COSTO'];
        return view('accounts.edit', compact('row','types'));
    }

    public function update(Request $r, $id)
    {
        $r->validate([
            'code' => "required|string|max:20|unique:accounts,code,{$id}",
            'name' => 'required|string|max:120',
            'type' => 'required|in:ACTIVO,PASIVO,PATRIMONIO,INGRESO,GASTO,COSTO',
        ]);

        DB::table('accounts')->where('id',$id)->update([
            'code' => trim($r->code),
            'name' => trim($r->name),
            'type' => $r->type,
        ]);

        return redirect()->route('accounts.index')->with('ok','Cuenta actualizada');
    }

    public function destroy($id)
    {
        DB::table('accounts')->where('id',$id)->delete();
        return back()->with('ok','Cuenta eliminada');
    }
}
