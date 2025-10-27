@extends('layout')
@section('content')
  <h2>404 – No encontrado</h2>
  <p>{{ $message ?? 'La página que buscas no existe.' }}</p>
  <p><a href="{{ route('dashboard') }}">Volver al dashboard</a></p>
@endsection
