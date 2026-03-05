@extends('adminlte::page')

@section('title', 'Operador — ' . $user->username)

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1><i class="{{ $user->is_npc ? 'fas fa-robot' : 'fas fa-user' }}"></i> {{ $user->username }}</h1>
    <div>
        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-warning btn-sm">
            <i class="fas fa-edit"></i> Editar
        </a>
        <a href="{{ route('admin.users') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        {{ session('success') }}
    </div>
@endif

<div class="row">
    {{-- Profile Card --}}
    <div class="col-md-4">
        <div class="card card-{{ $user->is_npc ? 'secondary' : 'primary' }}">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i
                        class="fas {{ $user->is_npc ? 'fa-robot' : 'fa-user-secret' }} fa-5x text-{{ $user->is_npc ? 'muted' : 'primary' }}"></i>
                </div>
                <h3 class="mb-0">{{ $user->username }}</h3>
                <p>
                    <span
                        class="badge badge-{{ $user->role === 'admin' ? 'danger' : ($user->is_npc ? 'secondary' : 'success') }}">
                        {{ $user->is_npc ? 'NPC' : strtoupper($user->role) }}
                    </span>
                    @if($user->is_online)
                        <span class="badge badge-success">● ONLINE</span>
                    @else
                        <span class="badge badge-secondary">○ OFFLINE</span>
                    @endif
                </p>
                <p class="text-muted mb-0">Nível {{ $user->level }}</p>
                <p class="text-muted small">Criado: {{ $user->created_at?->format('d/m/Y H:i') ?? 'N/A' }}</p>
            </div>
        </div>

        {{-- Hardware --}}
        <div class="card card-dark mt-3">
            <div class="card-header">
                <h4 class="card-title">Hardware</h4>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tr>
                        <th>CPU</th>
                        <td>{{ $user->cpu ?? 1 }} GHz</td>
                    </tr>
                    <tr>
                        <th>RAM</th>
                        <td>{{ $user->ram ?? 2 }} GB</td>
                    </tr>
                    <tr>
                        <th>SSD</th>
                        <td>{{ $user->ssd ?? 50 }} GB</td>
                    </tr>
                    <tr>
                        <th>Energia</th>
                        <td>{{ $user->energy_points ?? 100 }} pts</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Stats & Details --}}
    <div class="col-md-8">
        <div class="card card-dark">
            <div class="card-header">
                <h4 class="card-title">Informações de Rede</h4>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tr>
                        <th width="200">Email</th>
                        <td>{{ $user->email ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>Último IP</th>
                        <td><code>{{ $user->last_seen_ip ?? 'N/A' }}</code></td>
                    </tr>
                    <tr>
                        <th>Último acesso</th>
                        <td>{{ $user->last_seen_at?->diffForHumans() ?? 'Nunca' }}</td>
                    </tr>
                    <tr>
                        <th>Reputação</th>
                        <td>{{ number_format($user->reputation_score ?? 0) }}</td>
                    </tr>
                    <tr>
                        <th>Vulnerável até</th>
                        <td>
                            @if($user->vulnerable_until && \Carbon\Carbon::parse($user->vulnerable_until)->isFuture())
                                <span class="badge badge-danger">
                                    {{ \Carbon\Carbon::parse($user->vulnerable_until)->diffForHumans() }}
                                </span>
                            @else
                                <span class="badge badge-success">Seguro</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Stats JSON --}}
        <div class="card card-dark mt-3">
            <div class="card-header">
                <h4 class="card-title">Stats do Jogo (JSON)</h4>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tr>
                        <th width="200">Créditos</th>
                        <td>{{ $user->stats['credits'] ?? 0 }} CR</td>
                    </tr>
                    <tr>
                        <th>XP</th>
                        <td>{{ $user->stats['xp'] ?? 0 }}</td>
                    </tr>
                    <tr>
                        <th>Inventário</th>
                        <td>
                            @foreach($user->stats['inventory'] ?? [] as $prog)
                                <span class="badge badge-info">{{ $prog }}</span>
                            @endforeach
                            @if(empty($user->stats['inventory'])) <em class="text-muted">vazio</em> @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Flags ativas</th>
                        <td>
                            @if($user->stats['keylog_charges'] ?? 0) <span class="badge badge-warning">keylog
                            ({{ $user->stats['keylog_charges'] }}x)</span> @endif
                            @if($user->stats['stealth_active'] ?? false) <span class="badge badge-info">stealth</span>
                            @endif
                            @if($user->stats['cracker_active'] ?? false) <span
                            class="badge badge-primary">cracker</span> @endif
                            @if($user->stats['firewall_active'] ?? false) <span
                            class="badge badge-success">firewall</span> @endif
                            @if($user->stats['virus_infected'] ?? false) <span class="badge badge-danger">VIRUS</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Quick actions --}}
        <div class="card card-dark mt-3">
            <div class="card-header">
                <h4 class="card-title">Ações Rápidas</h4>
            </div>
            <div class="card-body">
                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-warning mr-2">
                    <i class="fas fa-edit"></i> Editar Operador
                </a>
                <a href="{{ route('admin.users') }}" class="btn btn-secondary">
                    <i class="fas fa-list"></i> Lista de Operadores
                </a>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="/css/admin_custom.css">
@stop