<header class="main-header">
    <nav class="navbar navbar-static-top" role="navigation">
        <div class="container">
        <a href="/" class="navbar-brand">{{ $app_name }}</a>
        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
                @if($current_user->is_admin)
                <li {!! set_active('admin*') !!}>
                    <a href="/admin">
                        <i class="fixhub fixhub-admin"></i>
                        <span class="hidden-xs">{{ trans('admin.label') }}</span>
                    </a>
                </li>
                @endif
                <li class="dropdown messages-menu" id="todo_menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fixhub fixhub-bell {{$todo_count ? 'text-danger' : null}}""></i>
                        <span class="label {{$todo_count ? 'label-success' : null}}">{{ $todo_count ?:null }}</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="header deploying_header"><i class="fixhub fixhub-load"></i> <span>{{ trans_choice('dashboard.running', $deploying_count, ['count' => $deploying_count]) }}</span></li>
                        <li>
                            <ul class="menu deploying_menu">
                                @forelse ($deploying as $deployment)
                                    <li class="todo_item" id="deployment_info_{{ $deployment->id }}">
                                        <a href="{{ route('deployments', ['id' => $deployment->id]) }}">
                                            <h4>{{ $deployment->project->name }} <small class="pull-right">{{ trans('dashboard.started') }}: {{ $deployment->started_at->format('g:i:s A') }}</small></h4>
                                            <p>{{ trans('deployments.branch') }}: {{ $deployment->branch }}</p>
                                        </a>
                                    </li>
                                @empty
                                    <li class="item_empty">
                                        <a href="javascript:void(0);">
                                        <small>{{ trans('dashboard.running_empty') }}</small>
                                        </a>
                                    </li>
                                @endforelse
                            </ul>
                        </li>
                        <li class="header pending_header"><i class="fixhub fixhub-clock"></i> <span>{{ trans_choice('dashboard.pending', $pending_count, ['count' => $pending_count]) }}</span></li>
                        <li>
                            <ul class="menu pending_menu">
                                @forelse ($pending as $deployment)
                                    <li class="todo_item" id="deployment_info_{{ $deployment->id }}">
                                        <a href="{{ route('deployments', ['id' => $deployment->id]) }}">
                                            <h4>{{ $deployment->project->name }} <small class="pull-right">{{ trans('dashboard.started') }}: {{ $deployment->started_at->format('g:i:s A') }}</small></h4>
                                            <p>{{ trans('deployments.branch') }}: {{ $deployment->branch }}</p>
                                        </a>
                                    </li>
                                @empty
                                    <li class="item_empty">
                                        <a href="javascript:void(0);">
                                        <small>{{ trans('dashboard.pending_empty') }}</small>
                                        </a>
                                    </li>
                                @endforelse
                            </ul>
                        </li>
                        <li class="footer"><a href="javascript:void(0);">{{ trans('app.close') }}</a></li>
                    </ul>
                </li>
                <li {!! set_active('profile', ['dropdown', 'user-menu']) !!}>
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <img src="{{ $current_user->avatar_url }}" class="user-image" />
                        <b class="caret"></b>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="dropdown-header">{{ trans('users.login_as', ['name' => $current_user->name]) }}</li>
                        <li class="divider"></li>
                        <li><a href="{{ route('profile') }}"><i class="fixhub fixhub-user"></i> {{ trans('users.profile') }}</a></li>
                        <li>
                        @if($dashboard == 'projects')
                        <a href="{{ route('dashboard.deployments') }}"><i class="fixhub fixhub-clock"></i> {{ trans('users.dashboard.deployments') }}</a>
                        @else
                        <a href="{{ route('dashboard.projects') }}"><i class="fixhub fixhub-project"></i> {{ trans('users.dashboard.projects') }}</a>
                        @endif
                        </li>
                        <li class="divider"></li>
                        <li><a href="{{ route('auth.logout') }}"><i class="fixhub fixhub-logout"></i> {{ trans('app.signout') }}</a></li>
                    </ul>
                </li>
            </ul>
        </div>
        </div>
    </nav>
</header>

@push('templates')
    <script type="text/template" id="deployment-list-template">
        <li class="todo_item" id="deployment_info_<%- id %>">
            <a href="<%- url %>">
                <h4><%- project_name %> <small class="pull-right">{{ trans('dashboard.started') }}: <%- time %></small></h4>
                <p>{{ trans('deployments.branch') }}: <%- branch %></p>
            </a>
        </li>
    </script>
    <script type="text/template" id="todo-item-empty-template">
        <li class="item_empty">
            <a href="javascript:void(0);">
                <small><%- empty_text %></small>
            </a>
        </li>
    </script>
@endpush
