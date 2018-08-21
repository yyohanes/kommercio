@setup
    // Sanity checks
    if (empty($path)) {
        exit('ERROR: $path var empty or not defined');
    }
    if (empty($build)) {
        exit('ERROR: $build var empty or not defined');
    }
    if (empty($commit)) {
        exit('ERROR: $commit var empty or not defined');
    }
    if (!file_exists($dir)) {
        exit("ERROR: cannot access $dir");
    }
    if (empty($env)) {
        exit('ERROR: $env var empty or not defined');
    }

    if (empty($user)) {
        exit('ERROR: $user var empty or not defined');
    }

    $matchPattern = '/^(?:release|staging)_([a-zA-Z]{2})/i';
    if (empty($build_tag) || !preg_match($matchPattern, $build_tag)) {
        exit('ERROR: $build_tag var empty, not defined or in unknown format');
    }

    $match = [];
    preg_match($matchPattern, $build_tag, $match);
    $edition = $match[1];

    $servers = [
        'localhost' => '127.0.0.1',
    ];
    $hosts = include __DIR__ . '/deploy/hosts.php';

    if (!isset($hosts[$edition])) {
        exit("ERROR: $edition hosts cannot be found.");
    }

    if (!isset($hosts[$edition][$env])) {
        exit("ERROR: $edition:$env hosts cannot be found.");
    }

    $remoteServers = $hosts[$edition][$env];
    foreach ($remoteServers as $name => $remoteServer) {
        $remoteServers = array_replace(
            $remoteServers,
            [
                $name => $user . '@' . $remoteServer,
            ]
        );
    }
    $remoteServerNames = array_keys($remoteServers);
    $servers = array_merge($servers, $remoteServers);

    // Ensure given $path is a potential web directory (/home/* or /var/www/*)
    if (!(preg_match("/(\/home\/|\/var\/www)/i", $path) === 1)) {
        exit('ERROR: $path provided doesn\'t look like a web directory path?');
    }

    $current_release_dir = $path . '/current';
    $releases_dir = $path . '/releases';
    $new_release_dir = $releases_dir . '/' . $build . '_' . $commit;

    // Command or path to invoke PHP
    $php = empty($php) ? 'docker-compose exec -T app php' : $php;
@endsetup

@servers($servers)

@story('deploy')
    acknowledge_hosts
    warm_up_target
    rsync
    manifest_file
    setup_symlinks
    activate_release
    start_docker_compose
    optimise
    cleanup
    warm_up_db
    migrate
@endstory

@task('acknowledge_hosts', ['on' => 'localhost'])
    @foreach ($remoteServers as $hostName => $remoteServer)
        @php
            $hostMatch = explode('@', $remoteServer);
            $host = $hostMatch[1];
        @endphp
        echo "* Trusting {{ $host }} as trusted host *"
        ssh-keyscan {{ $host }} >> ~/.ssh/known_hosts
    @endforeach
@endtask

@task('warm_up_target', ['on' => $remoteServerNames])
    echo "* Creating release folder on target *"
    mkdir -p {{ $new_release_dir }}
@endtask

@task('debug', ['on' => 'localhost'])
    ls -la {{ $dir }}
@endtask

@task('rsync', ['on' => 'localhost'])
    @foreach ($remoteServers as $remoteServer)
        @php
        $remote = $remoteServer . ':' . $new_release_dir;
        @endphp

        echo "* Deploying code from {{ $dir }} to {{ $remote }} *"
        # https://explainshell.com/explain?cmd=rsync+-zrSlh+--exclude-from%3Ddeployment-exclude-list.txt+.%2F.+%7B%7B+%24remote+%7D%7D
        rsync -zrSlh --stats --exclude-from=deployment-exclude-list.txt {{ $dir }}/ {{ $remote }}
    @endforeach
@endtask

@task('manifest_file', ['on' => $remoteServerNames])
    echo "* Writing deploy manifest file *"
    echo -e "{\"build\":\""{{ $build }}"\", \"commit\":\""{{ $commit }}"\", \"branch\":\""{{ $branch }}"\"}" > {{ $new_release_dir }}/deploy-manifest.json
@endtask

@task('setup_symlinks', ['on' => $remoteServerNames])
    echo "* Copying .env file to new release dir ({{ $path }}/envs/{{ $edition }}.{{ $env }}.txt -> {{ $new_release_dir }}/.env) *"
    cp {{ $path }}/envs/{{ $edition }}.{{ $env }}.txt {{ $new_release_dir }}/.env

    if [ -d {{ $new_release_dir }}/storage ]; then
        echo "* Backing up storage folder *"
        mv {{ $new_release_dir }}/storage {{ $new_release_dir }}/storage.orig 2>/dev/null
    fi

    if [ ! -d {{ $path }}/storage ]; then
        echo "* Required storage folder doesn't exist. Creating one... *"
        mkdir -p {{ $path }}/storage 2>/dev/null
    fi

    if [ ! -d {{ $path }}/storage/app ]; then
        echo "* Storage folder is empty. Filling with default content *"
        yes | cp -rf {{ $new_release_dir }}/storage.orig/* {{ $path }}/storage
    fi

    # echo "* Linking storage directory to new release dir ({{ $path }}/storage -> {{ $new_release_dir }}/storage) *"
    # ln -nfs {{ $path }}/storage {{ $new_release_dir }}/storage
@endtask

@task('activate_release', ['on' => $remoteServerNames])
    echo "* Activating new release ({{ $new_release_dir }} -> {{ $current_release_dir }}) *"
    ln -nfs {{ $new_release_dir }} {{ $current_release_dir }}
@endtask

@task('optimise', ['on' => $remoteServerNames])
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

@task('cleanup', ['on' => $remoteServerNames])
    echo "* Executing cleanup command in {{ $releases_dir }} *"
    ls -dt {{ $releases_dir }}/*/ | tail -n +4 | xargs rm -rf
@endtask

@task('start_docker_compose', ['on' => $remoteServerNames])
    echo "* Starting docker-compose *"
    cd {{ $path }}
    docker-compose image pull irvinsaltedegg/eggyolk-build
    docker-compose up -d --force-recreate
@endtask

@task('warm_up_db', ['on' => $remoteServerNames])
    echo '* Warm up db *'
    @php
    $dotenv = file_get_contents('.env');
    $rows = explode("\n", $dotenv);

    $dbHost = 'localhost';
    $dbName = 'eggyolk';
    $dbUsername = 'dbuser';
    $dbPassword = 'dbpassword';
    foreach ($rows as $row) {
        if (preg_match('/^DB_HOST/i', $row)) {
            $dbHost = explode('=', $row)[1];
        }

        if (preg_match('/^DB_DATABASE/i', $row)) {
            $dbName = explode('=', $row)[1];
        }

        if (preg_match('/^DB_USERNAME/i', $row)) {
            $dbUsername = explode('=', $row)[1];
        }

        if (preg_match('/^DB_PASSWORD/i', $row)) {
            $dbPassword = explode('=', $row)[1];
        }
    }
    @endphp

    cd {{ $path }}
    docker-compose exec -T app bash -c "/var/helper_scripts/create_db_user.sh {{ $edition }} {{ $dbHost }} {{ $dbUsername }} {{ $dbPassword }}"
@endtask

@task('migrate', ['on' => $remoteServerNames])
    echo '* Running migrations *'
    cd {{ $path }}
    {{ $php }} artisan migrate --force
@endtask
