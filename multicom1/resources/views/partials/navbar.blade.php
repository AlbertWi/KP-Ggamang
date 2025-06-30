<nav class="main-header navbar navbar-expand navbar-white navbar-light border-bottom">
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                <i class="fas fa-bars"></i>
            </a>
        </li>
    </ul>
    <ul class="navbar-nav ml-auto">
    <li class="nav-item dropdown">
            <a class="nav-link" data-bs-toggle="dropdown" href="#" role="button">
                <i class="fas fa-user-circle me-1"></i> {{ Auth::user()->name }}
            </a>
            <div class="dropdown-menu dropdown-menu-end">
                <span class="dropdown-item disabled">Role: {{ Auth::user()->role }}</span>
                <div class="dropdown-divider"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger">
                        <i class="fas fa-sign-out-alt me-1"></i> Logout
                    </button>
                </form>
            </div>
        </li>
    </ul>
</nav>
