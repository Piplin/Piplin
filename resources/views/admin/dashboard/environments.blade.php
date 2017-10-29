<div class="row">
    <div class="box-header with-border">
        <h3 class="box-title">{{ trans('admin.environments') }}</h3>
    </div>

    <!-- /.box-header -->
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-striped">
                @foreach($envs as $env)
                <tr>
                    <td width="120px">{{ $env['name'] }}</td>
                    <td>{{ $env['value'] }}</td>
                </tr>
                @endforeach
            </table>
        </div>
        <!-- /.table-responsive -->
    </div>
    <!-- /.panel-body -->
</div>