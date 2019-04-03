git fetch origin
git pull
rm -rf var/cache/* var/di/* var/generation/* var/page_cache/* var/tmp/* var/view_preprocessed/* var/composer_home/*
rm -r pub/static/frontend/* pub/static/_requirejs/*
php bin/magento cache:clean
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
