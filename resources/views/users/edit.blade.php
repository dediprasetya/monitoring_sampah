@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Edit Pengguna</h3>
    <form action="{{ route('users.update', $user) }}" method="POST">
        @csrf @method('PUT')
        <input name="name" value="{{ $user->name }}" class="form-control mb-2" placeholder="Nama" required>
        <input name="email" value="{{ $user->email }}" type="email" class="form-control mb-2" placeholder="Email" required>
        <input name="password" type="password" class="form-control mb-2" placeholder="Password (opsional)">
        <select name="role" class="form-control mb-2" required>
            <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
            <option value="petugas" {{ $user->role == 'petugas' ? 'selected' : '' }}>Petugas</option>
        </select>
        <button class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
