<div class="box box-default">
    <div class="box-body">
        <ul class="nav nav-pills nav-stacked">
        <li {!! set_active('admin') !!}><a href="/admin"><i class="fixhub fixhub-home"></i> {{ trans('admin.home') }}</a></li>
        <li {!! set_active(['admin/projects', 'admin/groups']) !!}><a href="/admin/projects"><i class="fixhub fixhub-project"></i> {{ trans('projects.manage') }}</a></li>
        <li {!! set_active(['admin/templates*', 'admin/keys', 'admin/cabinets*']) !!}><a href="/admin/templates"><i class="fixhub fixhub-cabinet"></i> {{ trans('admin.resources') }}</a></li>
        <li {!! set_active(['admin/users', 'admin/providers']) !!}><a href="/admin/users"><i class="fixhub fixhub-users"></i> {{ trans('users.manage') }}</a></li>
        <li {!! set_active(['admin/links', 'admin/tips']) !!}><a href="/admin/links"><i class="fixhub fixhub-addon"></i> {{ trans('admin.misc') }}</a></li>
        <li {!! set_active('admin/revisions') !!}><a href="/admin/revisions"><i class="fixhub fixhub-log"></i> {{ trans('revisions.manage') }}</a></li>
        </ul>
    </div>
</div>