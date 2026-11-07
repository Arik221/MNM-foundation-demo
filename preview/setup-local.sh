#!/usr/bin/env bash
# Local preview without Docker: PHP built-in server + MariaDB/MySQL.
# Needs: php (with mysqli), mariadb/mysql server, and an unpacked WordPress at $WP.
# Usage: WP=/path/to/wordpress bash preview/setup-local.sh
set -euo pipefail
WP="${WP:?set WP=/path/to/wordpress}"; HERE="$(cd "$(dirname "$0")/.." && pwd)"
mysql -uroot -e "CREATE DATABASE IF NOT EXISTS mnm_wp CHARACTER SET utf8mb4; CREATE USER IF NOT EXISTS 'mnm'@'localhost' IDENTIFIED BY 'mnm_pass'; GRANT ALL ON mnm_wp.* TO 'mnm'@'localhost';"
[ -f "$WP/wp-config.php" ] || { cp "$WP/wp-config-sample.php" "$WP/wp-config.php"; sed -i "s/database_name_here/mnm_wp/;s/username_here/mnm/;s/password_here/mnm_pass/" "$WP/wp-config.php"
  sed -i "s#<?php#<?php define('WP_HOME','http://127.0.0.1:8080'); define('WP_SITEURL','http://127.0.0.1:8080');#" "$WP/wp-config.php"; }
ln -sfn "$HERE/mnm-foundation-theme" "$WP/wp-content/themes/mnm-foundation-theme"
ln -sfn "$HERE/mnm-foundation-core"  "$WP/wp-content/plugins/mnm-foundation-core"
php "$HERE/preview/install-wp.php" "$WP"      # creates an admin user and prints its generated password (or set MNM_ADMIN_PASS)
php "$HERE/preview/seed.php" "$WP"            # activates theme + plugin, loads demo content
echo "Open http://127.0.0.1:8080  (admin password: see the line printed above)"
exec php -S 127.0.0.1:8080 -t "$WP" "$HERE/preview/router.php"
