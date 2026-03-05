@extends('adminlte::page')

@section('title', 'Editar — ' . $user->username)

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1><i class="fas fa-edit"></i> Editar Operador: {{ $user->username }}</h1>
    <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Voltar
    </a>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-dark">
            <div class="card-header">
                <h4 class="card-title">
                    @if($user->is_npc)
                        <i class="fas fa-robot text-muted"></i> NPC
                    @else
                        <i class="fas fa-user text-primary"></i> Jogador
                    @endif
                    — {{ $user->username }}
                </h4>
            </div>

            <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                @csrf
                <div class="card-body">

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Row 1: Identificação --}}
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Username <span class="text-danger">*</span></label>
                            <input type="text" name="username"
                                class="form-control @error('username') is-invalid @enderror"
                                value="{{ old('username', $user->username) }}" required>
                            @error('username') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', $user->email) }}">
                            @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Row 2: Permissões --}}
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Nível <span class="text-danger">*</span></label>
                            <input type="number" name="level" class="form-control @error('level') is-invalid @enderror"
                                value="{{ old('level', $user->level) }}" min="1" max="999" required>
                            @error('level') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Role <span class="text-danger">*</span></label>
                            <select name="role" class="form-control @error('role') is-invalid @enderror">
                                <option value="player" {{ $user->role === 'player' ? 'selected' : '' }}>Player</option>
                                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                            @error('role') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Energia</label>
                            <input type="number" name="energy_points"
                                class="form-control @error('energy_points') is-invalid @enderror"
                                value="{{ old('energy_points', $user->energy_points) }}" min="0" max="1000">
                            @error('energy_points') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Row 3: Hardware --}}
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>CPU (GHz)</label>
                            <input type="number" name="cpu" class="form-control @error('cpu') is-invalid @enderror"
                                value="{{ old('cpu', $user->cpu) }}" min="1">
                            @error('cpu') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4 form-group">
                            <label>RAM (GB)</label>
                            <input type="number" name="ram" class="form-control @error('ram') is-invalid @enderror"
                                value="{{ old('ram', $user->ram) }}" min="1">
                            @error('ram') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4 form-group">
                            <label>SSD (GB)</label>
                            <input type="number" name="ssd" class="form-control @error('ssd') is-invalid @enderror"
                                value="{{ old('ssd', $user->ssd) }}" min="1">
                            @error('ssd') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Password --}}
                    <div class="form-group">
                        <label>Nova Senha <small class="text-muted">(deixe em branco para manter a
                                atual)</small></label>
                        <input type="password" name="password"
                            class="form-control @error('password') is-invalid @enderror">
                        @error('password') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                </div>

                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="/css/admin_custom.css">
@stop