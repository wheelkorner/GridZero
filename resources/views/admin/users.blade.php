@extends('adminlte::page')

@section('title', 'Gerenciamento de Operadores')

@section('content_header')
<h1>Lista de Operadores no Grid</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-dark">
            <div class="card-header">
                <h3 class="card-title">Hackers e NPCs Registrados</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Level</th>
                            <th>Role</th>
                            <th>Reputação</th>
                            <th>Último IP</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>
                                    @if($user->is_npc)
                                        <i class="fas fa-robot text-muted" title="NPC"></i>
                                    @else
                                        <i class="fas fa-user text-primary" title="Jogador Real"></i>
                                    @endif
                                    {{ $user->username }}
                                    @if($user->is_npc)
                                        <span class="badge badge-secondary ml-1" style="font-size: 0.65rem;">NPC</span>
                                    @else
                                        <span class="badge badge-success ml-1" style="font-size: 0.65rem;">PLAYER</span>
                                    @endif
                                </td>
                                <td>
                                    <span
                                        class="badge {{ $user->level > 100 ? 'bg-danger' : ($user->level > 50 ? 'bg-warning' : 'bg-primary') }}">
                                        LVL {{ $user->level }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $user->role === 'admin' ? 'bg-purple' : 'bg-info' }}">
                                        {{ strtoupper($user->role) }}
                                    </span>
                                </td>
                                <td>{{ number_format($user->reputation_score) }}</td>
                                <td><code>{{ $user->last_seen_ip ?? 'N/A' }}</code></td>
                                <td>
                                    @if($user->is_online)
                                        <span class="badge badge-success">ONLINE</span>
                                    @else
                                        <span class="badge badge-secondary">OFFLINE</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-xs btn-primary">
                                        <i class="fas fa-eye"></i> INFO
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-xs btn-warning">
                                        <i class="fas fa-edit"></i> EDIT
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="/css/admin_custom.css">
<style>
    .bg-purple {
        background-color: #6f42c1 !important;
    }
</style>
@stop

@section('js')
<script> console.log('Hacker identification active.'); </script>
@stop