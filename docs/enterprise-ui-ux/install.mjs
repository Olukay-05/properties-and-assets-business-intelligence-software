#!/usr/bin/env node
// Installs the enterprise-ui-ux skill into a project.
//
// Usage (run from anywhere, target defaults to the current directory):
//   node path/to/enterprise-ui-ux/install.mjs [targetDir] [options]
//
// Options:
//   --claude          also mirror the skill to .claude/skills/enterprise-ui-ux
//   --no-prompts      skip the .github/prompts files (GitHub Copilot)
//   --no-agents-md    skip the AGENTS.md block
//   --no-context      skip creating docs/design/design-context.md
//   --dry-run         print what would happen, change nothing
//   --uninstall       remove the skill, prompts and AGENTS.md block (keeps docs/design)
//
// Safe to run repeatedly. It updates the skill in place and replaces only the marked AGENTS.md block.

import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const SKILL_NAME = 'enterprise-ui-ux';
const START = '<!-- enterprise-ui-ux:start -->';
const END = '<!-- enterprise-ui-ux:end -->';

const here = path.dirname(fileURLToPath(import.meta.url));
const args = process.argv.slice(2);
const flags = new Set(args.filter((a) => a.startsWith('--')));
const positional = args.filter((a) => !a.startsWith('--'));
const target = path.resolve(positional[0] || process.cwd());
const dry = flags.has('--dry-run');

const log = (msg) => console.log(msg);
const act = (msg, fn) => {
  log(`${dry ? '[dry-run] ' : ''}${msg}`);
  if (!dry) fn();
};

function copyDir(src, dest) {
  fs.mkdirSync(dest, { recursive: true });
  for (const entry of fs.readdirSync(src, { withFileTypes: true })) {
    if (entry.name === 'node_modules' || entry.name === '.git') continue;
    const s = path.join(src, entry.name);
    const d = path.join(dest, entry.name);
    if (entry.isDirectory()) copyDir(s, d);
    else fs.copyFileSync(s, d);
  }
}

function rmIfExists(p) {
  if (fs.existsSync(p)) fs.rmSync(p, { recursive: true, force: true });
}

function promptFile(mode, description, body) {
  return `---
mode: agent
description: ${description}
---

${body}
`;
}

const prompts = {
  spec: promptFile(
    'spec',
    'UX spec: design a screen or flow with the enterprise-ui-ux skill',
    `Follow \`.agents/skills/${SKILL_NAME}/SKILL.md\` in SPEC mode.
Read \`docs/design/design-context.md\` first. Then produce a screen spec using \`.agents/skills/${SKILL_NAME}/templates/screen-spec.md\` for the request below, and run \`checklists/ux-spec-checklist.md\` before finishing.

Request: \${input:request:Describe the screen or flow}`
  ),
  build: promptFile(
    'build',
    'Build UI: implement a screen or component with the enterprise-ui-ux skill',
    `Follow \`.agents/skills/${SKILL_NAME}/SKILL.md\` in BUILD mode.
Read \`docs/design/design-context.md\` and the relevant screen spec first. Implement with tokens and existing components, cover every state, then run \`checklists/dev-implementation-dod.md\` and report deviations.

Request: \${input:request:Describe what to build or the story to implement}`
  ),
  review: promptFile(
    'review',
    'Review UI: audit a screen, PR or flow with the enterprise-ui-ux rubric',
    `Follow \`.agents/skills/${SKILL_NAME}/SKILL.md\` in REVIEW mode.
Read \`docs/design/design-context.md\` first. Score with \`checklists/design-review-rubric.md\` and list findings in its format, leading with the top three issues.

Target: \${input:target:What to review (path, PR, screen, or flow)}`
  ),
};

