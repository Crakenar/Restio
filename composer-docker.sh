#!/bin/bash
# Run Composer commands through Docker instead of local PHP

# Make sure containers are running
docker compose up -d app redis

# Run composer command inside the container
docker compose exec app composer "$@"
