
# AGENTS.md

## Project Overview

This is a multi-tenant SaaS AI Chatbot + Live Chat Support platform.

Main folders:

- `dashboard/` → Laravel application
- `python-backend/` → AI/RAG backend
- `widget-sdk/` → public chatbot widget SDK
- `dashboard/public/widget/` → currently served widget files

The platform includes:
- Super Admin dashboard
- Client/Owner dashboard
- Custom Agent dashboard
- AI chatbot
- Human live chat
- Realtime communication using Laravel Reverb

---

# IMPORTANT WORKING RULES

## 1. Keep Changes Small

Always make the smallest possible change required for the task.

Do not:
- refactor unrelated code
- rename unrelated classes/methods
- change project architecture
- rewrite working code unnecessarily
- modify unrelated files

Before editing, identify the minimum files required.

---

## 2. Do Not Scan the Whole Repository

For every task:

- inspect only files directly related to the issue
- do not recursively analyze the whole repository unless explicitly requested
- do not inspect `vendor/`
- do not inspect `node_modules/`
- do not inspect logs, cache, build output, storage uploads, or generated files unless required

If exact file paths are provided, start with those files only.

---

## 3. Preserve Existing Architecture

Do not introduce a new architecture unless explicitly requested.

Current architecture:

### Roles

- `super_admin`
- `owner`
- `agent`

### Dashboards

- Super Admin → Filament
- Client/Owner → Filament
- Agent → custom Laravel Blade

Do NOT convert the Agent dashboard to Filament.

---

# MULTI-TENANT SECURITY

Tenant isolation is critical.

Relationships:

```text
Company
  ↓
Websites
  ↓
Chatbot / Live Chat Data