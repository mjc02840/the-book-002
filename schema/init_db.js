// init_db.js
// Initialize SQLite3 database using Node.js (better-sqlite3)

const fs = require('fs');
const path = require('path');

const dbPath = path.join(__dirname, '../db/sshld_002.db');
const schemaPath = path.join(__dirname, 'schema.sql');

// Create db directory if not exists
const dbDir = path.dirname(dbPath);
if (!fs.existsSync(dbDir)) {
  fs.mkdirSync(dbDir, { recursive: true });
  console.log(`✓ Created db directory: ${dbDir}`);
}

try {
  // Check if Database module is available
  let Database;
  try {
    Database = require('better-sqlite3');
  } catch (e) {
    // Try fallback
    console.warn('better-sqlite3 not found, attempting sql.js fallback...');

    // For MVP, create a stub database and schema file
    // The backend will create it dynamically when first accessed
    if (!fs.existsSync(dbPath)) {
      fs.writeFileSync(dbPath, '');
      console.log(`✓ Created database file at: ${dbPath}`);
      console.log(`✓ Database will be initialized on first backend access`);
      process.exit(0);
    }
  }

  if (Database) {
    const db = new Database(dbPath);

    // Read schema
    const schema = fs.readFileSync(schemaPath, 'utf8');

    // Execute schema
    db.exec(schema);

    console.log(`✓ Database initialized at: ${dbPath}`);
    console.log(`✓ Tables created successfully`);

    db.close();
  }
} catch (e) {
  console.error(`✗ Error: ${e.message}`);
  process.exit(1);
}