function install() {
  if (!fs.existsSync(path.join(here, 'SKILL.md'))) {
    console.error('SKILL.md not found next to install.mjs. Run this script from inside the skill folder.');
    process.exit(1);
  }
  if (!fs.existsSync(target)) {
    console.error(`Target directory does not exist: ${target}`);
    process.exit(1);
  }

  log(`Installing ${SKILL_NAME} into ${target}`);

  // 1. Primary location, universal across Codex, Copilot and Antigravity
  const primary = path.join(target, '.agents', 'skills', SKILL_NAME);
  if (path.resolve(primary) !== path.resolve(here)) {
    act(`Copy skill to ${path.relative(target, primary)}`, () => {
      rmIfExists(primary);
      copyDir(here, primary);
    });
  } else {
    log('Skill already lives at the primary location, skipping copy');
  }

  // 2. Optional Claude Code mirror
  if (flags.has('--claude')) {
    const claudeDir = path.join(target, '.claude', 'skills', SKILL_NAME);
    act(`Mirror skill to ${path.relative(target, claudeDir)}`, () => {
      rmIfExists(claudeDir);
      copyDir(here, claudeDir);
    });
  }

  // 3. GitHub Copilot prompt files
  if (!flags.has('--no-prompts')) {
    const dir = path.join(target, '.github', 'prompts');
    act('Write .github/prompts/enterprise-ui-ux-{spec,build,review}.prompt.md', () => {
      fs.mkdirSync(dir, { recursive: true });
      for (const [mode, content] of Object.entries(prompts)) {
        fs.writeFileSync(path.join(dir, `${SKILL_NAME}-${mode}.prompt.md`), content);
      }
    });
  }

  // 4. AGENTS.md block, replaced in place when it already exists
  if (!flags.has('--no-agents-md')) {
    const agentsPath = path.join(target, 'AGENTS.md');
    const snippet = fs
      .readFileSync(path.join(here, 'templates', 'agents-snippet.md'), 'utf8')
      .trim();
    const block = `${START}\n${snippet}\n${END}`;
    act('Update AGENTS.md block', () => {
      let current = fs.existsSync(agentsPath) ? fs.readFileSync(agentsPath, 'utf8') : '';
      const s = current.indexOf(START);
      const e = current.indexOf(END);
      if (s !== -1 && e !== -1 && e > s) {
        current = current.slice(0, s) + block + current.slice(e + END.length);
      } else {
        current = current.trimEnd() + (current.trim() ? '\n\n' : '') + block + '\n';
      }
      fs.writeFileSync(agentsPath, current);
    });
  }

  // 5. Project design context
  if (!flags.has('--no-context')) {
    const ctx = path.join(target, 'docs', 'design', 'design-context.md');
    if (fs.existsSync(ctx)) {
      log('docs/design/design-context.md already exists, leaving it untouched');
    } else {
      act('Create docs/design/design-context.md from template', () => {
        fs.mkdirSync(path.dirname(ctx), { recursive: true });
        fs.copyFileSync(path.join(here, 'templates', 'design-context.md'), ctx);
      });
    }
  }

  log('\nDone. Next steps:');
  log('  1. Ask your UX agent to fill in docs/design/design-context.md');
  log('  2. Commit .agents/, .github/prompts/, AGENTS.md and docs/design/');
}

function uninstall() {
  log(`Removing ${SKILL_NAME} from ${target}`);
  act(`Remove .agents/skills/${SKILL_NAME}`, () => rmIfExists(path.join(target, '.agents', 'skills', SKILL_NAME)));
  act(`Remove .claude/skills/${SKILL_NAME}`, () => rmIfExists(path.join(target, '.claude', 'skills', SKILL_NAME)));
  act('Remove Copilot prompt files', () => {
    for (const mode of Object.keys(prompts)) {
      rmIfExists(path.join(target, '.github', 'prompts', `${SKILL_NAME}-${mode}.prompt.md`));
    }
  });
  const agentsPath = path.join(target, 'AGENTS.md');
  if (fs.existsSync(agentsPath)) {
    act('Remove AGENTS.md block', () => {
      const current = fs.readFileSync(agentsPath, 'utf8');
      const s = current.indexOf(START);
      const e = current.indexOf(END);
      if (s !== -1 && e !== -1 && e > s) {
        const next = (current.slice(0, s).trimEnd() + '\n' + current.slice(e + END.length).trimStart()).trim();
        fs.writeFileSync(agentsPath, next ? next + '\n' : '');
      }
    });
  }
  log('docs/design/design-context.md was kept on purpose.');
}

if (flags.has('--uninstall')) uninstall();
else install();
