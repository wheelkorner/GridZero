@extends('adminlte::page')

@section('title', 'GridZero Admin')

@section('content_header')
<h1>Dashboard Administrativo</h1>
@stop

@section('content')
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $totalPlayers }}</h3>
                <p>Operadores na Rede</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <a href="#" class="small-box-footer">Ver Mais <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $onlineCount }}</h3>
                <p>Online agora</p>
            </div>
            <div class="icon">
                <i class="fas fa-signal"></i>
            </div>
            <a href="#" class="small-box-footer">Ver Mais <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $activeActions }}</h3>
                <p>Ações em Progresso</p>
            </div>
            <div class="icon">
                <i class="fas fa-tasks"></i>
            </div>
            <a href="#" class="small-box-footer">Ver Mais <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>99.9<sup style="font-size: 20px">%</sup></h3>
                <p>Integridade do Grid</p>
            </div>
            <div class="icon">
                <i class="fas fa-server"></i>
            </div>
            <a href="#" class="small-box-footer">Ver Mais <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card card-dark">
            <div class="card-header">
                <h3 class="card-title">Novos Operadores</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Nível</th>
                            <th>Criado em</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentUsers as $recent)
                            <tr>
                                <td>{{ $recent->username }}</td>
                                <td><span class="badge badge-primary">LVL {{ $recent->level }}</span></td>
                                <td>{{ $recent->created_at->diffForHumans() }}</td>
                                <td>
                                    @if($recent->last_seen_at && $recent->last_seen_at->gt(now()->subMinutes(5)))
                                        <span class="badge badge-success">ONLINE</span>
                                    @else
                                        <span class="badge badge-secondary">OFFLINE</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">Ferramentas de Admin</h3>
            </div>
            <div class="card-body">
                <a href="{{ url('logs') }}" class="btn btn-block btn-outline-danger btn-lg">
                    <i class="fas fa-file-medical-alt"></i> Ver Logs do Sistema
                </a>
                <hr>
                <p class="text-muted">Acesso como ROOT ao sistema de arquivos do servidor e monitoramento de tráfego.
                </p>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
{{--
<link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
<script> console.log('SUDO MODE ACTIVE.'); </script>
@stop