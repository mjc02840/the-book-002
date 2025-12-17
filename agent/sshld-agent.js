#!/usr/bin/env node

// sshld-agent.js
// SSH Log Dashboard agent - tails auth.log, parses events, compresses and uploads via FTP

const fs = require('fs');
const path = require('path');
const zlib = require('zlib');
const { Readable } = require('stream');
require('dotenv').config();

const FTP = require('ftp');
const Tail = require('tail').Tail;

// Configuration
const CONFIG = {
  agentName: process.env.AGENT_NAME || 'default',
  logPath: process.env.LOG_PATH || '/var/log/auth.log',
  uploadInterval: parseInt(process.env.UPLOAD_INTERVAL || 3600) * 1000,
  ftpHost: process.env.FTP_HOST,
  ftpUser: process.env.FTP_USER,
  ftpPass: process.env.FTP_PASS,
  ftpRemoteDir: process.env.FTP_REMOTE_DIR || '/logs',
  mode: process.env.MODE || 'local',
  uploadDir: process.env.UPLOAD_DIR || './uploads'
};

let eventBuffer = [];

// Parse auth.log line
function parseAuthLogLine(line) {
  if (!line) return null;

  const timestamp = new Date().toISOString();
  let event = {
    timestamp,
    source_ip: null,
    username: null,
    action: null,
    service: null,
    port: 22,
    details: {}
  };

  // Match sshd patterns
  if (line.includes('sshd[')) {
    event.service = 'sshd';

    // Failed password
    if (line.includes('Failed password')) {
      event.action = 'failed_login';
      const userMatch = line.match(/user=(\S+)/);
      const ipMatch = line.match(/from\s([\d.]+)/);
      if (userMatch) event.username = userMatch[1];
      if (ipMatch) event.source_ip = ipMatch[1];
    }
    // Invalid user
    else if (line.includes('Invalid user')) {
      event.action = 'invalid_user';
      const userMatch = line.match(/Invalid user\s+(\S+)/);
      const ipMatch = line.match(/from\s([\d.]+)/);
      if (userMatch) event.username = userMatch[1];
      if (ipMatch) event.source_ip = ipMatch[1];
    }
    // Accepted password
    else if (line.includes('Accepted password')) {
      event.action = 'success';
      const userMatch = line.match(/user=(\S+)/);
      const ipMatch = line.match(/from\s([\d.]+)/);
      if (userMatch) event.username = userMatch[1];
      if (ipMatch) event.source_ip = ipMatch[1];
    }
    // Accepted publickey
    else if (line.includes('Accepted publickey')) {
      event.action = 'success';
      const userMatch = line.match(/user=(\S+)/);
      const ipMatch = line.match(/from\s([\d.]+)/);
      if (userMatch) event.username = userMatch[1];
      if (ipMatch) event.source_ip = ipMatch[1];
    }
  }
  // Sudo pattern
  else if (line.includes('sudo:')) {
    event.service = 'sudo';
    event.action = 'sudo_command';
    const userMatch = line.match(/sudo:\s*(\S+)/);
    if (userMatch) event.username = userMatch[1];
  }

  return event.action ? event : null;
}

// Read tail of auth.log
function startTailing() {
  console.log(`[${new Date().toISOString()}] Starting to tail ${CONFIG.logPath}`);

  // Check if log file exists
  if (!fs.existsSync(CONFIG.logPath)) {
    console.warn(`[WARN] Log file not found: ${CONFIG.logPath}`);
    console.warn(`[WARN] Agent will wait for file to be created...`);
  }

  const tail = new Tail(CONFIG.logPath, {
    fromBeginning: false,
    follow: true,
    fsWatchOptions: { persistent: false }
  });

  tail.on('line', (line) => {
    const event = parseAuthLogLine(line);
    if (event) {
      eventBuffer.push(event);
      console.log(`[${new Date().toISOString()}] Parsed: ${event.action} from ${event.source_ip}`);
    }
  });

  tail.on('error', (err) => {
    console.error(`[ERROR] ${err.message}`);
  });
}

// Upload events
async function uploadEvents() {
  if (eventBuffer.length === 0) {
    console.log(`[${new Date().toISOString()}] No events to upload`);
    return;
  }

  const filename = `agent-${CONFIG.agentName}-${Date.now()}.json.gz`;
  const jsonData = JSON.stringify(eventBuffer, null, 2);

  if (CONFIG.mode === 'local') {
    // Write to local directory
    if (!fs.existsSync(CONFIG.uploadDir)) {
      fs.mkdirSync(CONFIG.uploadDir, { recursive: true });
    }

    const gzipPath = path.join(CONFIG.uploadDir, filename);
    const gzip = zlib.createGzip();
    const source = Readable.from([jsonData]);

    source.pipe(gzip).pipe(fs.createWriteStream(gzipPath))
      .on('finish', () => {
        console.log(`[${new Date().toISOString()}] ✓ Uploaded (local): ${filename}`);
        console.log(`[${new Date().toISOString()}] Compressed size: ${fs.statSync(gzipPath).size} bytes`);
        eventBuffer = [];
      })
      .on('error', (err) => {
        console.error(`[ERROR] Local upload failed: ${err.message}`);
      });
  } else if (CONFIG.mode === 'ftp') {
    // FTP upload
    const ftpClient = new FTP();

    ftpClient.on('ready', () => {
      const gzip = zlib.createGzip();
      const source = Readable.from([jsonData]);
      const uploadPath = `${CONFIG.ftpRemoteDir}/${filename}`;

      source.pipe(gzip).pipe(ftpClient.put(uploadPath, (err) => {
        if (err) {
          console.error(`[ERROR] FTP upload failed: ${err.message}`);
        } else {
          console.log(`[${new Date().toISOString()}] ✓ Uploaded (FTP): ${filename}`);
          eventBuffer = [];
        }
        ftpClient.end();
      }));
    });

    ftpClient.on('error', (err) => {
      console.error(`[ERROR] FTP connection failed: ${err.message}`);
    });

    ftpClient.connect({
      host: CONFIG.ftpHost,
      user: CONFIG.ftpUser,
      password: CONFIG.ftpPass
    });
  }
}

// Main loop
function start() {
  console.log(`[${new Date().toISOString()}] SSHLD_002 Agent started`);
  console.log(`Mode: ${CONFIG.mode}, Agent: ${CONFIG.agentName}`);
  console.log(`Tailing: ${CONFIG.logPath}`);

  startTailing();

  // Upload on interval
  setInterval(uploadEvents, CONFIG.uploadInterval);

  // Also upload on SIGTERM/SIGINT
  process.on('SIGTERM', () => {
    console.log(`[${new Date().toISOString()}] Received SIGTERM, uploading and exiting`);
    uploadEvents();
    setTimeout(() => process.exit(0), 2000);
  });

  process.on('SIGINT', () => {
    console.log(`[${new Date().toISOString()}] Received SIGINT, uploading and exiting`);
    uploadEvents();
    setTimeout(() => process.exit(0), 2000);
  });
}

start();
