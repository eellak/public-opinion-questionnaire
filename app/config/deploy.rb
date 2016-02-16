set :application, "poq.ellak"
set :domain,      "#{application}.gr"
set :deploy_to,   "/var/www/vhosts/www-poq/sites/poq.ellak.gr/capifony"
set :app_path,    "app"
set :user,        "dnikoudis"
ssh_options[:port] = "2022"
set :use_composer, true
set :use_sudo, true
default_run_options[:pty] = true

set :writable_dirs,       ["app/cache", "app/logs"]
set :webserver_user,      "www-data"
set :permission_method,   :acl
set :use_set_permissions, true

set :repository,  "https://github.com/eellak/public-opinion-questionnaire.git"
set :scm,         :git
# Or: `accurev`, `bzr`, `cvs`, `darcs`, `subversion`, `mercurial`, `perforce`, or `none`

set :model_manager, "doctrine"
# Or: `propel`

role :web,        domain                         # Your HTTP server, Apache/etc
role :app,        domain, :primary => true       # This may be the same as your `Web` server

set  :keep_releases,  3

# Hooks
after  "symfony:assetic:dump", "symfony:doctrine:schema:update" # Update doctrine schema

# Be more verbose by uncommenting the following line
logger.level = Logger::MAX_LEVEL