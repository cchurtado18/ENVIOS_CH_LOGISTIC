<nav style="
    width: 100%;
    background: linear-gradient(120deg, rgba(248, 250, 252, 0.95), rgba(237, 243, 255, 0.95));
    border-bottom: 1px solid rgba(15, 23, 42, 0.08);
    box-shadow: 0 10px 28px rgba(15, 23, 42, 0.08);
    padding: 18px 0;
    margin-bottom: 32px;
">
    <div style="
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 24px;
    ">
        <div style="display: flex; align-items: center; gap: 10px;">
            <span style="
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 38px;
                height: 38px;
                border-radius: 12px;
                background: rgba(18, 98, 180, 0.12);
                color: #1262b4;
                font-weight: 700;
                font-size: 18px;
            ">
                CH
            </span>
            <div style="display: flex; flex-direction: column; line-height: 1.2;">
                <strong style="color: #0f172a; font-size: 18px;">Panel Admin</strong>
                <span style="color: #6b7280; font-size: 13px;">Gestión interna del sistema</span>
            </div>
        </div>

        <div style="
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        ">
            <a href="{{ route('admin.index') }}" style="
                text-decoration: none;
                color: {{ request()->routeIs('admin.index') ? '#1262b4' : '#334155' }};
                font-weight: {{ request()->routeIs('admin.index') ? '700' : '600' }};
                padding: 8px 14px;
                border-radius: 10px;
                background: {{ request()->routeIs('admin.index') ? 'rgba(18, 98, 180, 0.12)' : 'transparent' }};
                transition: background 0.2s ease;
            ">Dashboard</a>
            <a href="{{ route('admin.clients') }}" style="
                text-decoration: none;
                color: {{ request()->routeIs('admin.clients') || request()->routeIs('admin.client*') ? '#1262b4' : '#334155' }};
                font-weight: {{ request()->routeIs('admin.clients') || request()->routeIs('admin.client*') ? '700' : '600' }};
                padding: 8px 14px;
                border-radius: 10px;
                background: {{ request()->routeIs('admin.clients') || request()->routeIs('admin.client*') ? 'rgba(18, 98, 180, 0.12)' : 'transparent' }};
                transition: background 0.2s ease;
            ">Clientes</a>
            <a href="{{ route('admin.inventory') }}" style="
                text-decoration: none;
                color: {{ request()->routeIs('admin.inventory') ? '#1262b4' : '#334155' }};
                font-weight: {{ request()->routeIs('admin.inventory') ? '700' : '600' }};
                padding: 8px 14px;
                border-radius: 10px;
                background: {{ request()->routeIs('admin.inventory') ? 'rgba(18, 98, 180, 0.12)' : 'transparent' }};
                transition: background 0.2s ease;
            ">Inventario</a>
            <a href="{{ route('admin.invoices') }}" style="
                text-decoration: none;
                color: {{ request()->routeIs('admin.invoices') || request()->routeIs('admin.invoices.*') ? '#1262b4' : '#334155' }};
                font-weight: {{ request()->routeIs('admin.invoices') || request()->routeIs('admin.invoices.*') ? '700' : '600' }};
                padding: 8px 14px;
                border-radius: 10px;
                background: {{ request()->routeIs('admin.invoices') || request()->routeIs('admin.invoices.*') ? 'rgba(18, 98, 180, 0.12)' : 'transparent' }};
                transition: background 0.2s ease;
            ">Facturas</a>
        </div>

        <form action="{{ route('logout') }}" method="POST" style="margin-left: auto;">
            @csrf
            <button type="submit" style="
                border: none;
                padding: 8px 16px;
                border-radius: 10px;
                background: #ef4444;
                color: white;
                font-weight: 600;
                cursor: pointer;
                transition: transform 0.2s ease;
            " onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                Cerrar Sesión
            </button>
        </form>
    </div>
</nav>

