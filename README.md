# SSHLD_002 - SSH Log Dashboard SaaS

Lightweight, zero-configuration SSH authentication log monitoring platform for Linux admins, hosting companies, and security-conscious developers.

## Quick Features

- **Agent**: Tails auth.log, parses SSH events, compresses and uploads to backend
- **Backend**: PHP API on OVH-friendly shared hosting (no SSH required)
- **Database**: SQLite3 for zero-ops deployment
- **Frontend**: React dashboard with real-time stats and event tracking
- **Monitoring**: Failed login attempts, brute-force detection, risk scoring

## Quick Start (Local Dev)

```bash
# 1. Initialize database
php schema/init_db.php

# 2. Install and start agent (Terminal 1)
cd agent && npm install && node sshld-agent.js

# 3. Start backend (Terminal 2)
cd backend && php -S localhost:8000

# 4. Start frontend (Terminal 3)
cd frontend && npm install && npm run dev
```

Visit http://localhost:5173 in your browser.

## Project Structure

```
/var/www/html/SSHLD_002/
├── schema/              # Database schema and initialization
├── agent/               # Node.js log collector
├── backend/             # PHP API + SQLite3
├── frontend/            # React + Vite dashboard
├── db/                  # SQLite database (generated)
├── tests/               # Test scripts and utilities
├── docs/                # Additional documentation
├── README.md            # This file
├── SETUP.md             # Detailed setup guide
└── DEPLOY.md            # OVH deployment guide
```

## Stack

- **Agent**: Node.js 16+
- **Backend**: PHP 8.0+
- **Frontend**: React 18+ with Vite
- **Database**: SQLite3
- **Hosting**: OVH shared hosting (or any server with PHP+FTP)

## Documentation

- [SETUP.md](./SETUP.md) - Local development setup and testing
- [DEPLOY.md](./DEPLOY.md) - Deployment to OVH shared hosting
- [docs/API.md](./docs/API.md) - REST API reference

## License

MIT

## Author

Built for SMBs and indie developers by a solo dev with zero budget.

---

**Status**: MVP Phase 1 Complete (log collection, basic dashboard, SQLite storage)
