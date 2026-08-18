Development
===========

Docker Test Environment
-----------------------

This repository includes a minimal Docker Compose setup for smoke testing the
extension against MediaWiki 1.43 with MariaDB:

    docker compose up -d

Open <http://localhost:8080> and test:

    {{#timeconvert:2014-01-01 13:00 GMT|America/New_York}}

To stop the test wiki:

    docker compose down

To recreate the database from scratch:

    docker compose down -v
    docker compose up -d

The test wiki creates an `Admin` user with password
`TimeConvertAdminPass123`.
