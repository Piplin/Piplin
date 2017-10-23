<div class="box box-default">
    <div class="box-body">
        <ul class="nav nav-pills nav-stacked">
            <li {!! set_active(['profile/basic', 'profile']) !!}>
                <a href="{{ route('profile', ['tab' => 'basic']) }}"><i class="fixhub fixhub-user"></i> {{ trans('users.basic') }}</a>
            </li>
            <li {!! set_active('profile/settings') !!}>
                <a href="{{ route('profile', ['tab' => 'settings']) }}"><i class="fixhub fixhub-setting"></i> {{ trans('users.settings') }}</a>
            </li>
            <li {!! set_active('profile/avatar') !!}>
                <a href="{{ route('profile', ['tab' => 'avatar']) }}"><i class="fixhub fixhub-image"></i> {{ trans('users.avatar') }}</a>
            </li>
            <li {!! set_active('profile/email') !!}>
                <a href="{{ route('profile', ['tab' => 'email']) }}"><i class="fixhub fixhub-email"></i> {{ trans('users.email') }}</a>
            </li>
            <li {!! set_active('profile/2fa') !!}>
                <a href="{{ route('profile', ['tab' => '2fa']) }}"><i class="fixhub fixhub-lock"></i> {{ trans('users.2fa') }}</a>
            </li>
        </ul>
    </div>
</div>