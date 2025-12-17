// Dashboard.jsx
// Main dashboard component with stats and event table

import { useState, useEffect } from 'react';
import { fetchEvents, fetchStats, triggerImport, checkHealth } from '../api';
import '../styles/dashboard.css';

export default function Dashboard() {
  const [events, setEvents] = useState([]);
  const [stats, setStats] = useState([]);
  const [loading, setLoading] = useState(false);
  const [apiStatus, setApiStatus] = useState('checking');
  const [importMessage, setImportMessage] = useState('');

  useEffect(() => {
    checkApiHealth();
    loadData();
    const interval = setInterval(loadData, 5000);
    return () => clearInterval(interval);
  }, []);

  async function checkApiHealth() {
    const health = await checkHealth();
    setApiStatus(health.status === 'ok' ? 'connected' : 'disconnected');
  }

  async function loadData() {
    setLoading(true);
    const [eventsData, statsData] = await Promise.all([
      fetchEvents(50),
      fetchStats()
    ]);
    setEvents(eventsData);
    setStats(statsData);
    setLoading(false);
  }

  async function handleImport() {
    setImportMessage('Importing...');
    const result = await triggerImport();
    setImportMessage(`Processed: ${result.processed}, Failed: ${result.failed}`);
    setTimeout(() => {
      loadData();
      setImportMessage('');
    }, 1000);
  }

  const todayStats = stats[0] || {};
  const failedCount = todayStats.failed_attempts || 0;
  const successCount = todayStats.successful_logins || 0;
  const riskScore = todayStats.risk_score || 0;

  return (
    <div className="dashboard">
      <header className="dashboard-header">
        <h1>SSHLD_002 Dashboard</h1>
        <div className="header-controls">
          <span className={`status-indicator ${apiStatus}`}>
            {apiStatus === 'connected' ? '● Connected' : '● Disconnected'}
          </span>
          <button onClick={handleImport} disabled={loading} className="import-btn">
            {loading ? 'Loading...' : 'Import Now'}
          </button>
        </div>
      </header>

      {importMessage && <div className="import-message">{importMessage}</div>}

      <div className="stats-grid">
        <div className="stat-card">
          <h2>Failed Logins</h2>
          <div className="stat-value failed">{failedCount}</div>
        </div>
        <div className="stat-card">
          <h2>Successful</h2>
          <div className="stat-value success">{successCount}</div>
        </div>
        <div className="stat-card">
          <h2>Risk Score</h2>
          <div className={`stat-value risk-${riskScore >= 50 ? 'high' : 'low'}`}>{riskScore}</div>
        </div>
        <div className="stat-card">
          <h2>Unique IPs</h2>
          <div className="stat-value">{todayStats.unique_ips || 0}</div>
        </div>
      </div>

      <section className="events-section">
        <h2>Recent Events</h2>
        {events.length === 0 ? (
          <p className="no-events">No events yet. Click "Import Now" to load data.</p>
        ) : (
          <table className="events-table">
            <thead>
              <tr>
                <th>Timestamp</th>
                <th>IP</th>
                <th>User</th>
                <th>Action</th>
                <th>Service</th>
              </tr>
            </thead>
            <tbody>
              {events.map((evt, idx) => (
                <tr key={idx} className={`action-row action-${evt.action}`}>
                  <td>{new Date(evt.timestamp).toLocaleString()}</td>
                  <td className="ip-cell">{evt.source_ip || '-'}</td>
                  <td>{evt.username || '-'}</td>
                  <td><span className={`badge badge-${evt.action}`}>{evt.action}</span></td>
                  <td>{evt.service || '-'}</td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </section>
    </div>
  );
}
