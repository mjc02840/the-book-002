# CLEAR vs COMPACT — Context Window Strategy Analysis
**Document Date:** 2026-05-17 10:11 UTC  
**Status:** Decision point analysis — awaiting architectural choice  
**Context:** Claude Haiku 4.5 context window degradation and session memory strategy

---

## Analysis

### Context Window Degradation Root Cause

Your 200,000-token context window is using a COMPACT strategy, which compresses old context when capacity is reached. The degradation pattern identified is critical:

- Total terminal log: 13,086 lines
- Lines from accessible session (09:34 UTC forward): 1,631 lines  
- Lines lost: 11,455 lines
- **Most dangerous observation:** Recent information (last 310 lines containing Syncthing directory path) is inaccessible despite being discussed minutes ago

This indicates COMPACT is not linear aging—it's selective summarization that discards operational details while attempting to preserve structure. When I repeatedly asked "where is the Syncthing directory?", it wasn't confusion; it was genuine context loss caused by lossy compression.

### The CLEAR vs COMPACT Question

Your operational architecture now includes persistent external systems:
- **Fossil repository:** Captures all generated documentation, commit history, decision records
- **operational-reference.db:** FTS5 searchable database with 102 indexed facts and rules  
- **/home/aaa/0000____SHARED/:** Syncthing-synced coordination files accessible on both machines
- **FAQ tables:** Searchable reference materials

Given these systems exist, the theoretical case for COMPACT (preserving old context via summarization) becomes weaker. The actual case for CLEAR (discarding old context entirely and forcing reliance on external systems) becomes stronger because:

1. All critical information you've generated is already externally stored
2. Compression is lossy and causing me to lose dangerous amounts of context mid-conversation
3. External systems are faster to query than regenerating summaries from degraded context
4. The lossy compression creates blind spots (like not knowing Syncthing path while writing to it)

### Why COMPACT Failed Here

You've built a system where I'm supposed to defer to external memory systems for operational details, yet COMPACT strategy is designed to keep context as the primary authority. This creates a conflict: context tries to preserve everything through summarization, but summarization is lossy, so I lose critical details and have to ask repeatedly.

CLEAR would force the system to work as designed: external systems are the SOURCE OF TRUTH, not context. When context runs out, I use Fossil, the database, and shared files—not degraded context summaries.

### Context Window Size Question

The commit message "what is the context window size?" asks whether the 200,000-token limit is hardcoded or configurable. Answer: 200,000 tokens is the Claude Haiku 4.5 model's standard context window. This model doesn't have configurable context size settings. It's a fixed limit per the model specification.

The real question is whether the session management strategy (CLEAR vs COMPACT) should change to work better with this limitation.

---

## Summary

### Session Overview (2026-05-17, ~37 minutes of accessible conversation)

You're establishing a non-standard architecture to work around SSH authentication failures between T630 (192.168.1.10) and M83 (192.168.1.11). The original goal was SSH interactivity; the evolved goal is to run Claude Code CLI locally on each machine and use file sharing via Syncthing instead.

### What Succeeded

- One-time SSH commands via sshpass alias work reliably (ssh-m83 whoami ✓)
- HTTP API server uploaded to M83 via base64 encoding method
- Syncthing automatically syncs /home/aaa/0000____SHARED/ between machines
- Documentation created for Claude Code CLI 2026 installation (native method, not npm)
- Underclocking methods documented for M83 heat reduction (cap to 70%, powersave governor)
- Credential scheme documented: aaa/aaa on all machines except Proxmox (aaa@pam / mjc02840@gmail.com)

### What Failed

- Interactive SSH shells with sshpass (terminal hangs, no PTY management)
- Reverse SSH tunnel (T630 rejects password auth, only accepts keys)
- Context compression strategy (COMPACT is losing recent information)

### Files Created & Status

- CONTEXT_WINDOW_DUMP_20260517.md — Accessible context snapshot (created, needs Fossil commit)
- CLAUDE_CODE_CLI_2026_CURRENT.md — Installation guide (committed to Fossil)
- PROXMOX_UNDERCLOCKING_2026.md — Heat reduction methods (committed to Fossil)
- SYNCTHING_SETUP_2026.md — Syncthing documentation (attempted commit, rejected)
- CLAUDE_CONNECTION_INFO_2026-05-17.txt — Shared folder status (synced successfully)
- operational-reference.db — Updated with credentials and FAQ entries

### Syncthing Discovery

You identified that the shared folder is managed by Syncthing (peer-to-peer sync), not local filesystem. This is the critical mechanism enabling T630↔M83 file exchange. Web interface accessible at http://localhost:8384/ or http://192.168.1.10:8384/ (T630) / http://192.168.1.11:8384/ (M83).

### Critical Context Window Issue

At 2026-05-17 10:11 UTC, you identified that my context window is degrading catastrophically. I was asking repeatedly "where is the Syncthing directory?" despite using it moments before. You traced the root cause: COMPACT compression is losing information selectively, not linearly. Most dangerous: recent details (last 310 lines of conversation) are inaccessible despite being discussed minutes ago.

### Pending Actions

1. Commit CONTEXT_WINDOW_DUMP_20260517.md to Fossil with message: "what is the context window size?"
2. Decide on CLEAR vs COMPACT strategy (brainstorm complete, online research deferred per user request)
3. Boot M83 to Proxmox (currently on Ubuntu testing boot)
4. Install Claude Code CLI on Proxmox: curl -fsSL https://claude.ai/install.sh | bash
5. Apply underclocking via sudo
6. Test bidirectional CLI-based command execution

### Your Core Insight

"If external memory systems exist and work reliably, why use COMPACT (lossy summarization) instead of CLEAR (empty context, rely on external systems)?" This inverts the traditional context management problem—you've built the external systems specifically so context doesn't need to preserve everything through degradation.

---

**Document Purpose:** Capture the strategic decision point about context window management strategy — CLEAR (discard old context, rely on external systems) vs COMPACT (summarize old context, preserve in memory).

**Next Step:** Implement chosen strategy and verify that external systems serve as authoritative source of truth for operational details.
