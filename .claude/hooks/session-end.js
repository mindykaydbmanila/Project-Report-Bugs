#!/usr/bin/env node
// Reads recent changelog entries and writes a session summary markdown file.

const fs = require('fs');
const path = require('path');

const memDir = path.join(__dirname, '../memory');
const logPath = path.join(memDir, 'changelog.jsonl');
const sessionsDir = path.join(memDir, 'sessions');

if (!fs.existsSync(logPath)) process.exit(0);

const today = new Date().toISOString().slice(0, 10);
const sessionFile = path.join(sessionsDir, `${today}.md`);

// Read last 50 changelog entries
const lines = fs.readFileSync(logPath, 'utf8').trim().split('\n').filter(Boolean);
const recent = lines.slice(-50).map(l => { try { return JSON.parse(l); } catch { return null; } }).filter(Boolean);

if (recent.length === 0) process.exit(0);

const fileList = [...new Set(recent.map(e => e.file).filter(Boolean))];
const summary = [
  `# Session Summary — ${today}`,
  '',
  `**Files touched (${fileList.length}):**`,
  ...fileList.map(f => `- ${f}`),
  '',
  `**Operations (${recent.length} total):**`,
  ...recent.map(e => `- [${e.timestamp.slice(11, 19)}] ${e.tool}: ${e.summary}`),
  '',
].join('\n');

fs.writeFileSync(sessionFile, summary);
