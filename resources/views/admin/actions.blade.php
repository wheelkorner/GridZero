@extends('adminlte::page')

@section('title', 'Ações em Tempo Real')

@section('content_header')
<h1>Monitoramento de Atividades (SUDO View)</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-dark">
            <div class="card-header border-transparent">
                <h3 class="card-title">Últimas 100 Operações no Grid</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table m-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Operador</th>
                                <th>Tipo</th>
                                <th>Alvo</th>
                                <th>Status</th>
                                <th>Início</th>
                                <th>Fim</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($actions as $action)
                                <tr>
                                    <td>#{{ $action->id }}</td>
                                    <td><strong>{{ $action->user->username ?? 'Unknown' }}</strong></td>
                                    <td>
                                        <span class="badge badge-info">{{ strtoupper($action->type) }}</span>
                                    </td>
                                    <td>
                                        @if($action->node)
                                            <code>{{ $action->node->name }}</code>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($action->status === 'pending')
                                            <span class="badge badge-warning">EXECUTANDO</span>
                                        @elseif($action->status === 'completed')
                                            <span class="badge badge-success">SUCESSO</span>
                                        @else
                                            <span class="badge badge-danger">{{ strtoupper($action->status) }}</span>
                                        @endif
                                    </td>
                                    <td><small>{{ $action->created_at->format('H:i:s') }}</small></td>
                                    <td>
                                        @if($action->ends_at)
                                            <small>{{ $action->ends_at->format('H:i:s') }}</small>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
<script> console.log('Real-time action monitoring enabled.'); </script>
@stop