#!/usr/bin/env bash
set -e # Exit immediately if a command exits with a non-zero status.

psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<-EOSQL
    CREATE USER symfony;
    CREATE DATABASE symfony;
    GRANT ALL PRIVILEGES ON DATABASE symfony TO symfony;
EOSQL
