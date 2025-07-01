<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ route('dashboard') }}" class="brand-link">
    <span class="brand-text font-weight-light">
      @if(Auth::check())
        @if(Auth::user()->role === 'owner')
          Multicom
        @else
          {{ Auth::user()->branch->name ?? 'Multicom' }}
        @endif
      @endif
    </span>
    </a>

    <div class="sidebar">
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
          <li class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>

          @if(Auth::check() && Auth::user()->role === 'admin')
          <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon fas fa-boxes"></i>
                <p>
                    Master
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('brands.index') }}" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Brand</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('types.index') }}" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Type</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('products.index') }}" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Produk</p>
                    </a>
                </li>
            </ul>
        </li>
            <li class="nav-item">
              <a href="{{ route('suppliers.index') }}" class="nav-link">
                <i class="nav-icon fas fa-truck"></i>
                <p>Supplier</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('purchases.index') }}" class="nav-link">
                <i class="nav-icon fas fa-cart-plus"></i>
                <p>Pembelian</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('stock-transfers.index') }}" class="nav-link">
                <i class="nav-icon fas fa-exchange-alt"></i>
                <p>Transfer Stok</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('stok-cabang') }}" class="nav-link">
                <i class="nav-icon fas fa-boxes"></i>
                <p>Stok</p>
              </a>
            </li>

          @elseif(Auth::check() && Auth::user()->role === 'kepala_toko')
            <li class="nav-item">
              <a href="{{ route('sales.index') }}" class="nav-link">
                <i class="nav-icon fas fa-cash-register"></i>
                <p>Barang Keluar</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('stock-transfers.index') }}" class="nav-link">
                <i class="nav-icon fas fa-exchange-alt"></i>
                <p>Transfer Stok</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('purchases.index') }}" class="nav-link">
                <i class="nav-icon fas fa-cart-plus"></i>
                <p>Pembelian</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('stok-cabang') }}" class="nav-link">
                <i class="nav-icon fas fa-warehouse"></i>
                <p>Stok Cabang</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('stock-requests.index') }}" class="nav-link">
                <i class="nav-icon fas fa-cash-register"></i>
                <p>Permintaan Barang</p>
              </a>
            </li>

          @elseif(Auth::check() && Auth::user()->role === 'owner')
            <li class="nav-item">
              <a href="{{ route('stok-cabang') }}" class="nav-link">
                <i class="nav-icon fas fa-boxes"></i>
                <p>Stok</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('branches.index') }}" class="nav-link">
                <i class="nav-icon fas fa-store"></i>
                <p>Manajemen Cabang</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('users.index') }}" class="nav-link">
                <i class="nav-icon fas fa-users-cog"></i>
                <p>Manajemen User</p>
              </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('owner.laporan.penjualan') }}" class="nav-link">
                  <i class="nav-icon fas fa-boxes"></i>
                  <p>Laporan Penjualan Cabang</p>
                </a>
              </li>
          @endif

        </ul>
      </nav>
    </div>
  </aside>
