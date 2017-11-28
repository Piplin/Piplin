<aside class="main-sidebar">
    <section class="sidebar">
        <div class="brand">
            <a href="{{ route('dashboard') }}">
                <img src="/img/logo.png" />
                <h4>Piplin</h4>
            </a>
        </div>
        <ul class="sidebar-menu">
            <li {!! set_active('/') !!}>
                <a href="/">
                    <i class="piplin piplin-dashboard"></i>
                    <strong>{{ trans('dashboard.title') }}</strong>
                </a>
            </li>
            <li {!! set_active('activities') !!}>
                <a href="{{ route('dashboard.activities') }}"><i class="piplin piplin-clock"></i>
                    <strong>{{ trans('dashboard.activities') }}</strong>
                </a>
            </li>
            <li class="small">
                <a href="#" data-toggle="modal" data-target="#todo">
                    <i class="piplin piplin-bell"></i>
                    <strong>{{ trans('dashboard.notifications') }}</strong>
                </a>
                <label class="todo_count badge bg-green {{ $todo_count == 0 ? 'hide' : null }}">
                        <span>{{ $todo_count }}</span>
                </label>
            </li>
        </ul>
        <div class="bottom-menu">
            @if($current_user->is_admin)
            <ul class="sidebar-menu">
                <li {!! set_active('admin*') !!}>
                    <a href="/admin">
                        <i class="piplin piplin-setting"></i>
                        <strong>{{ trans('admin.title') }}</strong>
                    </a>
                </li>
            </ul>
            @endif
            <div class="user-menu">
            <a href="{{ route('profile') }}">
                <img src="{{ $current_user->avatar_url }}" />
                <div>{{ $current_user->nickname ?: $current_user->name }}</div>
            </a>
            </div>
            <a href="{{ route('auth.logout') }}" class="small">
                <i class="piplin piplin-logout"></i> {{ trans('app.signout') }}
            </a>
        </div>
    </section>
</aside>