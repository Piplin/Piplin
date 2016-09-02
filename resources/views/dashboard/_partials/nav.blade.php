<header class="main-header">
    <nav class="navbar navbar-static-top" role="navigation">
        <div class="container">
        <a href="/" class="navbar-brand"><img src="/img/logo.svg" alt="{{ trans('app.name') }}">{{ trans('app.name') }}</a>
        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
                <!--
                <li class="dropdown messages-menu" id="issues_menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="ion ion-ios-information-outline"></i>
                        <span class="label label-success">{{ $author_issues_count + $assignee_issues_count }}</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="header"><i class="ion ion-arrow-up-c"></i> {{ trans_choice('dashboard.author_issues', $author_issues_count, ['count' => $author_issues_count]) }}</li>
                        <li>
                            <ul class="menu">
                                @foreach($author_issues as $issue)
                                <li><a href="#">{{ $issue->title }}</a></li>
                                @endforeach
                            </ul>
                        </li>
                        <li class="header"><i class="ion ion-arrow-down-c"></i> {{ trans_choice('dashboard.assignee_issues', $assignee_issues_count, ['count' => $assignee_issues_count]) }}</li>
                        <li>
                            <ul class="menu">
                                @foreach($assignee_issues as $issue)
                                <li><a href="#">{{ $issue->title }}</a></li>
                                @endforeach
                            </ul>
                        </li>
                    </ul>
                </li>
                -->
                <li class="dropdown messages-menu" id="pending_menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="ion ion-clock"></i>
                        <span class="label label-info">{{ $pending_count }}</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="header">{{ trans_choice('dashboard.pending', $pending_count, ['count' => $pending_count]) }}</li>
                        <li>
                            <ul class="menu">
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
                    </ul>
                </li>

                <li class="dropdown messages-menu" id="deploying_menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="ion ion-load-c @if($deploying_count) fixhub-spin @endif"></i>
                        <span class="label label-warning">{{ $deploying_count }}</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="header">{{ trans_choice('dashboard.running', $deploying_count, ['count' => $deploying_count]) }}</li>
                        <li>
                            <ul class="menu">
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
                    </ul>
                </li>
                <li class="dropdown user user-menu">
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
