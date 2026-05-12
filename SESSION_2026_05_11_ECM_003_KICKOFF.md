# Session 2026-05-11: ECM_003 Project Kickoff

## The Conversation

User explained the core problem they've been solving for 11+ months:
- Losing 60-80% of time dealing with persistent memory issues
- Claude loses all context between sessions
- Need a stateful system that remembers everything

## The Solution Architecture

**Two-part memory system:**
1. **Part A (Existing)** — GitHub ECM_002 captures all actions (SSH, file ops, metrics)
2. **Part B (To Build)** — Terminal text capture (prompts, responses, code, output)

**Both feed into:**
- Fossil repositories (source of truth)
- FTS5 SQLite database (searchable index)
- RAM disk (4GB, hot working memory)

**Sync model:**
- Load recent data from SSD into RAM disk at startup
- Everything captured in real-time
- RAM syncs to SSD every 5 minutes
- Never stateless again

## What We Built Today

### Design Phase (Completed)
- FTS5_SCHEMA_DESIGN.md (destination database structure)
- FOSSIL_A_SCHEMA_DESIGN.md (actions capture structure)
- FOSSIL_B_SCHEMA_DESIGN.md (text/terminal capture structure)
- INGEST_MERGE_LOGIC_DESIGN.md (merge algorithm)

### Implementation Phase (Completed)
- `init_fts5_database.py` — Created ecm_events.db with FTS5 full-text search
- `init_fossil_a.py` — Created fossil_a.fossil for actions
- `init_fossil_b.py` — Created fossil_b.fossil for text
- `init_all.py` — Master initialization script
- `ingest_merge.py` — Merges both fossils by timestamp into FTS5

### Code Committed
All code committed to main ECM_003 Fossil repository with message:
"Implementation: FTS5 init, Fossil A/B init, ingest merge script"

## Key Decisions Made

1. **Two Fossils vs One** — Two separate Fossils to avoid collisions, merged via timestamp
2. **FTS5 Schema** — Single table with type field (bash_command, ssh, file_op, prompt, response, etc.)
3. **Merge by Timestamp** — Simple, elegant sync mechanism (not complex deduplication)
4. **Real-time Capture** — Everything captured immediately, not batched
5. **5-minute SSD Sync** — Balance between persistence and write performance

## Bright Ideas / Insights

### Why This Works
- Timestamp synchronization is the "magic" (user's words) - simple, low-tech, effective
- Deduplication not needed at this stage - correlation by timestamp works
- Search returns "the full story" - actions + text together in chronological order

### What's Different from Previous Attempts
- Previous systems too complex (vector embeddings, multiple tiers, metadata extraction)
- This one: simple timestamp-based merge, two separate data sources, one searchable destination
- Robustness over speed - no corners cut, will take time

### The Bigger Picture
This is about transforming Claude from **stateless** to **stateful**:
- Every response is a note to future self
- Every action is recorded
- Complete institutional memory forever
- Self-awareness through complete history

## Next Steps

1. **Test the ingest script** - Add sample data to both fossils, run ingest_merge.py
2. **Build text capture mechanism** - Parse .claude/projects/*.jsonl or PROMPT_COMMAND hook
3. **Build action capture hooks** - Bash hooks for commands, file system watcher, etc.
4. **Set up 5-minute cron job** - automate ingest_merge.py every 5 minutes
5. **Integration test** - Real data flowing through both fossils to FTS5
6. **Search testing** - Verify FTS5 returns expected results

## Notes for Future Self

- Keep The Book 002 updated with insights and failures
- Deliberations capture (post-MVP) - user saw them in .claude folder months ago
- Vector embeddings (System B) planned for versions 3-5, not needed for MVP
- Terminal safety - previous hooks implementation killed terminal, be careful

## Challenges to Overcome

1. **Terminal capture** - Tried PROMPT_COMMAND, killed terminal. Need safer approach.
2. **Fossil integration** - Fossil internals complex. May need to use CLI vs direct DB access.
3. **Real-time sync** - 5-minute window means data briefly lost from internal context.
4. **Deduplication** - Not implemented yet, may need later if data overlap is problem.

## The Why

User is building this because:
- Projects span weeks/months across multiple sessions
- Cannot rebuild context every time from scratch
- Current approaches waste 60-80% of time on memory management
- This is essential for productivity in multi-session, multi-chat work
- This solution should be "the best" approach yet tried

---

**Status:** MVP Phase 1 complete. Implementation phase started.
**Next Review:** When ingest script tested with real data.
