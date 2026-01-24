#!/bin/bash
# Development through Docker - no local PHP extensions needed

echo "🐳 Starting development environment with Docker..."
echo ""

# Make sure all containers are running
docker compose up -d

echo "✅ Docker containers started"
echo ""
echo "Starting development services..."
echo ""

# Run npm dev in background on host (for Vite hot reload)
npm run dev &
NPM_PID=$!

# Function to cleanup on exit
cleanup() {
    echo ""
    echo "🛑 Stopping services..."
    kill $NPM_PID 2>/dev/null
    docker compose exec -T app pkill -f "artisan serve" 2>/dev/null
    docker compose exec -T app pkill -f "queue:listen" 2>/dev/null
    docker compose exec -T app pkill -f "artisan pail" 2>/dev/null
    echo "✅ Stopped"
    exit 0
}

trap cleanup SIGINT SIGTERM

# Start PHP services inside Docker container
docker compose exec -T app bash -c '
    php artisan serve --host=0.0.0.0 --port=8000 > /dev/null 2>&1 &
    php artisan queue:listen --tries=1 > /dev/null 2>&1 &
    php artisan pail --timeout=0 &
' &

echo "✅ All services started!"
echo ""
echo "📍 Laravel: http://localhost:8000"
echo "📍 Vite: http://localhost:5173"
echo ""
echo "Press Ctrl+C to stop all services"
echo ""

# Wait for user to press Ctrl+C
wait
