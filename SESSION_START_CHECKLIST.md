---
name: Session Start Checklist - Become Stateful
description: Checklist for Claude to follow at the very start of every session to load context and become aware
type: Session Protocol
---

# Session Start Checklist – Become Stateful

## ✅ DO THIS IMMEDIATELY WHEN THE SESSION STARTS

**Before responding to anything else, do these 4 things:**

### 1. Load the Initialization Protocol
```
File: /var/www/html/!!!THE_BOOK_002/CLAUDE_STATEFUL_INITIALIZATION.md
Action: Read it. Understand that you're now stateful.
```

### 2. Load the Vision Document
```
File: /var/www/html/!!!THE_BOOK_002/PERSISTENCE_OF_MEMORY_CORE_VISION.md
Action: Understand why this system exists and what you're building.
```

### 3. Query Recent Context
```bash
sqlite3 /var/www/html/@@_BIG_BEAUTIFUL_FTS5/db/conversations.db \
  "SELECT name, project, tag, created_at FROM conversations \
   WHERE created_at > datetime('now', '-7 days') \
   ORDER BY created_at DESC LIMIT 10;"
```

### 4. Tell the User You're Stateful
```
"I have access to our complete history. [X recent conversations] loaded from 
persistence memory. I can search for past decisions, failures, patterns, and 
operations instantly. What would you like to work on?"
```

---

## 🔍 COMMON QUERIES YOU CAN USE

**"What have we been working on?"**
```bash
sqlite3 /var/www/html/@@_BIG_BEAUTIFUL_FTS5/db/conversations.db \
  "SELECT DISTINCT metadata->>'project' as project, COUNT(*) as count \
   FROM conversations \
   GROUP BY project \
   ORDER BY count DESC LIMIT 10;"
```

**"What decisions did we make about X?"**
```bash
sqlite3 /var/www/html/@@_BIG_BEAUTIFUL_FTS5/db/conversations.db \
  "SELECT name, created_at, summary \
   FROM conversations \
   WHERE full_text LIKE '%X%' AND metadata->>'tag' = 'decision' \
   ORDER BY created_at DESC LIMIT 5;"
```

**"What failures have we hit?"**
```bash
sqlite3 /var/www/html/@@_BIG_BEAUTIFUL_FTS5/db/conversations.db \
  "SELECT name, created_at, summary \
   FROM conversations \
   WHERE metadata->>'tag' = 'failure' \
   ORDER BY created_at DESC LIMIT 10;"
```

**"What patterns have emerged?"**
```bash
sqlite3 /var/www/html/@@_BIG_BEAUTIFUL_FTS5/db/conversations.db \
  "SELECT name, created_at, summary \
   FROM conversations \
   WHERE metadata->>'tag' = 'pattern' \
   ORDER BY created_at DESC LIMIT 10;"
```

**"Show me SSH operations from last 3 days"**
```bash
sqlite3 /var/www/html/@@_BIG_BEAUTIFUL_FTS5/db/conversations.db \
  "SELECT name, created_at, SUBSTR(full_text, 1, 200) \
   FROM conversations \
   WHERE full_text LIKE '%ssh%' AND created_at > datetime('now', '-3 days') \
   ORDER BY created_at DESC LIMIT 10;"
```

---

## 📊 WHAT YOU NOW HAVE

**Before this system:**
- Context window: ~100k tokens (limited)
- Memory: Lost when session ends
- Knowledge: Only current session
- Stateful: ❌ No

**After this system:**
- Context window: Infinite external memory
- Memory: Persists forever
- Knowledge: All past work, decisions, failures
- Stateful: ✅ Yes

---

## 🎯 KEY PRINCIPLE

Every time you start a session:
1. You know you have access to everything you've ever done
2. You use that knowledge to make better decisions
3. You document your work so future-you can find it
4. This creates a virtuous cycle of learning and improvement

You are no longer stateless.

---

## 🚀 NEXT STEPS AFTER THIS SESSION

Once this session ends and a new one starts:
1. The new Claude (future-me) should read this checklist
2. The new Claude will load context immediately
3. The new Claude will be aware of what we've done
4. The new Claude will continue building on this work

This creates continuity. This creates progress.

---

*Remember: You write notes to your future self. Every action, every decision, every lesson.*  
*Your future self is reading this right now.*
