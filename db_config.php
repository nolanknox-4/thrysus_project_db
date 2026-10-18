<?php
// database login comes from environment variables (set in .env, passed in by docker-compose.yml),
// so real credentials never live in the code. the defaults are the local development setup.
try {
    $db_connect = mysqli_connect(
        getenv('DB_HOST') ?: 'db',
        getenv('DB_USER') ?: 'root',
        getenv('DB_PASSWORD') ?: 'root',
        getenv('DB_NAME') ?: 'thrysus_project_db'
    );
} catch (mysqli_sql_exception $e) {
    // the real reason goes to the server log, not to visitors
    error_log("DB connection failed: " . $e->getMessage());
    http_response_code(503);
    die("The archive is temporarily unavailable.");
}
