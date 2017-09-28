<div class="nav-tabs-custom">
    <ul class="nav nav-tabs">
        <li {!! set_active('admin') !!}><a href="/admin">{{ trans('admin.home') }}</a></li>
        <li {!! set_active('admin/projects') !!}><a href="/admin/projects">{{ trans('projects.manage') }}</a></li>
        <li {!! set_active('admin/groups') !!}><a href="/admin/groups">{{ trans('groups.manage') }}</a></li>
        <li {!! set_active('admin/templates') !!}><a href="/admin/templates">{{ trans('templates.manage') }}</a></li>
        <li {!! set_active('admin/users') !!}><a href="/admin/users">{{ trans('users.manage') }}</a></li>
        <li {!! set_active('admin/keys') !!}><a href="/admin/keys">{{ trans('keys.manage') }}</a></li>
        <li {!! set_active('admin/links') !!}><a href="/admin/links">{{ trans('links.manage') }}</a></li>
        <li {!! set_active('admin/tips') !!}><a href="/admin/tips">{{ trans('tips.manage') }}</a></li>
    </ul>
</div>