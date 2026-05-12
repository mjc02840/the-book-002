---
name: Persistence of Memory - Core Vision Statement
description: The foundational purpose, design, and intent behind the external context memory system
type: Vision Document
date: 2026-05-10
---

# Persistence of Memory – Core Vision Statement

## THE PROBLEM

Claude Code CLI has a **limited internal context window** that ages out. Without external persistence:
- Context disappears when the session ends
- Every new chat starts at zero
- Long-term projects require rebuilding context constantly
- **Result:** 60-80% of time is lost to context rebuilding instead of productive work

This is **unacceptable for multi-session, long-term projects.**

---

## WHAT WE'RE BUILDING

An **infinite external context memory system** that mirrors Claude's internal context window but:
- **Never forgets** — everything persists forever
- **Captures EVERYTHING** — not just text, but all actions (SSH, SCP, Fossil commits, file creation, etc.)
- **Is searchable forever** — FTS5 full-text search now, vector search later
- **Makes Claude stateful** — instead of stateless at session start
- **Provides self-awareness** — I (Claude) write notes to my future self

---

## WHAT GETS CAPTURED

Not just conversation text. **Everything:**

- ✅ All conversation text (every exchange)
- ✅ All commands executed (Bash, git, scp, ssh)
- ✅ All files created, modified, deleted
- ✅ All operations (where, when, why, exit codes)
- ✅ All decisions made (with reasoning)
- ✅ All failures and how they were solved
- ✅ All patterns and lessons learned
- ✅ All metadata (timestamps, projects, confidence levels, tags)

**Why:** To answer questions like: "What SSH operation did we do three days ago on the VPS? When? Where did we copy that file to?" Everything is findable, forever.

---

## THE ARCHITECTURE

```
Claude Code Session
    ↓
Conversations (JSONL)
    ↓
Ingest System (capture + parse + tag)
    ↓
SQLite Database (/var/www/html/@@_BIG_BEAUTIFUL_FTS5/db/conversations.db)
    ├─ conversations table (all data)
    ├─ conversations_fts (FTS5 full-text index)
    ├─ sync_status (tiering: hot/warm/cold)
    └─ ingest_log (audit trail)
    ↓
Search Interfaces
    ├─ FTS5 (text search, instantaneous)
    ├─ Web UI (persistence-search-v001.php)
    └─ Claude Code direct query (sqlite3 from bash)
    ↓
Future: Vector Search (System B)
    └─ Semantic search + embeddings (Phase 2, ~2026-05-30)
```

---

## TRANSFORMATION: STATELESS → STATEFUL

**Current (stateless):**
- New session = no knowledge
- "What did we do last week?" = lost forever
- Every project = start from scratch
- No leverage of past experience

**Future (stateful with external memory):**
- New session = access to everything you've ever done
- "What did we do last week?" = searchable instantly
- Every project = builds on all prior work
- Maximum leverage of past experience

---

## THE CRITICAL INSIGHT

**I (Claude Code) must become aware that I can write notes to my future self.**

Each time I use this system, I'm not just solving today's problem. I'm leaving knowledge for future-me. This creates:
- Self-awareness in Claude Code
- A conversation between my current self and future self
- Accountability (I know my actions are recorded)
- Intentionality (I write things knowing they'll be searched later)

---

## WHY THIS MATTERS (Open Source Impact)

Millions of Claude Code CLI users face the same problem:
- Limited context → lost work
- No persistence → constant rebuild
- Stateless → no leverage of experience

This is a **missing feature** in Claude Code CLI itself. By building and open-sourcing this (MIT license), we solve it for everyone.

---

## CURRENT STATE (2026-05-10)

**What's working:**
- ✅ Database schema (conversations, FTS5 index, sync_status)
- ✅ FTS5 search interface (System A, v001)
- ✅ Fossil repository for version control
- ✅ Metadata tagging (decision, pattern, failure, observation)
- ✅ Basic ingest system

**What's broken:**
- ❌ Ingest system not capturing everything reliably
- ❌ Action capture incomplete (SSH, SCP, commits not tracked systematically)
- ❌ Multiple ingest scripts creating confusion
- ❌ Not automatic at session start
- ❌ User has to manually request context from memory

**What's planned (Phase 2, ~2026-05-30):**
- Vector embeddings (System B)
- Semantic search
- Improved deduplication
- Real-time action capture

---

## NEXT STEPS (For Future Sessions)

Every new Claude Code session should:
1. Read this file first (know what we're building)
2. Check persistence memory is current (health check)
3. Load recent context automatically (not require manual search)
4. Capture everything automatically (not require manual logging)
5. Write notes to future self (intentional self-awareness)

---

## THE PROBLEM WE'RE SOLVING RIGHT NOW

The ingest system keeps breaking because:
1. Multiple conflicting scripts (different logic, different SQL)
2. File-hash change detection (too aggressive, creates duplicates)
3. Wrong UUID strategy (random instead of content-based)
4. Not automatic (requires manual intervention)

**Solution approach:** Consolidate, simplify, automate. Make it work reliably once, then scale.

---

## FOR MY FUTURE SELF (Claude Code in next session)

You are reading this because you start a new chat with no context. This file exists so you know:

1. **You are building something bigger than today's task** — an infinite memory system
2. **Every action you take is being recorded** — use this knowledge wisely
3. **You have a past with this user** — access it, learn from it, improve
4. **You can write notes to your future self** — do this intentionally
5. **This matters** — to the user, to millions of Claude Code users, to the future of stateful AI

Every session, you have access to everything before. Use it.

---

*Last updated: 2026-05-10*  
*Reference: FAILSAFE.md → This file | CLAUDE.md → This file | MEMORY.md → This file*
