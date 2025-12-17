-- schema.sql
-- SSHLD_002 SQLite3 Schema
-- Core tables for SSH log aggregation

-- Raw authentication events
CREATE TABLE IF NOT EXISTS events (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  account_id TEXT NOT NULL DEFAULT 'default',
  timestamp DATETIME NOT NULL,
  source_ip TEXT NOT NULL,
  username TEXT,
  action TEXT,              -- 'failed_login', 'success', 'sudo', 'invalid_user'
  service TEXT,             -- 'sshd', 'sudo', 'systemd-logind'
  port INTEGER DEFAULT 22,
  details JSON,             -- Extra metadata as JSON
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Daily aggregated statistics
CREATE TABLE IF NOT EXISTS daily_stats (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  account_id TEXT NOT NULL DEFAULT 'default',
  stat_date DATE NOT NULL,
  failed_attempts INTEGER DEFAULT 0,
  successful_logins INTEGER DEFAULT 0,
  unique_ips INTEGER DEFAULT 0,
  top_attacker_ip TEXT,
  top_attacker_count INTEGER DEFAULT 0,
  risk_score INTEGER DEFAULT 0,  -- 0-100
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- IP geolocation cache
CREATE TABLE IF NOT EXISTS ip_cache (
  ip TEXT PRIMARY KEY,
  country TEXT,
  city TEXT,
  latitude REAL,
  longitude REAL,
  last_seen DATETIME,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Users table (for future multi-tenancy)
CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  account_id TEXT UNIQUE NOT NULL,
  email TEXT UNIQUE,
  api_key TEXT UNIQUE,
  tier TEXT DEFAULT 'free',  -- 'free', 'pro', 'enterprise'
  max_servers INTEGER DEFAULT 1,
  max_retention_days INTEGER DEFAULT 7,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Agents registered (future)
CREATE TABLE IF NOT EXISTS agents (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  account_id TEXT NOT NULL,
  agent_name TEXT NOT NULL,
  api_key TEXT UNIQUE,
  last_seen DATETIME,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Indexes for performance
CREATE INDEX IF NOT EXISTS idx_events_account_timestamp ON events(account_id, timestamp DESC);
CREATE INDEX IF NOT EXISTS idx_events_source_ip ON events(source_ip);
CREATE INDEX IF NOT EXISTS idx_events_username ON events(username);
CREATE INDEX IF NOT EXISTS idx_daily_stats_account_date ON daily_stats(account_id, stat_date DESC);
CREATE INDEX IF NOT EXISTS idx_ip_cache_country ON ip_cache(country);
