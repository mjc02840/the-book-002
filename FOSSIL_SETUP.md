# Fossil SCM Setup for SSHLD_002

This document explains how to set up parallel Fossil version control for SSHLD_002.

## Why Both Git and Fossil?

The project uses both Git and Fossil for redundancy and flexibility:
- **Git**: Standard DVCS used by most developers, excellent for community contributions
- **Fossil**: Integrated project management, built-in bug tracking, simpler distributed workflow

## Installation

### Prerequisites

Fossil requires either pre-built binary or building from source:

### Install Fossil (Debian/Ubuntu)

**Option 1: Using apt (Recommended)**

```bash
sudo apt-get update
sudo apt-get install -y fossil
fossil version
```

**Option 2: Download Pre-built Binary**

```bash
# Download latest stable
cd /tmp
wget https://fossil-scm.org/home/uv/fossil-linux-x64-<VERSION>.zip
unzip fossil-linux-x64-*.zip
sudo mv fossil /usr/local/bin/
sudo chmod +x /usr/local/bin/fossil
fossil version
```

**Option 3: Build from Source**

```bash
git clone https://github.com/fossil-scm/fossil
cd fossil
./configure --with-openssl=auto
make
sudo make install
fossil version
```

## Initialize Fossil Repository

Once Fossil is installed:

```bash
cd /var/www/html/SSHLD_002

# Create Fossil repository
fossil init .fossil

# Configure user (one-time)
fossil user add MJC -p your_password
fossil user login MJC

# Add all git-tracked files
fossil addremove
```

## Create Initial Fossil Commit

```bash
# View status
fossil status

# Commit
fossil commit --comment "Initial SSHLD_002 project structure: agent, backend, frontend, schema, and documentation"

# View log
fossil log
```

## Using Both Git and Fossil in Parallel

### Workflow 1: Commit to Both Simultaneously

After making changes:

```bash
# Stage in git
git add -A
git commit -m "Feature: Add new API endpoint"

# Mirror to fossil
cd /var/www/html/SSHLD_002
fossil addremove
fossil commit --comment "Feature: Add new API endpoint"
```

### Workflow 2: Git as Primary, Fossil as Secondary

1. Work primarily with Git (primary)
2. Periodically sync Fossil:

```bash
# After git commits
fossil addremove
fossil commit --comment "Sync with git: $(git log -1 --oneline)"
```

### Workflow 3: Independent Branches

Use each system for different purposes:

- **Git**: Main development, CI/CD integration
- **Fossil**: Backup, offline work, integrated project tracking

## Fossil Commands Reference

```bash
# Repository initialization
fossil init <repo-name>
fossil clone <url> [<local-copy>]
fossil open <repo> [<version>]
fossil close

# Checking changes
fossil status
fossil changes
fossil diff

# Committing
fossil commit --comment "message"
fossil commit --branch new-feature --comment "message"

# History
fossil log [--timeline] [--verbose]
fossil timeline [-n 20]

# Branching
fossil branch new feature-name
fossil branch list
fossil merge <branch>

# User management
fossil user add <username> -p <password>
fossil user login <username>
fossil user list

# Web interface (useful!)
fossil ui      # Starts web interface on http://localhost:8080
fossil server  # Production server mode
```

## Fossil Web Interface

Fossil has a built-in web UI - very useful!

```bash
cd /var/www/html/SSHLD_002
fossil ui
# Opens http://localhost:8080
# Shows: timeline, files, branches, tickets, wiki, docs
```

## Syncing Fossil with Git

Create a sync script (optional):

```bash
#!/bin/bash
# sync.sh - Keep git and fossil in sync

cd /var/www/html/SSHLD_002

# Get latest commit message from git
MSG=$(git log -1 --oneline)

# Check if fossil needs update
if [ -n "$(fossil changes)" ]; then
    fossil addremove
    fossil commit --comment "Sync: $MSG"
    echo "✓ Fossil updated"
else
    echo "✓ Fossil already up to date"
fi
```

Run periodically:
```bash
chmod +x sync.sh
./sync.sh
```

## Ignoring Files in Fossil

Fossil ignores files listed in `.fossil-ignore`:

```bash
# Create .fossil-ignore
cat > /var/www/html/SSHLD_002/.fossil-ignore << 'EOF'
# Fossil ignore file
node_modules/
dist/
.env
*.log
*.swp
*.db
db/*.db
uploads/
.DS_Store
.vscode/
.idea/
EOF

# Apply
fossil addremove
fossil commit --comment "Add fossil ignore file"
```

## Tips

1. **Keep Both Synced**: Set up a habit to commit to both systems
2. **Use Fossil for Backups**: Fossil repositories are self-contained in `.fossil` file
3. **Distributed History**: Both git and fossil support full cloning
4. **Fossil Tickets**: Use Fossil's integrated ticket system for bug tracking (Phase 2 feature)
5. **Project Wiki**: Fossil includes a wiki - useful for internal documentation

## Troubleshooting

### "fossil: command not found"
Install Fossil (see Installation section above)

### Permission denied when opening Fossil repo
```bash
chmod 600 .fossil
chmod 755 .
```

### Locked database error
Fossil may be running a UI or server:
```bash
# Kill any running fossil processes
pkill fossil

# Or be specific:
fossil close  # If in an open checkout
```

### Sync conflicts between git and fossil
If files diverge:

1. Delete `.fossil` file
2. Reinitialize: `fossil init .fossil`
3. Re-add all files and commit

## Next Steps

Once Fossil is installed:

```bash
cd /var/www/html/SSHLD_002
fossil init .fossil
fossil user add MJC -p your_password
fossil addremove
fossil commit --comment "Initial SSHLD_002 project"
```

Then establish a sync habit when making changes.

## Resources

- [Fossil Official Docs](https://fossil-scm.org/)
- [Fossil vs Git](https://fossil-scm.org/home/doc/trunk/www/faq.wiki)
- [Fossil Forum](https://fossil-scm.org/forum/)

---

**Note**: This project uses Git as primary version control. Fossil is optional for local redundancy and integrated project management tools.
