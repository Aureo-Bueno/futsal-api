SELECT 'CREATE DATABASE bff_db'
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'bff_db')\gexec

SELECT 'CREATE DATABASE team_db'
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'team_db')\gexec

SELECT 'CREATE DATABASE match_db'
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'match_db')\gexec

SELECT 'CREATE DATABASE standings_db'
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'standings_db')\gexec

SELECT 'CREATE DATABASE futsal_test_db'
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'futsal_test_db')\gexec
