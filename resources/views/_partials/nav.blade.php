<header class="main-header">
    <nav class="navbar navbar-static-top" role="navigation">
        <a href="/" class="navbar-brand">{{ $app_name }}</a>
        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
                <li class="dropdown messages-menu" id="todo_menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="piplin piplin-bell {{$todo_count ? 'text-danger' : null}}""></i>
                        <span class="label {{$todo_count ? 'label-success' : null}}">{{ $todo_count ?:null }}</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="header running_header"><i class="piplin piplin-load"></i> <span>{{ trans_choice('dashboard.running', $running_count, ['count' => $running_count]) }}</span></li>
                        <li>
                            <ul class="menu running_menu">
                                @forelse ($running as $deployment)
                                    <li class="todo_item" id="deployment_info_{{ $deployment->id }}">
                                        <a href="{{ route('tasks.show', ['id' => $deployment->id]) }}">
                                            <h4>{{ $deployment->project->name }} <small class="pull-right">{{ trans('dashboard.started') }}: {{ $deployment->started_at->format('g:i:s A') }}</small></h4>
                                            <p>{{ trans('tasks.branch') }}: {{ $deployment->branch }}</p>
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
                        <li class="header pending_header"><i class="piplin piplin-clock"></i> <span>{{ trans_choice('dashboard.pending', $pending_count, ['count' => $pending_count]) }}</span></li>
                        <li>
                            <ul class="menu pending_menu">
                                @forelse ($pending as $deployment)
                                    <li class="todo_item" id="deployment_info_{{ $deployment->id }}">
                                        <a href="{{ route('tasks.show', ['id' => $deployment->id]) }}">
                                            <h4>{{ $deployment->project->name }} <small class="pull-right">{{ trans('dashboard.started') }}: {{ $deployment->started_at->format('g:i:s A') }}</small></h4>
                                            <p>{{ trans('tasks.branch') }}: {{ $deployment->branch }}</p>
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
            </ul>
        </div>
    </nav>
</header>

@push('templates')
    <script type="text/template" id="deployment-list-template">
        <li class="todo_item" id="deployment_info_<%- id %>">
            <a href="<%- url %>">
                <h4><%- project_name %> <small class="pull-right">{{ trans('dashboard.started') }}: <%- time %></small></h4>
                <p>{{ trans('tasks.branch') }}: <%- branch %></p>
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
