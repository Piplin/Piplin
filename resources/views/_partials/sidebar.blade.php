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
            <li {!! set_active('tasks') !!}>
                <a href="{{ route('dashboard.tasks') }}"><i class="piplin piplin-clock"></i>
                    <strong>{{ trans('dashboard.tasks') }}</strong>
                </a>
                <label class="todo_count badge bg-gray"><i class="piplin piplin-bell"></i> <span></span></label>
            </li>
            @if(!$in_admin)
            <li class="small">
                <a href="#" data-toggle="modal" data-target="#project_create"><i class="piplin piplin-plus"></i>
                    <strong>{{ trans('projects.create') }}</strong>
                </a>
            </li>
            @endif
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