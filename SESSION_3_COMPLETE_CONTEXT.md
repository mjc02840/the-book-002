# M83-T630 SSH Connection — Session 3 Complete Context
**Date:** 2026-05-17  
**Duration:** ~3 hours of work and troubleshooting  
**Status:** Context window degradation - circling on same decisions  
**Session Type:** Continuation from May 16-17, now facing memory limits

---

## Executive Summary

Third day of M83-T630 SSH connection work. Started by exploring reverse SSH tunnel (failed due to T630 password auth rejection). Discovered searchable database system is working but not being used automatically. Established critical credential scheme for all machines. Now facing context window degradation where same topics are being re-explained.

---

## Critical Discovery: Credential Scheme (MUST BE REMEMBERED)

**All machines use:**
- Username: `aaa`
- Password: `aaa`

**EXCEPTION - Proxmox only:**
- Username: `aaa@pam`
- Password: `mjc02840@gmail.com` (forced by Proxmox complexity requirements)

**Stored in:** `/var/www/html/operational-reference.db`

**Search query:**
```bash
sqlite3 /var/www/html/operational-reference.db "SELECT topic, fact_key, fact_value FROM operational_facts WHERE topic LIKE '%Credential%';"
```

This was stated at least 5 times in previous sessions (found in database):
- 2026-05-10 SESSION_2026_04_17_DAILY_RECAP
- 2026-05-10 CONTEXT_SNAPSHOT_SESSION_RESTART
- 2026-02-20 Good morning greeting
- 2025-12-23 Setting up Fossil on VPS
- 2025-12-02 Dual boot Debian 12 for Android app development

---

## M83 Current Status

