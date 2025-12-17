# SSHLD_002 - Deployment to OVH Shared Hosting

Complete guide for deploying SSHLD_002 to OVH.COM shared hosting.

## Prerequisites

- OVH shared hosting account with FTP access (Port 21)
- PHP 8.0+ available on hosting
- SQLite3 support (included in most PHP 8.0+ installations)
- 100GB+ storage (per your plan)
- FTP credentials (hostname, username, password)
- Domain name or subdomain

## Architecture Overview

```
Your t630 Server                    OVH Shared Hosting
┌─────────────────┐                ┌──────────────────────┐
│ SSH Log Agent   │  FTP Upload     │ PHP API + Dashboard  │
│ (Node.js)       │──────────────→  │ (public_html/)       │
│ /var/log/       │  Compressed     │ SQLite DB            │
└─────────────────┘  JSON           │ (in db/ folder)      │
                                    └──────────────────────┘
                                         ↓
                                    Browser Access
                                    https://yourdomain.com
```

## Step 1: Prepare Local Build

On your t630 (development machine):

```bash
cd /var/www/html/SSHLD_002

# Build React frontend
cd frontend
npm run build
# Creates dist/ folder with optimized production build

# Return to project root
cd ..
```

## Step 2: Prepare Deployment Package

Create a clean deployment directory:

```bash
mkdir -p ~/sshld_deploy
cd ~/sshld_deploy

# Copy backend files
cp -r /var/www/html/SSHLD_002/backend/* .

# Copy frontend build
mkdir -p public
cp -r /var/www/html/SSHLD_002/frontend/dist/* public/

# Create required directories
mkdir -p db uploads logs

# Copy schema for setup
cp /var/www/html/SSHLD_002/schema/schema.sql .
cp /var/www/html/SSHLD_002/schema/init_db.php .
```

Package structure:
```
~/sshld_deploy/
├── public/              # Frontend (React build)
│   ├── index.html
│   ├── assets/
│   └── ...
├── src/
│   ├── Database.php
│   └── Importer.php
├── cron/
│   └── ftp-watcher.php
├── .env.example
├── .env
├── .htaccess           # Apache rewrite rules (to create)
├── init_db.php
├── schema.sql
├── db/                 # Will be created
├── uploads/            # Will be created
└── logs/               # Will be created
```

## Step 3: Create .htaccess for URL Routing

Create this file for proper API routing:

```bash
cat > ~/sshld_deploy/.htaccess << 'EOF'
# .htaccess
# SSHLD_002 URL routing for OVH shared hosting

<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /sshld_002/

    # Serve files and directories as-is
    RewriteCond %{REQUEST_FILENAME} -f [OR]
    RewriteCond %{REQUEST_FILENAME} -d
    RewriteRule ^ - [L]

    # Route API calls to index.php
    RewriteRule ^api/ index.php [L,QSA]

    # Route other requests to frontend
    RewriteRule ^(.*)$ public/index.html [L]
</IfModule>
EOF
```

## Step 4: Upload via FTP

You can use any FTP client. Here's using `lftp`:

```bash
# Install lftp if needed
sudo apt-get install lftp

# Connect and upload
lftp ftp://your_ftp_user:your_ftp_pass@your_ftp_host
> cd public_html
> mkdir sshld_002
> cd sshld_002
> mirror -R ~/sshld_deploy .
> exit
```

Or using another FTP client (FileZilla, WinSCP, etc.):
1. Connect to: `your_ftp_host` (port 21)
2. Login with: `your_ftp_user` / `your_ftp_pass`
3. Navigate to: `/public_html/`
4. Create folder: `sshld_002`
5. Upload contents of `~/sshld_deploy/` into it

## Step 5: Initialize Database on OVH

Once files are uploaded, initialize the database:

**Option A: Via Browser (Recommended for OVH)**

1. Visit: `https://yourdomain.com/sshld_002/init_db.php`
2. You should see:
   ```
   ✓ Database initialized at: /path/to/sshld_002/db/sshld_002.db
   ✓ Tables created successfully
   ```

**Option B: Via SSH (if available)**

```bash
ssh your_ssh_user@yourdomain.com
cd /var/www/html/sshld_002
php init_db.php
```

**Option C: Via FTP Manager**

If OVH's control panel has a file manager:
1. Navigate to `/sshld_002/`
2. Right-click `init_db.php` → Execute or Run

## Step 6: Configure Permissions

Via FTP manager or SSH:

```bash
chmod 755 db/
chmod 755 uploads/
chmod 755 logs/
chmod 644 db/sshld_002.db (after initialization)
```

## Step 7: Update Backend Configuration

Edit `backend/.env` on OVH via FTP:

```bash
# .env on OVH
DB_PATH=/var/www/html/sshld_002/db/sshld_002.db
FTP_UPLOAD_DIR=/var/www/html/sshld_002/uploads
ACCOUNT_ID=default
MODE=local
```

(Update paths to match your OVH hosting structure)

## Step 8: Setup Cron Job (FTP Watcher)

OVH cron jobs are set up in the control panel:

1. Log in to OVH Control Panel
2. Navigate to: Web > Your Domain > Cron
3. Create new cron job:
   - **Command**: `php /var/www/html/sshld_002/backend/cron/ftp-watcher.php`
   - **Frequency**: Every 5 minutes
   - **Email notifications**: Optional

This runs every 5 minutes to:
- Check `/uploads/` directory
- Import any new `.json.gz` files
- Update daily stats
- Log results

## Step 9: Configure Agent for FTP Upload

Back on your t630, update `agent/.env` for production:

