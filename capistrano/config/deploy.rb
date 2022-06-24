# config valid only for current version of Capistrano
lock "3.11.0"

set :application, "cinra.dev"
set :repo_url, "git@github.com:ryooku/wp-gutenberg.git"
set :project_root, "/srv/"

framework_tasks = [:starting, :started, :updating, :updated, :publishing, :published, :finishing, :finished]

framework_tasks.each do |t|
  Rake::Task["deploy:#{t}"].clear
end

Rake::Task[:deploy].clear

task :init do
  run_locally do
    # execute "uptime"
  end
  on roles(:web) do
    execute "cd #{fetch :project_root}; git clone #{fetch :repo_url} #{fetch :application}"
  end
end

task :deploy do
  on roles(:web) do
    branch = capture "cd #{fetch :project_root}#{fetch :application}; git symbolic-ref --short HEAD"
    execute "cd #{fetch :project_root}#{fetch :application}; git pull origin #{branch}"
    branch = capture "cd #{fetch :project_root}#{fetch :application}/container/wp/html/assets/themes/wp-bootstrap; git symbolic-ref --short HEAD"
    execute "cd #{fetch :project_root}#{fetch :application}/container/wp/html/assets/themes/wp-bootstrap; git pull origin #{branch}"
  end
end


# Default branch is :master
# ask :branch, `git rev-parse --abbrev-ref HEAD`.chomp

# Default deploy_to directory is /var/www/my_app_name
# set :deploy_to, "/var/www/my_app_name"

# Default value for :format is :airbrussh.
# set :format, :airbrussh

# You can configure the Airbrussh format using :format_options.
# These are the defaults.
# set :format_options, command_output: true, log_file: "log/capistrano.log", color: :auto, truncate: :auto

# Default value for :pty is false
# set :pty, true

# Default value for :linked_files is []
# append :linked_files, "config/database.yml", "config/secrets.yml"

# Default value for linked_dirs is []
# append :linked_dirs, "log", "tmp/pids", "tmp/cache", "tmp/sockets", "public/system"

# Default value for default_env is {}
# set :default_env, { path: "/opt/ruby/bin:$PATH" }

# Default value for local_user is ENV['USER']
# set :local_user, -> { `git config user.name`.chomp }

# Default value for keep_releases is 5
# set :keep_releases, 5
