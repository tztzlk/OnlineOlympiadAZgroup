-- Создание базы и роли приложения Online Olympiad в PostgreSQL.
-- Запускается от суперпользователя postgres:
--   psql -U postgres -h 127.0.0.1 -v app_password="'<пароль>'" -f database/postgres/setup.sql
-- Проще всего — через scripts/setup-postgres.ps1: он сам сгенерирует пароль и запишет его в .env.
--
-- Принципы:
--   * приложение работает под отдельной ролью без прав суперпользователя;
--   * к базе не может подключиться никто, кроме этой роли и администраторов;
--   * пароли хранятся в scram-sha-256;
--   * зависшие запросы и транзакции обрываются по таймауту.

\set ON_ERROR_STOP on

SET password_encryption = 'scram-sha-256';

SELECT 'CREATE ROLE olympiad_app'
WHERE NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'olympiad_app')
\gexec

ALTER ROLE olympiad_app WITH
    LOGIN
    NOSUPERUSER
    NOCREATEDB
    NOCREATEROLE
    NOREPLICATION
    NOBYPASSRLS
    CONNECTION LIMIT 50
    PASSWORD :app_password;

ALTER ROLE olympiad_app SET statement_timeout = '30s';
ALTER ROLE olympiad_app SET idle_in_transaction_session_timeout = '60s';
ALTER ROLE olympiad_app SET client_encoding = 'UTF8';
ALTER ROLE olympiad_app SET timezone = 'UTC';

SELECT 'CREATE DATABASE online_olympiad OWNER olympiad_app ENCODING ''UTF8'' TEMPLATE template0'
WHERE NOT EXISTS (SELECT 1 FROM pg_database WHERE datname = 'online_olympiad')
\gexec

-- Подключаться к базе может только роль приложения (и суперпользователь).
REVOKE ALL ON DATABASE online_olympiad FROM PUBLIC;
GRANT CONNECT, TEMPORARY ON DATABASE online_olympiad TO olympiad_app;

\connect online_olympiad

-- Схема public принадлежит приложению; остальным ролям создавать в ней объекты нельзя.
ALTER SCHEMA public OWNER TO olympiad_app;
REVOKE ALL ON SCHEMA public FROM PUBLIC;
GRANT USAGE, CREATE ON SCHEMA public TO olympiad_app;
