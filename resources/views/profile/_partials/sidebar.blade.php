<div class="box box-default">
    <div class="box-body">
        <ul class="nav nav-pills nav-stacked">
            <li {!! set_active(['profile/basic', 'profile']) !!}>
                <a href="/profile/basic"><i class="fixhub fixhub-user"></i> Basic</a>
            </li>
            <li {!! set_active('profile/settings') !!}>
                <a href="/profile/settings"><i class="fixhub fixhub-admin"></i> Settings</a>
            </li>
            <li {!! set_active('profile/avatar') !!}>
                <a href="/profile/avatar"><i class="fixhub fixhub-group"></i> Avatar</a>
            </li>
            <li {!! set_active('profile/email') !!}>
                <a href="/profile/email"><i class="fixhub fixhub-email"></i> Email</a>
            </li>
            <li {!! set_active('profile/2fa') !!}>
                <a href="/profile/2fa"><i class="fixhub fixhub-lock"></i> 2FA</a>
            </li>
        </ul>
    </div>
</div>