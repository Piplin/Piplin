<div class="row">
    <div class="box-header with-border">
        <h3 class="box-title">{{ trans('admin.dependencies') }}</h3>
    </div>

    <!-- /.box-header -->
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-striped">
                @foreach($dependencies as $dependency => $version)
                <tr>
                    <td width="300px">{{ $dependency }}</td>
                    <td><span class="label label-primary">{{ $version }}</span></td>
                </tr>
                @endforeach
            </table>
        </div>
        <!-- /.table-responsive -->
    </div>
    <!-- /.box-body -->
</div>