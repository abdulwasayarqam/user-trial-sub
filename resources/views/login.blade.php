@extends('layouts.auth-layout')

@section('content')
    <form action="{{ route('userLogin') }}" method="post">
        @csrf

        @if (\Session::has('error'))
            <p style="color: red;">{{ \Session::get('error') }}</p>
        @endif

        <h1>Login</h1>

        <fieldset>

            <label for="mail">Email:</label>
            <input type="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" name="password" required>

        </fieldset>

        <button type="submit">Login</button>
    </form>
@endsection
