# composer install
composer install 

#convert env.example
cp .env.example .env

# Apply database migrations
php artisan migrate 

# create key generate
php artisan key:generate

# Start server
php artisan serve --host 0.0.0.0

#
sudo chmod -R 777 storage