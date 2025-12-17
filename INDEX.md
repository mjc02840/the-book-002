# SSHLD_002 Documentation Index

Complete guide to all project files and documentation.

## Quick Navigation

### 🚀 Getting Started (Read These First)

1. **[START_HERE.md](./START_HERE.md)** - **START HERE!**
   - Quick-start guide (5 minutes)
   - Automated setup script
   - Manual setup instructions
   - Troubleshooting quick reference
   - Success criteria

2. **[README.md](./README.md)** - Project Overview
   - Features summary
   - Quick features list
   - Project structure
   - Technology stack

### 📖 Detailed Guides

3. **[SETUP.md](./SETUP.md)** - Local Development Setup
   - Complete setup instructions
   - Step-by-step guide for all services
   - Test workflow (generating SSH events)
   - Comprehensive troubleshooting
   - Configuration reference
   - Development tips

4. **[DEPLOY.md](./DEPLOY.md)** - OVH Deployment
   - Deployment architecture
   - Build and preparation
   - FTP upload instructions
   - Database initialization
   - Permissions and configuration
   - Cron job setup
   - End-to-end testing
   - Monitoring and scaling

5. **[docs/API.md](./docs/API.md)** - REST API Reference
   - Base URLs (local and production)
   - All 4 API endpoints documented
   - Request/response examples
   - Error handling
   - Agent upload format
   - Usage examples (JavaScript, Bash, Python)
   - Database schema reference

### 🔧 Additional Resources

6. **[FOSSIL_SETUP.md](./FOSSIL_SETUP.md)** - Fossil SCM Setup
   - Fossil installation guide
   - Repository initialization
   - Sync workflows (both systems)
   - Fossil web UI
   - Fossil commands reference
   - Tips and troubleshooting

7. **[PROJECT_COMPLETION_SUMMARY.md](./PROJECT_COMPLETION_SUMMARY.md)** - Technical Details
   - Complete feature list
   - Component details
   - Technology stack
   - Performance characteristics
   - File statistics
   - Known limitations
   - Next steps (Phase 2)

## Project Structure

```
SSHLD_002/
│
├── 📁 agent/                    # Node.js SSH log collection
│   ├── sshld-agent.js          # Main agent script
│   ├── package.json            # Dependencies
│   └── .env                    # Configuration (local dev)
│
├── 📁 backend/                  # PHP REST API
│   ├── public/
│   │   └── index.php           # API endpoints
│   ├── src/
│   │   ├── Database.php        # SQLite wrapper
│   │   └── Importer.php        # Event importer
│   ├── cron/
│   │   └── ftp-watcher.php     # FTP monitoring job
│   └── .env                    # Configuration (local dev)
│
├── 📁 frontend/                 # React + Vite Dashboard
│   ├── src/
│   │   ├── components/
│   │   │   └── Dashboard.jsx   # Main dashboard
│   │   ├── styles/
│   │   │   └── dashboard.css   # Styles
│   │   ├── App.jsx             # App root
│   │   ├── api.js              # API client
│   │   └── main.jsx            # Entry point
│   ├── index.html              # HTML template
│   ├── package.json            # Dependencies
│   └── vite.config.js          # Build config
│
├── 📁 schema/                   # Database Setup
│   ├── schema.sql              # Database schema
│   ├── init_db.js              # Node.js initializer
│   └── init_db.php             # PHP initializer
│
├── 📁 db/                       # SQLite Database
│   └── sshld_002.db           # Auto-generated
│
├── 📁 tests/                    # Testing
│   └── test-local.sh          # Automated setup
│
├── 📁 docs/                     # Additional Docs
│   └── API.md                 # API reference
│
├── 📋 Documentation Files
│   ├── START_HERE.md          # ← Start here!
│   ├── README.md              # Project overview
│   ├── SETUP.md               # Local development
│   ├── DEPLOY.md              # OVH deployment
│   ├── FOSSIL_SETUP.md        # Fossil SCM
│   ├── PROJECT_COMPLETION_SUMMARY.md  # Technical details
│   └── INDEX.md               # This file
│
└── 🔧 Configuration
    ├── .gitignore             # Git ignore rules
    ├── .env                   # Environment variables
    └── package.json files (agent, frontend)
```

