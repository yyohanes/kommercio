@servers(['web' => $user.'@'.$host,'localhost' => '127.0.0.1'])

@setup
    // Sanity checks
    if (empty($host)) {
        exit('ERROR: $host var empty or not defined');
    }
    if (empty($edition)) {
        exit('ERROR: $edition var empty or not defined');
    }
    if (empty($user)) {
        exit('ERROR: $user var empty or not defined');
    }
    if (empty($path)) {
        exit('ERROR: $path var empty or not defined');
    }
    if (empty($build)) {
        exit('ERROR: $build var empty or not defined');
    }
    if (empty($commit)) {
        exit('ERROR: $commit var empty or not defined');
    }
    if (empty($env)) {
        exit('ERROR: $env var empty or not defined');
    }

    if (file_exists($path) || is_writable($path)) {
        exit("ERROR: cannot access $path");
    }

    // Ensure given $path is a potential web directory (/home/* or /var/www/*)
    if (!(preg_match("/(\/home\/|\/var\/www\/)/i", $path) === 1)) {
        exit('ERROR: $path provided doesn\'t look like a web directory path?');
    }

    $current_release_dir = $path . '/current';
    $releases_dir = $path . '/releases';
    $new_release_dir = $releases_dir . '/' . $build . '_' . $commit;

    $remote = $user . '@' . $host . ':' . $new_release_dir;

    // Command or path to invoke PHP
    $php = empty($php) ? 'php' : $php;
@endsetup

@story('deploy')
    warm_up_target
    rsync
    manifest_file
    setup_symlinks
    activate_release
    start_docker_compose
    optimise
    cleanup
    migrate
@endstory

@task('warm_up_target', ['on' => 'web'])
    echo "* Creating release folder on target *"
    mkdir -p {{ $new_release_dir }}
@endtask

@task('debug', ['on' => 'localhost'])
    ls -la {{ $dir }}
@endtask

@task('rsync', ['on' => 'localhost'])
    echo "* Deploying code from {{ $dir }} to {{ $remote }} *"
    # https://explainshell.com/explain?cmd=rsync+-zrSlh+--exclude-from%3Ddeployment-exclude-list.txt+.%2F.+%7B%7B+%24remote+%7D%7D
    rsync -zrSlh --stats --exclude-from=deployment-exclude-list.txt {{ $dir }}/ {{ $remote }}
@endtask

@task('manifest_file', ['on' => 'web'])
    echo "* Writing deploy manifest file *"
    echo -e "{\"build\":\""{{ $build }}"\", \"commit\":\""{{ $commit }}"\", \"branch\":\""{{ $branch }}"\"}" > {{ $new_release_dir }}/deploy-manifest.json
@endtask

@task('setup_symlinks', ['on' => 'web'])
    echo "* Linking .env file to new release dir ({{ $path }}/envs/{{ $edition }}.{{ $env }}.txt -> {{ $new_release_dir }}/.env) *"
    ln -nfs {{ $path }}/envs/{{ $edition }}.{{ $env }}.txt {{ $new_release_dir }}/.env

    if [ ! -d {{ $path }}/storage ]; then
        echo "* Creating storage dir *"
        mkdir -p {{ $path }}/storage
    fi

    echo "* Linking storage directory to new release dir ({{ $path }}/storage -> {{ $new_release_dir }}/storage) *"
    ln -nfs {{ $path }}/storage {{ $new_release_dir }}/storage
@endtask

@task('activate_release', ['on' => 'web'])
    echo "* Activating new release ({{ $new_release_dir }} -> {{ $current_release_dir }}) *"
    ln -nfs {{ $new_release_dir }} {{ $current_release_dir }}
@endtask

@task('optimise', ['on' => 'web'])
    echo '* Clearing cache and optimising *'
    cd {{ $path }}

    {{ $php }} artisan cache:clear
    {{ $php }} artisan config:clear
    {{ $php }} artisan route:clear
    {{ $php }} artisan view:clear

    # https://laravel.com/docs/5.5/deployment#optimization
    {{ $php }} artisan config:cache
    # Only use when no closure used in routes
    #{{ $php }} artisan route:cache

    #echo '* Reloading php-fpm *'
    #sudo -S service php7.1-fpm reload

    #echo '* Gracefully terminating Laravel Horizon *'
    #{{ $php }} artisan horizon:terminate
    #sudo supervisorctl stop horizon # workaround
    #sudo supervisorctl start horizon # workaround
@endtask

@task('cleanup', ['on' => 'web'])
    echo "* Executing cleanup command in {{ $releases_dir }} *"
    ls -dt {{ $releases_dir }}/*/ | tail -n +2 | xargs rm -rf
@endtask

@task('start_docker_compose', ['on' => 'web'])
    echo "* Starting docker-compose *"
    cd {{ $path }}
    docker-compose up -d
@endtask

@task('migrate', ['on' => 'web'])
    echo '* Running migrations *'
    cd {{ $path }}
    {{ $php }} artisan migrate --force
@endtask
