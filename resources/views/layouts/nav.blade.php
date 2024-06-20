<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="{{ route('dashboard') }}">Trial & Subscription App</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
        aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">

            @if (auth()->user()->is_subscribed == 1)
                <li class="nav-item {{ Route::is('dashboard') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('dashboard') }}">Home</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ route('dashboard') }}">Test Menu</a>
                </li>
            @endif

            <li class="nav-item {{ Route::is('subscription') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('subscription') }}">Subscription</a>
            </li>

            <li class="nav-item">
                <a class="nav-link logoutUser" href="#">Logout</a>
            </li>
        </ul>
    </div>
</nav>
