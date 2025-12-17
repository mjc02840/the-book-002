#!/bin/bash

# test-local.sh
# SSHLD_002 Local Development Automation Script
# Initializes database, installs dependencies, and displays startup instructions

echo "=== SSHLD_002 Local Development Setup ==="
echo ""

PROJECT_DIR="/var/www/html/SSHLD_002"
cd "$PROJECT_DIR" || exit 1

echo "Project directory: $PROJECT_DIR"
echo ""

# Check database
echo "Step 1: Database Initialization"
if [ ! -f "db/sshld_002.db" ]; then
    echo "  → Initializing database..."
    php schema/init_db.php
    if [ $? -eq 0 ]; then
        echo "  ✓ Database initialized successfully"
    else
        echo "  ✗ Database initialization failed"
        exit 1
    fi
else
    echo "  ✓ Database already exists at db/sshld_002.db"
fi
echo ""

# Check agent dependencies
echo "Step 2: Agent Dependencies"
if [ ! -d "agent/node_modules" ]; then
    echo "  → Installing agent dependencies (this may take a minute)..."
    cd agent && npm install > /dev/null 2>&1
    if [ $? -eq 0 ]; then
        echo "  ✓ Agent dependencies installed"
    else
        echo "  ✗ Agent npm install failed"
        cd ..
        exit 1
    fi
    cd ..
else
    echo "  ✓ Agent dependencies ready"
fi
echo ""

# Check frontend dependencies
echo "Step 3: Frontend Dependencies"
if [ ! -d "frontend/node_modules" ]; then
    echo "  → Installing frontend dependencies (this may take a minute)..."
    cd frontend && npm install > /dev/null 2>&1
    if [ $? -eq 0 ]; then
        echo "  ✓ Frontend dependencies installed"
    else
        echo "  ✗ Frontend npm install failed"
        cd ..
        exit 1
    fi
    cd ..
else
    echo "  ✓ Frontend dependencies ready"
fi
echo ""

# Verify setup
echo "Step 4: Verification"
echo "  Checking database tables..."
TABLE_COUNT=$(sqlite3 db/sshld_002.db "SELECT COUNT(name) FROM sqlite_master WHERE type='table';" 2>/dev/null)
if [ "$TABLE_COUNT" -gt 0 ]; then
    echo "  ✓ Database tables verified ($TABLE_COUNT tables)"
else
    echo "  ✗ Database tables not found"
    exit 1
fi
echo ""

# Check ports
echo "Step 5: Port Availability Check"
echo "  Checking port 8000 (backend)..."
if lsof -i :8000 > /dev/null 2>&1; then
    echo "  ⚠ Port 8000 is already in use (PHP server may be running)"
else
    echo "  ✓ Port 8000 is available"
fi

echo "  Checking port 5173 (frontend)..."
if lsof -i :5173 > /dev/null 2>&1; then
    echo "  ⚠ Port 5173 is already in use (Vite server may be running)"
else
    echo "  ✓ Port 5173 is available"
fi
echo ""

# Success message
echo "=== Setup Complete! ==="
echo ""
echo "You can now start the services. Open 3 separate terminal windows and run:"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "Terminal 1 - SSH Log Agent:"
echo "  cd $PROJECT_DIR/agent"
echo "  node sshld-agent.js"
echo ""
echo "Terminal 2 - PHP Backend API:"
echo "  cd $PROJECT_DIR/backend"
echo "  php -S localhost:8000"
echo ""
echo "Terminal 3 - React Frontend:"
echo "  cd $PROJECT_DIR/frontend"
echo "  npm run dev"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "Once all three are running, visit:"
echo "  Dashboard: http://localhost:5173"
echo "  Backend API: http://localhost:8000/api/health"
echo ""
echo "For detailed setup instructions, see: $PROJECT_DIR/SETUP.md"
echo ""
