@if(session('success'))
  <div class="alert alert-success d-flex align-items-center" role="alert">
    <i class="bi bi-check-circle me-2"></i>
    <div>{{ session('success') }}</div>
  </div>
@endif

@if($errors->any())
  <div class="alert alert-danger" role="alert">
    <div class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle me-2"></i>Hubo errores:</div>
    <ul class="mb-0">
      @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
    </ul>
  </div>
@endif
