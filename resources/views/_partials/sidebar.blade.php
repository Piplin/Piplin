<aside class="main-sidebar">
    <section class="sidebar">
        <div class="brand">
            <a href="{{ route('dashboard') }}">
                <img src="/img/logo.png" />
                <h4>Piplin</h4>
            </a>
        </div>
        <ul class="sidebar-menu">
            <!--
            <li {!! set_active('/') !!}>
                <a href="/">
                    <i class="piplin piplin-dashboard"></i>
                    <strong>{{ trans('dashboard.title') }}</strong>
                </a>
            </li>
        -->
            <li {!! set_active('tasks') !!}>
                <a href="{{ route('dashboard.tasks') }}"><i class="piplin piplin-clock"></i>
                    <strong>{{ trans('dashboard.tasks') }}</strong>
                </a>
                <label class="todo_count badge bg-gray"><i class="piplin piplin-bell"></i> <span></span></label>
            </li>

            <li {!! set_active('projects*') !!}>
                <a href="{{ route('dashboard.projects') }}"><i class="piplin piplin-project"></i>
                    <strong>{{ trans('dashboard.projects') }}</strong>
                </a>
            </li>

            @if($current_user->is_admin)
            <li {!! set_active('admin*') !!}>
                <a href="/admin">
                    <i class="piplin piplin-setting"></i>
                    <strong>{{ trans('admin.title') }}</strong>
                </a>
            </li>
            @endif
        </ul>
        <div class="user-menu">
            <div class="profile">
            <a href="{{ route('profile') }}">
                <img src="{{ $current_user->avatar_url }}" />
                <div>{{ $current_user->nickname ?: $current_user->name }}</div>
            </a>
        </div>
            <a href="{{ route('auth.logout') }}" class="dropdown-toggle">
                <i class="piplin piplin-logout"></i> {{ trans('app.signout') }}
            </a>
        </div>
    </section>
</aside>