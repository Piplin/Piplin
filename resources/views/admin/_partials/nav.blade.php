<div class="nav-tabs-custom">
    <ul class="nav nav-tabs">
        <li {!! set_active('admin') !!}><a href="/admin">{{ trans('admin.home') }}</a></li>
        <li {!! set_active('admin/projects') !!}><a href="/admin/projects">{{ trans('projects.manage') }}</a></li>
        <li {!! set_active('admin/users') !!}><a href="/admin/users">{{ trans('users.manage') }}</a></li>
        <li {!! set_active('admin/links') !!}><a href="/admin/links">{{ trans('admin.misc') }}</a></li>
        <li {!! set_active('admin/revisions') !!}><a href="/admin/revisions">{{ trans('revisions.manage') }}</a></li>
    </ul>
</div>