# cd /var/lib/docker/volume/[CONTAINERNAME]/_data/config
# subl config.php
# sudo docker exec -it nextcloud_redis redis-cli -a mysecurepassword monitor

<?php
$CONFIG = array (
  'htaccess.RewriteBase' => '/',
  'memcache.local' => '\\OC\\Memcache\\APCu',
  'apps_paths' => 
  array (
    0 => 
    array (
      'path' => '/var/www/html/apps',
      'url' => '/apps',
      'writable' => false,
    ),
    1 => 
    array (
      'path' => '/var/www/html/custom_apps',
      'url' => '/custom_apps',
      'writable' => true,
    ),
  ),
  'upgrade.disable-web' => true,
  'instanceid' => 'ocqexr8m3lhi',
  'passwordsalt' => 'DWroQl/FKNtLZEhBUqjZPaQGOM/CYb',
  'secret' => 'O/H0ZUkqNtiI1TqvbJnwL1vkJTmzosJxDH4Hv8bhXty98v4q',
  'trusted_domains' => 
  array (
    0 => 'nextcloud.rhinoman.me',
  ),
  'datadirectory' => '/var/www/html/data',
  'dbtype' => 'mysql',
  'version' => '31.0.8.1',
  'overwrite.cli.url' => 'http://nextcloud.rhinoman.me',
  'dbname' => 'nextcloud',
  'dbhost' => 'db',
  'dbport' => '',
  'dbtableprefix' => 'oc_',
  'mysql.utf8mb4' => true,
  'dbuser' => 'nextcloud',
  'dbpassword' => 'password',
  'installed' => true,
  'app_install_overwrite' => 
  array (
  ),
  'loglevel' => 2,
  'filelocking.enabled' => true,
  'memcache.local' => '\\OC\\Memcache\\Redis',
  'memcache.locking' => '\\OC\\Memcache\\Redis',
  'redis' => [
        'host' => 'redis',
        'port' => 6379,
        'password' => 'password', // Same as in docker-compose.yml
        'timeout' => 1.5,
        ],
);
