# Q-Game VPS deployment

Q-Game uses `qlink_prod` with the PostgreSQL search path `q_game,core,public`.
Run `provision-q-game.sql` as the PostgreSQL superuser before the first Laravel
migration. This creates the application schema and an isolated
`q_game.migrations` repository.

Copy `.env.production` to `/var/www/q-game/.env`, set `DB_PASSWORD`, and copy
the production `APP_KEY` from Q-Link without committing it. Do not run
`db:seed` in production: the application intentionally starts empty.

Install `nginx-q-game.conf` and `php-fpm-q-game.conf`, obtain the TLS
certificate with Certbot, then run `php artisan migrate --force`,
`php artisan route:cache`, and `php artisan view:cache`. Do not run
`php artisan config:cache` or `php artisan optimize` until the Q-Link shared
`APP_KEY` source is corrected: config caching currently removes that key from
Q-Game's runtime configuration.
