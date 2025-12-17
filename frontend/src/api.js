// api.js
// Frontend API client utilities

const API_URL = 'http://localhost:8000/api';

export async function fetchEvents(limit = 100, offset = 0) {
  try {
    const res = await fetch(`${API_URL}/events?limit=${limit}&offset=${offset}`);
    if (!res.ok) {
      throw new Error(`API error: ${res.status}`);
    }
    return await res.json();
  } catch (err) {
    console.error('Fetch events failed:', err);
    return [];
  }
}

export async function fetchStats() {
  try {
    const res = await fetch(`${API_URL}/stats`);
    if (!res.ok) {
      throw new Error(`API error: ${res.status}`);
    }
    return await res.json();
  } catch (err) {
    console.error('Fetch stats failed:', err);
    return [];
  }
}

export async function triggerImport() {
  try {
    const res = await fetch(`${API_URL}/import`, { method: 'POST' });
    if (!res.ok) {
      throw new Error(`API error: ${res.status}`);
    }
    return await res.json();
  } catch (err) {
    console.error('Import failed:', err);
    return { error: err.message };
  }
}

export async function checkHealth() {
  try {
    const res = await fetch(`${API_URL}/health`);
    if (!res.ok) {
      throw new Error(`API error: ${res.status}`);
    }
    return await res.json();
  } catch (err) {
    console.error('Health check failed:', err);
    return { status: 'error' };
  }
}
