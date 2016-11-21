@extends('admin.admin_base')

@section('title')
  413
@endsection

@section('content')
  <div class="row">
    <div class="error-page">
      <h2 class="headline text-yellow">413</h2>
      <br />
      <div class="error-content">
        <h3><i class="fa fa-warning text-yellow"></i> Sua Sessão expirou!</h3>

        <p>
          Você está tentando submeter uma alocação com sua sessão expirada. Efetue
          o logoff e então faça o login para renovar a sessão e realizar sua alocação.
        </p>
      </div>
    </div>
  </div>
@endsection
