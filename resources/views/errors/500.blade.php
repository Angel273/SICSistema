@extends('layout')
@section('content')
  <h2>500 – Error del servidor</h2>
  <p>{{ $message ?? 'Ups… algo salió mal. Intenta de nuevo.' }}</p>
  <p><a href="{{ route('dashboard') }}">Volver al dashboard</a></p>
@endsection