## Quick Command Reference

### Setup
```bash
# Automated setup
bash /var/www/html/SSHLD_002/tests/test-local.sh

# Manual agent start
cd /var/www/html/SSHLD_002/agent && node sshld-agent.js

# Manual backend start
cd /var/www/html/SSHLD_002/backend && php -S localhost:8000

# Manual frontend start
cd /var/www/html/SSHLD_002/frontend && npm run dev
```

### Testing
```bash
# Trigger SSH events
ssh invalid@localhost  # Try failed login

# Import events
curl -X POST http://localhost:8000/api/import

# Check API health
curl http://localhost:8000/api/health

# View database
sqlite3 db/sshld_002.db "SELECT COUNT(*) FROM events;"
```

### Deployment
```bash
# Build frontend
cd frontend && npm run build

# View deployment package contents
ls ~/sshld_deploy/

# Upload via FTP (example with lftp)
lftp ftp://user:pass@host
> mirror -R ~/sshld_deploy /sshld_002
```

## Documentation by Topic

### Getting Started
- → **START_HERE.md** (5 min quickstart)
- → **SETUP.md** (Detailed setup with troubleshooting)

### Using the System
- → **docs/API.md** (API endpoints and usage)
- → **README.md** (Features and overview)

### Deploying
- → **DEPLOY.md** (Step-by-step OVH deployment)

### Understanding the Code
- → **PROJECT_COMPLETION_SUMMARY.md** (Technical architecture)
- → **docs/API.md** (Data formats and examples)

### Version Control
- → **FOSSIL_SETUP.md** (Fossil SCM parallel setup)

## File Quick Reference

| File | Purpose | Read When |
|------|---------|-----------|
| START_HERE.md | Quick start | First thing |
| README.md | Overview | Understanding the project |
| SETUP.md | Local dev | Setting up locally |
| DEPLOY.md | Production | Deploying to OVH |
| docs/API.md | API docs | Building integrations |
| FOSSIL_SETUP.md | VCS | Using Fossil |
| PROJECT_COMPLETION_SUMMARY.md | Technical details | Deep dive |
| INDEX.md | Navigation | Finding things (this file) |

## Git Commits

View the commit history:
```bash
cd /var/www/html/SSHLD_002
git log --oneline
```

- **cfd9ae7** - Add START_HERE quick-start guide
- **17c3324** - Add project completion summary
- **5310f07** - Make scripts executable
- **8b100fd** - Add Fossil documentation
- **3c64f1c** - Initial project structure

## Troubleshooting

### Can't find something?

1. Check [START_HERE.md](./START_HERE.md) - Quick reference
2. See [SETUP.md](./SETUP.md#troubleshooting) - Troubleshooting section
3. Check [docs/API.md](./docs/API.md) - If API-related
4. Read [PROJECT_COMPLETION_SUMMARY.md](./PROJECT_COMPLETION_SUMMARY.md) - Technical details

### Still stuck?

Look for error-specific help in:
- SETUP.md → "Troubleshooting" section
- START_HERE.md → "Troubleshooting" section
- docs/API.md → "Error Responses" section

## Next Steps

1. **First Time**: Read [START_HERE.md](./START_HERE.md)
2. **Setup**: Run `bash tests/test-local.sh`
3. **Test**: Follow SETUP.md test workflow
4. **Deploy**: Use DEPLOY.md when ready
5. **Integrate**: Reference docs/API.md for integration

## Project Status

- ✅ MVP Phase 1: Complete
- ⏳ Phase 2: Analytics & Threat Detection (coming soon)
- 🔄 Phase 3: Multi-tenancy & Monetization (planned)

## Support

- **Quick Help**: START_HERE.md
- **Setup Issues**: SETUP.md → Troubleshooting
- **API Questions**: docs/API.md
- **Deployment Help**: DEPLOY.md
- **Technical Deep Dive**: PROJECT_COMPLETION_SUMMARY.md

---

**Last Updated**: December 17, 2025
**Status**: MVP Phase 1 Complete
**Location**: /var/www/html/SSHLD_002/

👉 **Ready to start? Read [START_HERE.md](./START_HERE.md)**
