<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rows =  DB::table('products')
        ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
        ->leftJoin('product_stocks as ps', 'ps.product_id', '=', 'products.id')
        ->selectRaw('products.*, categories.name AS category, COALESCE(SUM(ps.qty),0) AS stock_sum')
        ->groupBy('products.id','products.sku','products.name','products.category_id','products.has_serial','products.avg_cost','categories.name') // incluye las columnas de products.* que uses
        ->orderByDesc('products.id')
        ->paginate(10);
            return view('products.index', compact('rows'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $cats = DB::table('categories')->orderBy('name')->get();
        return view('products.create', compact('cats'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $r)
    {
       $r->validate(['sku'=>'required|unique:products','name'=>'required']);
        DB::table('products')->insert([
        'sku'=>$r->sku,'name'=>$r->name,'category_id'=>$r->category_id,
        'has_serial'=>$r->boolean('has_serial'), 'avg_cost'=>$r->input('avg_cost',0)
        ]);
        return redirect()->route('products.index')->with('ok','Producto creado');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $p = DB::table('products')->find($id);
        $cats = DB::table('categories')->orderBy('name')->get();
        return view('products.edit', compact('p','cats'));
      }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $r, string $id)
    {
        $r->validate(['sku'=>"required|unique:products,sku,$id",'name'=>'required']);
        DB::table('products')->where('id',$id)->update([
        'sku'=>$r->sku,'name'=>$r->name,'category_id'=>$r->category_id,
        'has_serial'=>$r->boolean('has_serial'),'avg_cost'=>$r->input('avg_cost',0)
        ]);
    return back()->with('ok','Actualizado');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::table('products')->where('id',$id)->delete();
        return back()->with('ok','Eliminado');
    }
}