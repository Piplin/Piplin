<ul class="nav nav-tabs">
    <li {!! set_active('admin') !!}><a href="/admin"><i class="ion ion-ios-home"></i> {{ trans('admin.home') }}</a></li>
    <li {!! set_active(['admin/projects', 'admin/groups', 'admin/templates*', 'admin/keys']) !!}><a href="/admin/projects"><i class="ion ion-cube"></i> {{ trans('projects.manage') }}</a></li>
    <li {!! set_active(['admin/users', 'admin/providers']) !!}><a href="/admin/users"><i class="ion ion-ios-people"></i> {{ trans('users.manage') }}</a></li>
    <li {!! set_active(['admin/links', 'admin/tips']) !!}><a href="/admin/links"><i class="ion ion-link"></i> {{ trans('admin.misc') }}</a></li>
    <li {!! set_active('admin/revisions') !!}><a href="/admin/revisions"><i class="ion ion-ios-pulse"></i> {{ trans('revisions.manage') }}</a></li>
</ul>