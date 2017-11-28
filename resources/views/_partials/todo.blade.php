<div class="modal fade" id="todo" tabindex="-1" role="dialog" aria-hidden="true"> 
    <div class="modal-dialog"> 
        <div class="modal-content"> 
            <div class="modal-header"> 
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button> 
                <h4 class="modal-title"><i class="piplin piplin-bell"></i> <span>{{ trans('dashboard.notifications') }}</span></h4> 
            </div> 
            <div class="modal-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                          <th width="5%">#</th>
                          <th>{{ trans('projects.name') }}</th>
                          <th>{{ trans('tasks.branch') }}</th>
                          <th width="20%">{{ trans('tasks.started') }}</th>
                        </tr>
                    </thead>
                    <tbody class="running_menu">
                    @forelse ($running as $task)
                    <tr class="item" id="task_info_{{ $task->id }}">
                        <td><a href="{{ route('tasks.show', ['id' => $task->id]) }}">{{ $task->id }}</a></td>
                        <td>{{ $task->project->name }}</td>
                        <td>{{ $task->branch }}</td>
                        <td>{{ $task->started_at->format('g:i:s A') }}</td>
                    </tr>
                    @empty
                    <tr class="item_empty">
                        <td colspan="4" class="text-center">{{ trans('dashboard.running_empty') }}</td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div> 
            <div class="modal-footer"> 
                <div class="btn-group">
                     <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('app.close') }}</button>
                </div>
            </div> 
        </div><!-- /.modal-content --> 
    </div><!-- /.modal-dialog --> 
</div>
@push('templates')
    <script type="text/template" id="task-list-template">
        <tr class="item" id="task_info_<%- id %>">
            <td><a href="/task/<%- id %>"><%- id %></a></td>
            <td><%- project_name %> </td>
            <td><%- branch %></td>
            <td><%- time %></td>
        </tr>
    </script>
@endpush