# MEMA ERP — DESIGN SYSTEM & UX STANDARDS

**Document:** `PLAN/11-DESIGN-SYSTEM-AND-UX.md` · **Version:** 1.0.0-PLAN

---

## 1. Design principles

An ERP is used all day, every day, by people who did not choose it. That single fact should drive every
interface decision.

1. **Speed of task completion over visual novelty.** A Registrar processing 300 applications cares about
   keystrokes, not animation. Optimise the hundredth repetition, not the first impression.
2. **Density where expertise lives, space where it does not.** Admin tables are dense and information-rich;
   the student portal and public website are open and guided. These are different audiences with different
   frequencies of use — one visual language applied uniformly would fail both.
3. **The system never loses work.** Long forms autosave. Navigating away warns. A dropped connection during a
   twenty-minute application does not discard it.
4. **Errors explain and resolve.** "Registration failed" is useless. "You cannot register for CSC 201 — you
   have not passed CSC 101" plus a link to advising is actionable.
5. **State is always visible.** Users must always know where an item is in a workflow, who holds it, and what
   happens next. Most ERP support calls are "where is my request?"
6. **Accessible by default.** WCAG 2.2 AA is an acceptance criterion in every module, not a retrofit.

---

## 2. Brand and tokens

| Token | Value | Use |
|---|---|---|
| `--color-primary` | `#0A3E50` Deep Teal | Primary actions, headers, active navigation |
| `--color-secondary` | `#1E8449` Forest Green | Success, positive status, confirmation |
| `--color-canvas` | `#F8FAFC` | Page background |
| `--color-surface` | `#FFFFFF` | Cards, tables, panels |
| `--color-danger` | `#B91C1C` | Destructive actions, errors |
| `--color-warning` | `#B7791F` | Caution, pending, expiring |
| `--color-info` | `#1D4ED8` | Informational |

Typography: Inter (UI), tabular figures for all numeric columns — misaligned digits in a fee table make
scanning materially slower. Base 14px in admin, 16px in student and public surfaces.

Spacing: 4px base scale. **Arbitrary values are rejected at review** (ADR-005). If a token does not exist,
add the token — this is what keeps seven applications visually coherent over 24 months.

All tokens live once in `packages/ui` and are consumed by all seven apps. Rebranding is a token change.

---

## 3. Application-specific UX posture

| App | Posture | Key patterns |
|---|---|---|
| **Website** | Marketing, SEO, wide public audience | Fast static pages, clear programme discovery, prominent "Apply" |
| **Applicant** | Guided, reassuring, low prior knowledge | Multi-step wizard, visible progress, autosave, resumable, clear document requirements, mobile-first |
| **Student** | Frequent, mobile-heavy, task-focused | Dashboard of "what needs my attention", one-tap common actions, offline-tolerant reads |
| **Lecturer** | Periodic, bursty, high-stakes | Marks entry optimised for speed — keyboard navigation, paste from spreadsheet, validation before submit, explicit lock confirmation |
| **Staff** | Occasional, form-driven | Clear request status, simple approvals |
| **Admin** | All-day, expert, dense | Persistent filters, bulk actions, keyboard shortcuts, saved views, inline editing, export everywhere |
| **Management** | Infrequent, glanceable | Few KPIs, clear trends, drill-down, print/export-ready |

**Marks entry deserves special attention.** A lecturer entering 200 marks will use this screen a handful of
times a year under deadline pressure, and errors there are high-consequence. It needs spreadsheet-grade
ergonomics: arrow-key navigation, paste a column, live validation against the maximum, a visible unsaved-changes
count, an explicit review step before the irreversible lock, and no possibility of silent partial submission.

---

## 4. Core patterns

**Data tables** (`packages/tables`): server-side pagination, sort and filter; column show/hide and reorder
persisted per user; sticky header and first column; row selection with bulk actions; export respecting current
filters; empty, loading skeleton and error states; responsive card layout below 768px.

**Forms** (`packages/forms`): React Hook Form + Zod schemas generated from OpenAPI, so client and server
validation cannot drift; inline validation on blur, not on every keystroke; errors associated to fields for
screen readers; required fields marked, not optional ones; autosave on long forms; unsaved-changes guard;
file upload with progress, type/size feedback and retry.

**Workflow and approvals:** a visual state timeline showing completed, current and pending steps with the
holder of each; comments and rejection reasons always visible; a single "what needs my action" queue per user.

**Notifications:** in-app centre with read/unread, grouped by type, deep-linked to the record, and preference
controls per channel.

---

## 5. Accessibility requirements

| Requirement | Standard |
|---|---|
| Contrast | 4.5:1 body, 3:1 large text and UI components |
| Keyboard | Every function reachable; visible focus; logical order; no traps |
| Screen readers | Semantic HTML, ARIA only where semantics are insufficient; tested with NVDA and VoiceOver |
| Forms | Labels associated, errors announced, focus moved to first error |
| Motion | Respect `prefers-reduced-motion` |
| Zoom | Usable at 200% without horizontal scrolling |
| Targets | Minimum 44×44px on touch |
| Charts | Never colour alone; data table alternative always available |

Radix primitives under shadcn/ui provide correct keyboard and screen-reader behaviour for dialogs, menus,
comboboxes and tabs — the components most often built wrong by hand. axe-core runs in CI and blocks on
violations; manual audit per module release.

---

## 6. Performance budgets

| Metric | Public site | Portals | Admin |
|---|---|---|---|
| LCP | < 1.5 s | < 2.0 s | < 2.5 s |
| INP | < 200 ms | < 200 ms | < 200 ms |
| CLS | < 0.1 | < 0.1 | < 0.1 |
| JS bundle (initial) | < 100 KB | < 200 KB | < 300 KB |

Enforced in CI with Lighthouse budgets. Route-level code splitting; virtualised long tables; images optimised
via `next/image`; ECharts loaded only on chart routes.

**The student portal is budgeted for a low-end Android on a throttled 3G connection**, because that is what a
large share of students actually use. Testing only on a developer laptop over office fibre produces a portal
that excludes the students who most need it to work.

---

## 7. Mobile and responsive

All seven apps are responsive. The student and applicant portals are **designed mobile-first**, not adapted —
the majority of their traffic is mobile. Admin is desktop-first but must remain usable on a tablet for
approvals, since approvers are frequently away from a desk and a blocked approval blocks a workflow.

Native apps (Phase 5, MOD-05-08) cover push notifications, biometric login, offline access to timetable,
results and exam card, and QR attendance — the capabilities a web app genuinely cannot deliver well. Everything
else remains web, to avoid maintaining two implementations of the same feature.
