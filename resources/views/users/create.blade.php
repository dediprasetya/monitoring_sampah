@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Tambah Pengguna</h3>
    <form action="{{ route('users.store') }}" method="POST">
        @csrf
        <input name="name" class="form-control mb-2" placeholder="Nama" required>
        <input name="email" type="email" class="form-control mb-2" placeholder="Email" required>
        <input name="password" type="password" class="form-control mb-2" placeholder="Password" required>
        <select name="role" class="form-control mb-2" required>
            <option value="">Pilih Role</option>
            <option value="admin">Admin</option>
            <option value="petugas">Petugas</option>
        </select>
        <button class="btn btn-success">Simpan</button>
    </form>
</div>
@endsection
