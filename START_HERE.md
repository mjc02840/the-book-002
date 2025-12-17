# SSHLD_002 - START HERE

Welcome! This is your quick-start guide to get SSHLD_002 running in minutes.

## What You Have

A complete **SSH Log Dashboard** system with:
- ✅ Node.js agent (collects SSH logs)
- ✅ PHP backend (REST API)
- ✅ React frontend (real-time dashboard)
- ✅ SQLite3 database
- ✅ Full documentation
- ✅ Ready for OVH deployment

## Option 1: Automated Setup (Recommended)

Run this script to set everything up automatically:

```bash
bash /var/www/html/SSHLD_002/tests/test-local.sh
```

This will:
1. Initialize the SQLite3 database
2. Install all npm dependencies
3. Verify everything is ready
4. Show you exactly what to run

**Estimated time**: 1-2 minutes

---

## Option 2: Manual Setup

### Step 1: Navigate to Project

```bash
cd /var/www/html/SSHLD_002
```

### Step 2: Initialize Database

The database is auto-initialized. To manually create it:

```bash
node schema/init_db.js
```

### Step 3: Start Agent (Terminal 1)

```bash
cd agent
npm install  # First time only
node sshld-agent.js
```

Expected output:
```
[2025-12-17T10:30:45.123Z] SSHLD_002 Agent started
Mode: local, Agent: t630-default
Tailing: /var/log/auth.log
```

### Step 4: Start Backend (Terminal 2)

```bash
cd backend
php -S localhost:8000
```

Expected output:
```
Development Server (http://localhost:8000)
Press Ctrl+C to quit.
```

### Step 5: Start Frontend (Terminal 3)

```bash
cd frontend
npm install  # First time only
npm run dev
```

Expected output:
```
VITE v5.x.x  ready in XXX ms

➜  Local:   http://localhost:5173/
```

### Step 6: Open Dashboard

Visit in your browser:
```
http://localhost:5173
```

You should see the SSHLD_002 dashboard with empty stats (no data yet).

---

## Test the System

### Generate SSH Log Events

In a new terminal, trigger some SSH events:

```bash
# Try invalid login (will fail)
ssh invalid@localhost

# Try another one
ssh baduser@localhost
```

Or check what's in the auth.log:
```bash
sudo tail -f /var/log/auth.log | grep sshd
```

### Import Events

1. **Via Dashboard**: Click "Import Now" button
2. **Via API**:
   ```bash
   curl -X POST http://localhost:8000/api/import
   ```

### View Results

Within 5 seconds, the dashboard should show:
- Failed login count in stat cards
- Events in the table
- Updated risk score

---

## What's Next?

### ✅ Testing Locally

- Run the system for a few hours
- Generate more SSH events
- Watch stats update in real-time
- Check database: See [SETUP.md - Troubleshooting](./SETUP.md#troubleshooting)

### 📦 Deploy to OVH

When ready, follow [DEPLOY.md](./DEPLOY.md):
- Build frontend
- Upload via FTP
- Initialize OVH database
- Configure agent for FTP
- Setup cron job

### 📚 Learn More

- **Full Setup Guide**: [SETUP.md](./SETUP.md)
- **API Reference**: [docs/API.md](./docs/API.md)
- **OVH Deployment**: [DEPLOY.md](./DEPLOY.md)
- **Project Status**: [PROJECT_COMPLETION_SUMMARY.md](./PROJECT_COMPLETION_SUMMARY.md)
- **Overview**: [README.md](./README.md)

---

## Troubleshooting

### "Agent not tailing auth.log"

Check if the file exists:
```bash
ls -l /var/log/auth.log
```

If it's at a different location, edit `agent/.env`:
```bash
LOG_PATH=/var/log/secure  # For RHEL/CentOS
```

### "Backend returns 404"

Make sure PHP is running:
```bash
# Check if port 8000 is listening
lsof -i :8000

# If nothing, start PHP:
cd backend && php -S localhost:8000
```

### "Frontend shows blank"

Check browser console (F12):
- Look for "Failed to fetch" errors
- Verify backend is running on :8000
- Check Vite config is correct

### "No events in database"

1. Did you generate SSH events? (see Test section above)
2. Did you click "Import Now"?
3. Check agent/uploads/ folder:
   ```bash
   ls agent/uploads/
   ```

### Still stuck?

See detailed troubleshooting in [SETUP.md](./SETUP.md#troubleshooting)

---

## Quick Commands Reference

```bash
# Check if services are running
lsof -i :8000  # Backend
lsof -i :5173  # Frontend

# Kill a service
pkill -f "php -S"      # Kill PHP backend
pkill -f "node"        # Kill Node services
pkill -f "vite"        # Kill Vite

# View database
sqlite3 db/sshld_002.db "SELECT COUNT(*) FROM events;"

# Test API
curl http://localhost:8000/api/health
curl http://localhost:8000/api/events
curl -X POST http://localhost:8000/api/import

# Check agent logs
tail -100 agent/uploads/*.json.gz | zcat | head -20
```

---

## Project Structure

```
SSHLD_002/
├── agent/          → Node.js log collector
├── backend/        → PHP REST API
├── frontend/       → React dashboard
├── schema/         → Database setup
├── db/             → SQLite database
├── docs/           → API documentation
├── tests/          → Test scripts
└── [documentation] → README, SETUP, DEPLOY, etc.
```

---

## Architecture (Simple)

```
Your Server                    SSHLD_002 (Localhost)
┌──────────────┐              ┌─────────────────────┐
│ auth.log     │              │ Dashboard           │
└──────┬───────┘              │ ┌─────────────────┐ │
       │                      │ │ React Frontend  │ │
       └──→ Agent ───→ uploads │ │ (http://5173)   │ │
            (Node.js)   │     │ └────────┬────────┘ │
                        │     │          ↓          │
                        │     │ ┌─────────────────┐ │
                        └────→│ PHP Backend API  │ │
                              │ (http://8000)    │ │
                              └────────┬────────┘ │
                                       ↓          │
                              ┌─────────────────┐ │
                              │ SQLite3 DB      │ │
                              │ (sshld_002.db)  │ │
                              └─────────────────┘ │
                        └─────────────────────────┘
```

---

## Success Criteria

You've completed setup when you can:

✅ Run `tests/test-local.sh` without errors
✅ All 3 services start (agent, backend, frontend)
✅ Dashboard loads at http://localhost:5173
✅ API responds at http://localhost:8000/api/health
✅ Import button works
✅ Events appear in the dashboard

---

## Next: Read SETUP.md

For detailed setup, troubleshooting, and testing workflow:

👉 **[SETUP.md](./SETUP.md)**

---

**Questions?** Check:
- [SETUP.md](./SETUP.md) - Detailed setup & troubleshooting
- [DEPLOY.md](./DEPLOY.md) - OVH deployment
- [docs/API.md](./docs/API.md) - API reference
- [PROJECT_COMPLETION_SUMMARY.md](./PROJECT_COMPLETION_SUMMARY.md) - What's built

**Ready?** Run:
```bash
bash /var/www/html/SSHLD_002/tests/test-local.sh
```

Good luck! 🚀
