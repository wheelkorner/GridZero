@extends('adminlte::page')

@section('title', 'Gerenciamento de Nodes')

@section('content_header')
<h1>Infraestrutura do Grid (Nodes)</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-dark">
            <div class="card-header border-transparent">
                <h3 class="card-title">Nodes da Rede</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table m-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome do Host</th>
                                <th>Dificuldade</th>
                                <th>Multiplicador</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($nodes as $node)
                                <tr>
                                    <td><a href="#">#{{ $node->id }}</a></td>
                                    <td><code>{{ $node->name }}</code></td>
                                    <td>
                                        @php
                                            $width = min($node->difficulty * 10, 100);
                                            $color = $node->difficulty > 7 ? 'danger' : ($node->difficulty > 4 ? 'warning' : 'success');
                                        @endphp
                                        <div class="progress progress-xs">
                                            <div class="progress-bar bg-{{ $color }}" style="width: {{ $width }}%"></div>
                                        </div>
                                        <small>Lvl {{ $node->difficulty }}</small>
                                    </td>
                                    <td>
                                        <span
                                            class="badge badge-info">x{{ number_format($node->reward_multiplier, 1) }}</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary"><i
                                                class="fas fa-search"></i></button>
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
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
<script> console.log('Node infrastructure monitoring active.'); </script>
@stop