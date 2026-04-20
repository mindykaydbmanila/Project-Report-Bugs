#!/usr/bin/env node
// Logs significant Edit/Write tool calls to .claude/memory/changelog.jsonl

const fs = require('fs');
const path = require('path');

const input = JSON.parse(fs.readFileSync('/dev/stdin', 'utf8'));
const { tool_name, tool_input } = input;

if (!['Edit', 'Write'].includes(tool_name)) process.exit(0);

const logPath = path.join(__dirname, '../memory/changelog.jsonl');
const entry = {
  timestamp: new Date().toISOString(),
  tool: tool_name,
  file: tool_input.file_path ?? null,
  summary: tool_name === 'Edit'
    ? `Edited: replaced "${(tool_input.old_string ?? '').slice(0, 80).replace(/\n/g, '↵')}"`
    : `Wrote file (${(tool_input.content ?? '').length} chars)`,
};

fs.appendFileSync(logPath, JSON.stringify(entry) + '\n');
