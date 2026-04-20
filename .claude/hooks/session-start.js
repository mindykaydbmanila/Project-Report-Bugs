#!/usr/bin/env node
// Reads the last 3 session summaries and prints them as context.

const fs = require('fs');
const path = require('path');

const sessionsDir = path.join(__dirname, '../memory/sessions');

if (!fs.existsSync(sessionsDir)) process.exit(0);

const files = fs.readdirSync(sessionsDir)
  .filter(f => f.endsWith('.md'))
  .sort()
  .slice(-3);

if (files.length === 0) process.exit(0);

const output = files.map(f => {
  const content = fs.readFileSync(path.join(sessionsDir, f), 'utf8');
  return `---\n${content}`;
}).join('\n');

// Output to stdout so Claude Code injects it as session context
process.stdout.write(output + '\n');
