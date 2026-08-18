#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

if [ ! -f LocalSettings.php ]; then
	php maintenance/run.php install \
		--server=http://localhost:8080 \
		--scriptpath= \
		--dbtype=mysql \
		--dbserver=database \
		--dbname=mediawiki \
		--dbuser=mediawiki \
		--dbpass=mediawiki \
		--installdbuser=mediawiki \
		--installdbpass=mediawiki \
		--pass=TimeConvertAdminPass123 \
		"TimeConvert Test" \
		"Admin"

	cat /docker/LocalSettings.extra.php >> LocalSettings.php
fi

php maintenance/run.php update --quick

exec apache2-foreground
