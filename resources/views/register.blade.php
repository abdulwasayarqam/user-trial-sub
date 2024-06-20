@extends('layouts.auth-layout')

@section('content')
    <form action="{{ route('userRegister') }}" method="post">
        @csrf

        @if (\Session::has('success'))
            <p style="color: green;">{{ \Session::get('success') }}</p>
        @elseif (\Session::has('error'))
            <p style="color: red;">{{ \Session::get('error') }}</p>
        @endif

        <h1>Sign Up</h1>

        <fieldset>
            <label for="name">Name:</label>
            <input type="text" name="name" required>

            <label for="mail">Email:</label>
            <input type="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" name="password" required>

        </fieldset>

        <button type="submit">Register</button>
    </form>
@endsection
