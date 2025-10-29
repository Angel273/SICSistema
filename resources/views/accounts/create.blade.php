@extends('layout')

@section('title', 'Nueva Cuenta Contable')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

  {{-- Header --}}
  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold">Nueva Cuenta Contable</h1>
    <a href="{{ route('accounts.index') }}" class="text-sm text-gray-500 hover:text-black">← Volver al catálogo</a>
  </div>

  {{-- Errores --}}
  @if ($errors->any())
    <div class="rounded-lg bg-red-50 border border-red-200 text-red-700 p-4 text-sm">
      <div class="font-semibold mb-1">Se encontraron errores:</div>
      <ul class="list-disc list-inside space-y-1">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Formulario --}}
  <form method="POST" action="{{ route('accounts.store') }}"
        class="grid gap-4 p-6 bg-white border rounded-xl shadow-sm">
    @csrf

    <div>
      <label class="block text-sm text-gray-600 mb-1 font-medium">Código</label>
      <input
        type="text"
        name="code"
        value="{{ old('code') }}"
        required maxlength="20"
        placeholder="1101"
        class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
      >
      <p class="text-xs text-gray-400 mt-1">Ejemplo: 1101 para Caja, 1201 para Bancos.</p>
    </div>

    <div>
      <label class="block text-sm text-gray-600 mb-1 font-medium">Nombre</label>
      <input
        type="text"
        name="name"
        value="{{ old('name') }}"
        required maxlength="120"
        placeholder="Caja general"
        class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
      >
    </div>

    <div>
      <label class="block text-sm text-gray-600 mb-1 font-medium">Tipo de cuenta</label>
      <select
        name="type"
        required
        class="w-full border rounded-lg px-3 py-2 bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none"
      >
        <option value="">-- Selecciona --</option>
        @foreach($types as $t)
          <option value="{{ $t }}" {{ old('type')===$t ? 'selected' : '' }}>
            {{ ucfirst($t) }}
          </option>
        @endforeach
      </select>
    </div>

    <div class="flex items-center justify-end gap-3 pt-4 border-t">
      <a href="{{ route('accounts.index') }}"
         class="px-4 py-2 rounded-lg border text-sm hover:bg-gray-50">Cancelar</a>
      <button
        type="submit"
        class="px-5 py-2 rounded-lg bg-black text-white text-sm hover:opacity-90">
        Guardar cuenta
      </button>
    </div>
  </form>
</div>

{{-- Estilos para impresión mínima (opcional) --}}
<style>
@media print {
  a[href]:after { content: ''; }
  nav, .border-t, button, .flex.items-center.justify-between { display:none !important; }
}
</style>
@endsection

