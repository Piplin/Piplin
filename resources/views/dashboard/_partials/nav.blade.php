<header class="main-header">
    <nav class="navbar navbar-static-top" role="navigation">
        <div class="container">
        <a href="/" class="navbar-brand"><img src="/img/logo.svg" alt="{{ $app_name }}">{{ $app_name }}</a>
        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">

                @if($current_user->is_admin)
                <li {!! set_active('admin*') !!}>
                    <a href="/admin">
                        <i class="ion ion-wrench"></i>
                        <span class="hidden-xs">{{ trans('admin.label') }}</span>
                    </a>
                </li>
                @endif
                <li class="dropdown messages-menu" id="todo_menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="ion ion-android-notifications {{$todo_count ? 'text-danger' : null}}""></i>
                        <span class="label {{$todo_count ? 'label-info' : null}}">{{ $todo_count ?:null }}</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="header deploying_header"><i class="ion ion-load-c"></i> <span>{{ trans_choice('dashboard.running', $deploying_count, ['count' => $deploying_count]) }}</span></li>
                        <li>
                            <ul class="menu deploying_menu">
                                @foreach ($deploying as $deployment)
                                    <li id="deployment_info_{{ $deployment->id }}">
                                        <a href="{{ route('deployments', ['id' => $deployment->id]) }}">
                                            <h4>{{ $deployment->project->name }} <small class="pull-right">{{ trans('dashboard.started') }}: {{ $deployment->started_at->format('g:i:s A') }}</small></h4>
                                            <p>{{ trans('deployments.branch') }}: {{ $deployment->branch }}</p>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                        <li class="header pending_header"><i class="ion ion-clock"></i> <span>{{ trans_choice('dashboard.pending', $pending_count, ['count' => $pending_count]) }}</span></li>
                        <li>
                            <ul class="menu pending_menu">
                                @foreach ($pending as $deployment)
                                    <li id="deployment_info_{{ $deployment->id }}">
                                        <a href="{{ route('deployments', ['id' => $deployment->id]) }}">
                                            <h4>{{ $deployment->project->name }} <small class="pull-right">{{ trans('dashboard.started') }}: {{ $deployment->started_at->format('g:i:s A') }}</small></h4>
                                            <p>{{ trans('deployments.branch') }}: {{ $deployment->branch }}</p>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                        <li class="footer"><a href="javascript:void(0);">Close</a></li>
                    </ul>
                </li>
                <li {!! set_active('profile', ['dropdown', 'user', 'user-menu']) !!}>
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <img src="{{ $current_user->avatar_url }}" class="user-image" />
                        <span class="hidden-xs">{{ $current_user->name }}</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="user-header">
                            <img src="{{ $current_user->avatar_url }}" class="img-circle" />
                            <p>{{ $current_user->name }} - {{ $current_user->nickname }}</p>
                            <p>{{ $current_user->role_name }}
                        </li>
                        <li class="user-footer">
                            <div class="pull-left">
                                <a href="{{ route('profile.index') }}" class="btn btn-default btn-flat">{{ trans('users.profile') }}</a>
                            </div>
                            <div class="pull-right">
                                <a href="{{ route('auth.logout') }}" class="btn btn-default btn-flat">{{ trans('app.signout') }}</a>
                            </div>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
        </div>
    </nav>
</header>

@push('templates')
    <script type="text/template" id="deployment-list-template">
        <li id="deployment_info_<%- id %>">
            <a href="<%- url %>">
                <h4><%- project_name %> <small class="pull-right">{{ trans('dashboard.started') }}: <%- time %></small></h4>
                <p>{{ trans('deployments.branch') }}: <%- branch %></p>
            </a>
        </li>
    </script>
@endpush
