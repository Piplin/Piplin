@extends('layouts.admin')

@section('admin-content')
    <div class="box">
        <div class="box-body" id="no_projects">
            <p>{{ trans('projects.none') }}</p>
        </div>

        <div class="box-body table-responsive" id="project_list">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>{{ trans('projects.name') }}</th>
                        <th>{{ trans('projects.repository') }}</th>
                        <th>{{ trans('projects.branch') }}</th>
                        <th>{{ trans('projects.deployed') }}</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
            {!! $projects_raw->render() !!}
        </div>
    </div>

    @include('admin.projects.dialog')
@stop

@section('right-buttons')
    <div class="pull-right">
        <button type="button" class="btn btn-success" title="{{ trans('projects.create') }}" data-toggle="modal" data-target="#project"><span class="ion ion-plus-round"></span> {{ trans('projects.create') }}</button>
    </div>
@stop

@push('javascript')
    <script type="text/javascript">
        var projects = {!! $projects !!}

        new app.ProjectsTab();
        app.Projects.add(projects.data);
        @if(isset($action) && $action == 'create')
        $('button.btn.btn-success').trigger('click');
        @endif
    </script>
@endpush

@push('templates')
    <script type="text/template" id="project-template">
        <td><%- id %></td>
        <td><a href="/projects/<%- id %>"><%- group_name %>/<%- name %></a></td>
        <td><%- repository %></td>
        <td><span class="label label-default"><%- branch %></span></td>
        <td>
            <% if (deploy) { %>
                <%- deploy %>
            <% } else { %>
                {{ trans('app.never') }}
            <% } %>
        </td>
        <td>
            <div class="btn-group pull-right">
                <button class="btn btn-default btn-clone" title="{{ trans('projects.clone') }}" data-toggle="modal" data-target="#project-clone" data-project_id="<%- id %>"><i class="ion ion-ios-browsers-outline"></i></button>
                <button class="btn btn-default btn-edit" title="{{ trans('app.edit') }}" data-toggle="modal" data-target="#project"><i class="ion ion-compose"></i></button>
                <button class="btn btn-danger btn-trash" title="{{ trans('app.delete') }}" data-toggle="modal" data-target="#model-trash"><i class="ion ion-trash-a"></i></button>
            </div>
        </td>
    </script>

    <script type="text/template" id="project-sidebar-template">
        <li><a href="/projects/<%- id %>" id="sidebar_project_<%- id %>"><%- name %></a></li>
    </script>
@endpush