```bash
# agent/.env (Production)
AGENT_NAME=t630-server-1
LOG_PATH=/var/log/auth.log
UPLOAD_INTERVAL=3600      # Upload every hour
MODE=ftp
FTP_HOST=your_ftp_host.ovh.net
FTP_USER=your_ftp_user
FTP_PASS=your_ftp_pass
FTP_REMOTE_DIR=/sshld_002/uploads
```

Restart agent:

```bash
cd /var/www/html/SSHLD_002/agent
node sshld-agent.js
```

Expected output:
```
[2025-12-17T10:30:45.123Z] SSHLD_002 Agent started
Mode: ftp, Agent: t630-server-1
Tailing: /var/log/auth.log
[2025-12-17T10:30:50.456Z] Parsed: failed_login from 192.168.1.100
[2025-12-17T11:30:50.456Z] ✓ Uploaded (FTP): agent-t630-server-1-1702797050456.json.gz
```

## Step 10: Test End-to-End

1. **Agent uploads to OVH FTP**:
   - Check: FTP `/sshld_002/uploads/` directory has `.gz` files
   - Wait up to 60 minutes for first upload (or adjust `UPLOAD_INTERVAL`)

2. **Cron imports files**:
   - Files disappear from `/uploads/` after import
   - Check cron job log in OVH control panel

3. **Dashboard updates**:
   - Visit: `https://yourdomain.com/sshld_002/`
   - Stats cards should show data
   - Events table should populate

4. **Verify data in database**:
   - Via OVH SSH (if available):
     ```bash
     sqlite3 /var/www/html/sshld_002/db/sshld_002.db
     sqlite> SELECT COUNT(*) FROM events;
     ```

## Step 11: DNS & HTTPS Setup

OVH usually auto-provides HTTPS via Let's Encrypt:

1. Log in to OVH Control Panel
2. Verify SSL certificate is active
3. Access via: `https://yourdomain.com/sshld_002/`

## Troubleshooting

### Database not initializing

**Problem**: init_db.php returns error

**Solution**:
- Verify PHP 8.0+ is installed: contact OVH support
- Check permissions: `/sshld_002/` should be 755 or 775
- Try via SSH if available:
  ```bash
  cd /var/www/html/sshld_002
  php init_db.php
  ```

### FTP upload fails from agent

**Problem**: Agent logs show FTP connection errors

**Solution**:
- Verify credentials in `agent/.env`
- Check FTP user permissions on `/sshld_002/uploads/`
- Ensure uploads directory exists: create via FTP if needed
- Test FTP connection locally:
  ```bash
  ftp your_ftp_host
  > cd /sshld_002/uploads
  > quit
  ```

### Cron job not running

**Problem**: No new events in database despite agent uploads

**Solution**:
- Verify cron job is enabled in OVH control panel
- Check cron log in OVH control panel
- Manually test the cron script:
  ```bash
  curl https://yourdomain.com/sshld_002/backend/cron/ftp-watcher.php
  # or via SSH:
  php /var/www/html/sshld_002/backend/cron/ftp-watcher.php
  ```

### Dashboard shows blank or 404

**Problem**: `https://yourdomain.com/sshld_002/` returns 404

**Solution**:
- Verify `.htaccess` file is uploaded and readable
- Check RewriteEngine is enabled in OVH hosting
- Verify `public/index.html` exists
- Contact OVH support if mod_rewrite is disabled

### API returns 500 errors

**Problem**: `/api/events` returns 500 Internal Server Error

**Solution**:
- Check database path in `backend/.env`
- Verify `db/` and `db/sshld_002.db` exist and have proper permissions
- Check PHP error logs in OVH control panel
- Verify SQLite3 is installed: ask OVH support

## Performance Optimization

For multiple servers uploading:

1. **Batch uploads**: Increase `UPLOAD_INTERVAL` in agent (3600 = 1 hour)
2. **Database indexes**: Already included in schema.sql
3. **Retention policy**: Configure in Phase 2
4. **Archive old logs**: Move events older than 90 days to archive

## Monitoring

**Check agent is uploading**:
```bash
# Monitor FTP directory
# (Via OVH file manager or FTP client)
# Should see new .gz files every UPLOAD_INTERVAL
```

**Check cron is running**:
```bash
# OVH control panel: Web > Domain > Cron
# View execution logs
```

**Check dashboard**:
```bash
# Visit https://yourdomain.com/sshld_002/
# Stats should update every 5 minutes (cron frequency)
```

## Scaling to Multiple Servers

To monitor multiple servers:

1. Deploy agent on each server with unique `AGENT_NAME`
2. All agents upload to same OVH FTP directory
3. Backend auto-aggregates by `account_id` in database
4. Dashboard shows combined stats for all servers

Example:
```bash
# Server 1: agent/.env
AGENT_NAME=prod-web-1

# Server 2: agent/.env
AGENT_NAME=prod-db-1

# Server 3: agent/.env
AGENT_NAME=prod-mail-1
```

All upload to same OVH account/backend, creating a unified security dashboard.

## Backup Strategy

Backup your database regularly:

**OVH Control Panel Backup**:
1. Use OVH backup feature if available
2. Or manually backup:
   ```bash
   # Via FTP manager, download:
   # /sshld_002/db/sshld_002.db
   ```

**On your t630**:
```bash
# Add to crontab
0 2 * * * cp /var/www/html/SSHLD_002/db/sshld_002.db ~/backups/sshld_$(date +%Y%m%d).db
```

## Next Steps

- Monitor logs for 1-2 weeks
- Verify all data flows correctly
- Build Phase 2 (analytics, threat detection, multi-user)
- Add email alerts
- Implement monetization tiers

See [README.md](./README.md) for full project overview.
