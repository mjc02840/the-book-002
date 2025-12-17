# SSHLD_002 - Local Development Setup

Complete guide for setting up and testing SSHLD_002 on your local development machine.

## Prerequisites

- Node.js 16+ (`node --version`)
- PHP 8.0+ (`php --version`)
- SQLite3 (`sqlite3 --version`)
- Debian 12 or Ubuntu (other Linux distros work too)
- Bash shell

## Step 1: Initialize Database

```bash
cd /var/www/html/SSHLD_002
php schema/init_db.php
```

Expected output:
```
✓ Database initialized at: /var/www/html/SSHLD_002/db/sshld_002.db
✓ Tables created successfully
```

## Step 2: Start Agent (Terminal 1)

```bash
cd /var/www/html/SSHLD_002/agent
npm install
node sshld-agent.js
```

The agent will:
- Tail `/var/log/auth.log` (configurable in `.env`)
- Parse SSH authentication events
- Compress events to `.json.gz` format
- Upload to `./uploads/` directory every 60 seconds (local mode)

Expected output:
```
[2025-12-17T10:30:45.123Z] SSHLD_002 Agent started
Mode: local, Agent: t630-default
Tailing: /var/log/auth.log
[2025-12-17T10:30:50.456Z] Parsed: failed_login from 192.168.1.100
...
```

## Step 3: Start Backend (Terminal 2)

```bash
cd /var/www/html/SSHLD_002/backend
php -S localhost:8000
```

The backend will:
- Listen on http://localhost:8000
- Serve `/api/events` - fetch authentication events
- Serve `/api/stats` - fetch daily statistics
- Serve `/api/import` - trigger import of agent uploads
- Serve `/api/health` - health check endpoint

Test the API:
```bash
curl http://localhost:8000/api/health
# Response: {"status":"ok","timestamp":"2025-12-17T10:30:50Z"}

curl http://localhost:8000/api/events
# Response: [] (empty until you import)

curl -X POST http://localhost:8000/api/import
# Response: {"processed":1,"failed":0}
```

## Step 4: Start Frontend (Terminal 3)

```bash
cd /var/www/html/SSHLD_002/frontend
npm install
npm run dev
```

Frontend will:
- Start development server on http://localhost:5173
- Auto-reload on code changes
- Proxy API calls to http://localhost:8000/api
- Display dashboard with real-time event updates

Visit: http://localhost:5173

## Test Workflow

### Generate SSH Test Events

In another terminal, generate some auth log entries:

```bash
# Try invalid login multiple times
for i in {1..5}; do
  ssh invalid@localhost 2>&1 | true
done

# Try valid login (will fail if no SSH key)
ssh localhost 2>&1 | true
```

Monitor `/var/log/auth.log` to see new entries:
```bash
sudo tail -f /var/log/auth.log
```

### Trigger Import

Option A: Use dashboard button
- Open http://localhost:5173
- Click "Import Now" button
- Events should appear in the table within 5 seconds

Option B: Use curl
```bash
curl -X POST http://localhost:8000/api/import
```

Option C: Check agent uploads
```bash
ls -la /var/www/html/SSHLD_002/agent/uploads/
```

### Verify Data Flow

1. **Agent generates events** (Terminal 1)
   - Check for: `✓ Uploaded (local): agent-...json.gz`

2. **Backend imports events** (Terminal 2)
   - Make API call: `curl -X POST http://localhost:8000/api/import`
   - Check `/api/events`: `curl http://localhost:8000/api/events`

3. **Frontend displays events** (Terminal 3)
   - Visit http://localhost:5173
   - Events appear in "Recent Events" table
   - Stats update in cards

## Troubleshooting

### Agent not tailing /var/log/auth.log

**Problem**: Agent shows "Log file not found"

**Solution**:
- Check if file exists: `ls -l /var/log/auth.log`
- On some systems it might be `/var/log/secure`
- Edit `agent/.env`:
  ```bash
  LOG_PATH=/var/log/secure  # or your system's auth log
  ```

### Backend API returns 404

**Problem**: curl http://localhost:8000/ returns 404

**Solution**:
- Ensure PHP server is running: `php -S localhost:8000` (in backend/)
- Check port 8000 is not in use: `lsof -i :8000`
- Verify `.env` file exists in `backend/` directory

### Frontend shows blank page or API errors

**Problem**: Dashboard doesn't load or shows connection errors

**Solution**:
- Check browser console: F12 → Console tab
- Verify backend is running on :8000
- Check frontend proxy config in `vite.config.js`
- Clear browser cache: Ctrl+Shift+Delete

### Database is locked

**Problem**: Error "database is locked"

**Solution**:
- Close any SQLite clients: `sqlite3 db/sshld_002.db .quit`
- Check file permissions: `chmod 644 db/sshld_002.db`
- Restart backend: Kill and restart `php -S localhost:8000`

### npm install fails

**Problem**: npm install throws errors

**Solution**:
```bash
# Clear npm cache
npm cache clean --force

# Delete node_modules
rm -rf node_modules package-lock.json

# Reinstall
npm install
```

## Development Tips

### View Agent Uploads

```bash
ls -lh agent/uploads/
zcat agent/uploads/agent-*.json.gz | head -20
```

### Query Database Directly

```bash
sqlite3 db/sshld_002.db
sqlite> SELECT COUNT(*) FROM events;
sqlite> SELECT * FROM events LIMIT 5;
sqlite> .exit
```

### Check Daily Stats

```bash
sqlite3 db/sshld_002.db "SELECT * FROM daily_stats ORDER BY stat_date DESC LIMIT 3;"
```

### Monitor Logs

```bash
# Agent logs
tail -f agent/uploads/*.json.gz  # After decompression

# Backend error logs
php -S localhost:8000 2>&1 | tee backend.log

# Frontend console (in browser)
# F12 → Console
```

## Configuration

Edit `.env` files to customize:

**agent/.env**:
```bash
AGENT_NAME=my-server       # Agent identifier
LOG_PATH=/var/log/auth.log # Log file to tail
UPLOAD_INTERVAL=60         # Upload every 60 seconds
MODE=local                 # local or ftp
```

**backend/.env**:
```bash
DB_PATH=../db/sshld_002.db
FTP_UPLOAD_DIR=./uploads
ACCOUNT_ID=default
```

## Next Steps

- **Test with real logs**: Run for a few hours and collect data
- **Verify dashboard**: Ensure all stats and events display correctly
- **Check deployment**: See [DEPLOY.md](./DEPLOY.md) for OVH setup
- **Phase 2 features**: Analytics, threat detection, multi-user support

## Support

See [docs/API.md](./docs/API.md) for API reference and data formats.
