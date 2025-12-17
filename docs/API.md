# SSHLD_002 API Reference

Complete REST API documentation for SSHLD_002 backend.

## Base URL

- **Local Development**: `http://localhost:8000`
- **OVH Production**: `https://yourdomain.com/sshld_002`

## Authentication

Currently no authentication required (MVP Phase 1). API keys will be added in Phase 2.

## Response Format

All responses are JSON.

### Success Response
```json
{
  "status": "ok",
  "data": { ... }
}
```

### Error Response
```json
{
  "error": "Error description"
}
```

## Endpoints

### GET /api/events

Fetch recent authentication events.

**Query Parameters**:
- `limit` (int, default 100): Max events to return
- `offset` (int, default 0): Pagination offset

**Example**:
```bash
curl "http://localhost:8000/api/events?limit=50&offset=0"
```

**Response** (200 OK):
```json
[
  {
    "id": 1,
    "account_id": "default",
    "timestamp": "2025-12-17T10:30:45.123Z",
    "source_ip": "192.168.1.100",
    "username": "root",
    "action": "failed_login",
    "service": "sshd",
    "port": 22,
    "details": {},
    "created_at": "2025-12-17T10:30:45Z"
  },
  {
    "id": 2,
    "account_id": "default",
    "timestamp": "2025-12-17T10:31:12.456Z",
    "source_ip": "192.168.1.101",
    "username": "ubuntu",
    "action": "success",
    "service": "sshd",
    "port": 22,
    "details": {},
    "created_at": "2025-12-17T10:31:12Z"
  }
]
```

**Field Descriptions**:
- `id`: Unique event identifier
- `account_id`: Account/organization identifier (for multi-tenancy)
- `timestamp`: When the event occurred (ISO 8601)
- `source_ip`: Source IP address
- `username`: SSH username attempt
- `action`: Event type (see below)
- `service`: Service that logged the event
- `port`: Port number (default 22 for SSH)
- `details`: Additional metadata as JSON object
- `created_at`: When the event was recorded in database

**Actions**:
- `failed_login`: Failed password authentication
- `invalid_user`: User does not exist
- `success`: Successful authentication (password or pubkey)
- `sudo_command`: sudo command execution

---

### GET /api/stats

Fetch daily aggregated statistics.

**Query Parameters**: None

**Example**:
```bash
curl "http://localhost:8000/api/stats"
```

**Response** (200 OK):
```json
[
  {
    "id": 1,
    "account_id": "default",
    "stat_date": "2025-12-17",
    "failed_attempts": 42,
    "successful_logins": 8,
    "unique_ips": 15,
    "top_attacker_ip": "203.0.113.45",
    "top_attacker_count": 12,
    "risk_score": 65,
    "created_at": "2025-12-17T10:30:45Z",
    "updated_at": "2025-12-17T10:30:45Z"
  }
]
```

**Field Descriptions**:
- `stat_date`: Date of statistics (YYYY-MM-DD)
- `failed_attempts`: Total failed login attempts
- `successful_logins`: Total successful authentications
- `unique_ips`: Count of unique source IPs
- `top_attacker_ip`: IP with most failed attempts
- `top_attacker_count`: Count of attempts from top attacker
- `risk_score`: Overall risk rating (0-100)

**Risk Score Calculation**:
```
base_score = 0
+ (failed_attempts / 50) * 20    // Up to 20 pts
+ (unique_ips / 100) * 30        // Up to 30 pts
+ (brute_force_detected ? 30 : 0)  // Future
+ (root_attempts ? 10 : 0)         // Future
+ (suspicious_country ? 10 : 0)    // Future
= risk_score (capped at 100)
```

---

### POST /api/import

Trigger import of uploaded event files from agent.

**Query Parameters**: None

**Example**:
```bash
curl -X POST "http://localhost:8000/api/import"
```

**Request Body**: Empty (no body required)

**Response** (200 OK):
```json
{
  "processed": 2,
  "failed": 0,
  "message": "Import completed"
}
```

**Response Fields**:
- `processed`: Number of files successfully imported
- `failed`: Number of files that failed to import
- `message`: Status message (optional)

**What it does**:
1. Checks `FTP_UPLOAD_DIR` (default: `./uploads/`) for `.json.gz` files
2. Decompresses each file
3. Parses JSON event array
4. Inserts events into `events` table
5. Generates/updates daily stats
6. Deletes processed files

