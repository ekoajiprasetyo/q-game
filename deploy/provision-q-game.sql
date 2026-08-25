-- Run once on the VPS as the PostgreSQL superuser before artisan migrate.
-- This keeps Q-Game's migration repository separate from core.migrations.
CREATE SCHEMA IF NOT EXISTS q_game AUTHORIZATION qlink_user;

CREATE TABLE IF NOT EXISTS q_game.migrations (
    id BIGSERIAL PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    batch INTEGER NOT NULL
);

ALTER TABLE q_game.migrations OWNER TO qlink_user;