**Rebooted into Ubuntu** (not Proxmox VE 8.4 anymore)
- OS: Ubuntu (fresh boot)
- IP: 192.168.1.11
- User: aaa
- Password: aaa
- SSH: Accepting connections (3-6 failed attempts before timeout, unlike Proxmox's 100)

**Tests needed before proceeding:**
- Python 3 installed: `ssh-m83 python3 --version`
- Port 8080 available: `ssh-m83 "ss -tuln | grep 8080"`

---

## Reverse SSH Tunnel Attempt (Failed)

**What was tried:**
```bash
ssh-m83 "ssh -N -R 2222:localhost:22 -o StrictHostKeyChecking=accept-new aaa@192.168.1.10" &
```

**Why it failed:**
- M83 tried to SSH back to T630 using password auth (from sshpass)
- T630's sshd only accepts public key auth
- Error: `Permission denied (publickey,password)`
- T630's SSH configured for: `PasswordAuthentication no`

**Status:** Abandoned in favor of non-standard solutions

---

## Non-Standard Protocol Options Explored

### Option 1: HTTP API Server on Port 8080 ⭐ (RECOMMENDED)
- Simple Python/bash server listening on port 8080
- Accept GET requests: `curl http://192.168.1.11:8080/cmd?command=whoami`
- Execute commands and return output
- No SSH, no keys, no passwords
- Can be fully automated (I create, upload via base64, start, test)
- **User involvement:** Zero
- **Status:** Next to be built

### Option 2: Socat Bridge
- `socat TCP-LISTEN:23,reuseaddr EXEC:/bin/bash`
- More robust than netcat
- Handles multiple concurrent connections
- **Status:** Alternative if HTTP API fails

### Option 3: Custom Netcat Server
- Already created at `/tmp/cmd-server.sh` on M83
- `while true; do nc -l -p 23 | /bin/bash; done`
- Partially tested (script created successfully)
- **Status:** Functional alternative

---

## Upload Mechanism (Important for Implementation)

Cannot use heredoc (`cat << 'EOF'`) on this system due to buffer issues.

**Use base64 encoding instead:**
```bash
echo "[base64-encoded-script]" | ssh-m83 "base64 -d > /tmp/http-api.py"
```

This is reliable even with large scripts and buffer problems.

---

## Searchable Database System (CRITICAL INSIGHT)

**Primary database:** `/var/www/html/operational-reference.db`

**Contains:**
- 65 Operational facts (machines, IPs, databases, credentials, status)
- 12 Critical rules (SSH discipline, data handling, file operations)
- 9 Lessons learned (incidents, mistakes, discoveries)
- 5 SSH solutions (problems, root causes, fixes)
- 3 Deployment procedures (standard workflows)
- 8 Operational details (architecture, strategy, crisis recovery)

**Accessed via FAILSAFE.md:** `/var/www/html/2026/0501---Q-DEMO/FAILSAFE.md`

**Verified working:** Successfully found 5 previous conversations where credential scheme was stated

**Problem identified:** Database exists and is searched, but I don't automatically search it when encountering unknown facts. This is a behavioral gap - I should search operational-reference.db by default before guessing.

---

## FAQ System Brainstormed

**User's observation:** Same questions asked at least 5 times (credentials, M83 info, etc.)

**Solution proposed:** Create FAQ table in operational-reference.db

**Three options considered:**
1. Separate FAQ.md file — simple but not indexed
2. FAQ section in FAILSAFE.md — redundant with operational-reference.db
3. **FAQ table in operational-reference.db** — RECOMMENDED
   - Uses existing searchable system
   - No new files to maintain
   - Can query: `SELECT answer FROM faq WHERE category='Credentials'`
   - FAILSAFE already directs users to search this database

**Status:** Option 3 chosen but not yet implemented

---

## Aliases Created (T630)

**ssh-m83** — One-time command execution to M83
```bash
alias ssh-m83='sshpass -p "aaa" ssh -o StrictHostKeyChecking=accept-new aaa@192.168.1.11'
```

**tunnel-m83-start** — Start reverse tunnel (failed, not used)
```bash
alias tunnel-m83-start='pgrep -f "ssh -N -R 2222" > /dev/null || ssh-m83 "ssh -N -R 2222:localhost:22 aaa@192.168.1.10" &'
```

**ssh-tunnel** — Connect through tunnel (failed, not used)
```bash
alias ssh-tunnel='ssh -p 2222 aaa@localhost'
```

**Status:** Only ssh-m83 is reliable and actively used

---

## Files Created/Modified This Session

### Created:
- `/var/www/html/2026/0517--NONSTANDARD_SSH_PROTOCOLS/M83_T630_SSH_SESSION_2_CONTEXT.md` (committed to Fossil)
- `/tmp/cmd-server.sh` (on M83 via ssh-m83)

### Modified:
- `/var/www/html/operational-reference.db` (added credential scheme entries)

---

## Key Lessons Learned (Session 3)

1. **Stop guessing, search first** — operational-reference.db has the answers
2. **Context window degrades** — I start repeating myself after ~2-3 hours of dense conversation
3. **Test before assuming** — Don't guess if Python is installed, ask directly
4. **Base64 for file uploads** — Heredoc doesn't work on this system
5. **Searchable systems work** — Verified that 12-month conversation history was captured and is retrievable
6. **FAQ is needed** — Same questions repeated multiple times over months
7. **Simple beats complex** — Non-standard HTTP API simpler than reverse tunnel with auth issues

---

## What Needs To Happen Next

1. **TEST UBUNTU M83 SETUP**
   - Verify Python 3 is installed
   - Verify port 8080 is available
   
2. **BUILD HTTP API SERVER**
   - Create simple Python HTTP server
   - Accept GET requests with command parameter
   - Execute commands and return output
   - Encode as base64, upload to M83 via ssh-m83
   
3. **START HTTP SERVER ON M83**
   - Upload via ssh-m83 and base64 method
   - Start in background
   - Keep running persistently
   
4. **TEST FROM T630**
   - `curl http://192.168.1.11:8080/cmd?command=whoami`
   - Verify bidirectional command execution works
   
5. **DOCUMENT THE SOLUTION**
   - Save final HTTP API implementation
   - Commit to Fossil
   - Create usage guide

---

## User Feedback on Session Quality

**Issue identified:** Started repeating same explanations (memory degradation)
- Reverse tunnel mechanics explained multiple times
- Memory system structure explained multiple times
- Credential scheme storage options debated in circles
- **Root cause:** Context window approaching limit

**User's directive:** Save and commit this state before continuing further work

---

## Critical Context for Future Sessions

**Remember:**
- All machines: username `aaa` / password `aaa` (except Proxmox: `mjc02840@gmail.com`)
- Ubuntu M83 just rebooted (fresh state)
- HTTP API on port 8080 is the chosen path forward
- Base64 upload method is required (no heredoc)
- Searchable database at `/var/www/html/operational-reference.db` should be searched FIRST
- Context window starts degrading after ~3 hours of dense conversation

**Do NOT repeat:**
- Don't suggest SSH keys (user rejected)
- Don't suggest sshpass for interactive shells (known limitation)
- Don't guess about system capabilities (test first)
- Don't miss the searchable database (search there before guessing)

---

**Document Created:** 2026-05-17 09:39 UTC  
**Session Duration:** May 16-17-18 (if continued), ~14+ hours total  
**Status:** Context window degrading, circling on same decisions  
**Next Action:** Build HTTP API on Ubuntu M83, test, commit

---