---

### GET /api/health

Health check endpoint.

**Query Parameters**: None

**Example**:
```bash
curl "http://localhost:8000/api/health"
```

**Response** (200 OK):
```json
{
  "status": "ok",
  "timestamp": "2025-12-17T10:30:45Z"
}
```

---

## Agent Upload Format

Agents upload compressed event files in this format:

**Filename**: `agent-{name}-{timestamp}.json.gz`
- Example: `agent-t630-default-1702800645123.json.gz`

**Decompressed JSON Structure**:
```json
[
  {
    "timestamp": "2025-12-17T10:30:45.123Z",
    "source_ip": "192.168.1.100",
    "username": "root",
    "action": "failed_login",
    "service": "sshd",
    "port": 22,
    "details": {
      "attempt": 1,
      "method": "password"
    }
  },
  {
    "timestamp": "2025-12-17T10:31:12.456Z",
    "source_ip": "192.168.1.101",
    "username": "ubuntu",
    "action": "success",
    "service": "sshd",
    "port": 22,
    "details": {
      "method": "publickey",
      "key_id": "..."
    }
  }
]
```

**Requirements**:
- Must be JSON array (not single object)
- Each event must have `timestamp`, `source_ip`, `action`
- `username` can be null for invalid_user actions
- `details` is optional (empty object `{}` is fine)

---

## Error Responses

### 404 Not Found
```json
{
  "error": "Not found"
}
```

### 500 Internal Server Error
```json
{
  "error": "Database error: database is locked"
}
```

---

## Rate Limiting

No rate limiting in MVP Phase 1. Will be added in Phase 2.

---

## CORS Headers

All endpoints return these headers:

```
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, OPTIONS
Access-Control-Allow-Headers: Content-Type
```

Allows requests from any origin (relaxed for MVP).

---

## Example Usage

### JavaScript/Frontend

```javascript
// Fetch recent events
const response = await fetch('http://localhost:8000/api/events?limit=50');
const events = await response.json();

// Get statistics
const statsRes = await fetch('http://localhost:8000/api/stats');
const stats = await statsRes.json();

// Trigger import
const importRes = await fetch('http://localhost:8000/api/import', {
  method: 'POST'
});
const result = await importRes.json();
console.log(`Processed: ${result.processed}, Failed: ${result.failed}`);
```

### Bash/cURL

```bash
# Fetch events
curl http://localhost:8000/api/events

# Get stats
curl http://localhost:8000/api/stats

# Trigger import
curl -X POST http://localhost:8000/api/import

# Check health
curl http://localhost:8000/api/health
```

### Python

```python
import requests
import json

# Get events
events = requests.get('http://localhost:8000/api/events').json()
print(f"Found {len(events)} events")

# Get stats
stats = requests.get('http://localhost:8000/api/stats').json()
print(f"Risk score: {stats[0]['risk_score']}")

# Trigger import
result = requests.post('http://localhost:8000/api/import').json()
print(f"Imported {result['processed']} files")
```

---

## Future Enhancements (Phase 2)

- API key authentication
- Rate limiting per account
- Filtering by date range
- IP geolocation data in responses
- Threat detection results
- Bulk operations
- Webhook support for alerts

---

## Database Schema Reference

For advanced usage, the backend uses these tables:

**events**:
- `id` (INTEGER PRIMARY KEY)
- `account_id` (TEXT)
- `timestamp` (DATETIME)
- `source_ip` (TEXT)
- `username` (TEXT)
- `action` (TEXT)
- `service` (TEXT)
- `port` (INTEGER)
- `details` (JSON)
- `created_at` (DATETIME)

**daily_stats**:
- `id` (INTEGER PRIMARY KEY)
- `account_id` (TEXT)
- `stat_date` (DATE)
- `failed_attempts` (INTEGER)
- `successful_logins` (INTEGER)
- `unique_ips` (INTEGER)
- `top_attacker_ip` (TEXT)
- `top_attacker_count` (INTEGER)
- `risk_score` (INTEGER)
- `created_at` (DATETIME)
- `updated_at` (DATETIME)

See `schema/schema.sql` for complete schema.

---

## Support

For issues or questions, see:
- [SETUP.md](../SETUP.md) - Local development troubleshooting
- [DEPLOY.md](../DEPLOY.md) - OVH deployment help
- [README.md](../README.md) - Project overview
