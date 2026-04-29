# CLAUDE.md

## Overview

WebForge is an open-source visual tool for designing and generating data-intensive web applications. The tool itself is a React SPA; its output is real codebases (frontend + backend + documentation).

## Monorepo structure

This repository is a pnpm workspace (ADR-006). Packages live under `packages/`:

| Package                  | Role                                                |
| ------------------------ | --------------------------------------------------- |
| `@webforge/app`          | The WebForge visual tool — React SPA (user-facing)  |
| `@webforge/shared-types` | Shared Zod schemas and derived TypeScript types     |

Future packages (not yet scaffolded): `@webforge/component-library`, `@webforge/generator-core`, `@webforge/template-*`.

## Stack per package

### `@webforge/app` (ADR-001, ADR-002)

- **React 18** + **Vite 5** — SPA host
- **TypeScript strict** — `strict`, `noUncheckedIndexedAccess`, `exactOptionalPropertyTypes`, `noImplicitOverride`
- **Zustand ^4.5 + Immer ^10** — global state (Zustand `immer` middleware)
- **Vitest** + **React Testing Library** — tests (jsdom env)
- Path alias `@/` → `packages/app/src/`

### `@webforge/shared-types` (ADR-005)

- **Zod ^3.23** — schema definitions and type derivation
- **TypeScript strict** — no build step, consumed as source by linked packages
- No runtime React/DOM concerns; pure types and validators

## File & naming conventions

- TypeScript source: `.ts` for pure logic, `.tsx` for React components
- Component files: `PascalCase.tsx` (e.g. `App.tsx`, `ProjectPanel.tsx`)
- Non-component files: `kebab-case.ts` (e.g. `vite.config.ts`, `use-project.ts`)
- Zustand stores: one file per slice under `src/store/`; hook exported as `use<Name>Store`
- Types derived from Zod: `z.infer<typeof FooSchema>` → exported as `Foo`; schema stays named `FooSchema`
- Test files: colocated next to source, named `<source>.test.ts(x)`

## Folder layout

```
webforge/
├── packages/
│   ├── app/
│   │   ├── src/
│   │   │   ├── main.tsx        # Vite entry / React mount
│   │   │   ├── App.tsx         # Root component
│   │   │   ├── store/          # Zustand slices
│   │   │   └── components/     # (future) reusable UI
│   │   ├── index.html
│   │   ├── vite.config.ts
│   │   └── package.json
│   └── shared-types/
│       ├── src/
│       │   └── index.ts        # public entry (schemas + types)
│       └── package.json
├── tsconfig.base.json
├── .eslintrc.cjs
├── .prettierrc
├── pnpm-workspace.yaml
└── package.json
```

## Testing

- **Runner**: Vitest (`pnpm --filter <pkg> test`, or `pnpm -r test`)
- **DOM env**: jsdom (configured in `@webforge/app`)
- **Component tests**: React Testing Library + `@testing-library/jest-dom`
- **Location**: `<name>.test.ts(x)` colocated next to the source file
- **Style**: explicit imports — `import { describe, it, expect } from 'vitest'`; no Vitest globals

## Common commands

```bash
pnpm install                              # install all dependencies
pnpm dev                                  # run the app in dev mode
pnpm --filter @webforge/app dev           # same, explicit
pnpm --filter @webforge/app build         # production bundle
pnpm -r typecheck                         # TS type-check across all packages
pnpm -r lint                              # ESLint across all packages
pnpm -r test                              # Vitest across all packages
pnpm format                               # Prettier format the repo
```

## Architectural patterns (MUST follow)

- **Shared types belong in `@webforge/shared-types`.** Never duplicate a type in `@webforge/app` that describes persisted or cross-cutting data. Types internal to a single component stay local.
- **Global state uses Zustand + Immer.** No Redux. No React Context API for state management (Context only for truly static DI such as theme or feature flags — and even then, prefer Zustand).
- **Validation uses Zod at trust boundaries.** Schemas live in `@webforge/shared-types`; validate at runtime when reading from storage, the network, or user input. Internal functions trust their typed inputs.
- **Persistence uses `idb-keyval` (ADR-005).** Not yet implemented — will be wired up in a later feature. When that lands, all persisted data is validated against a Zod schema before being returned from the persistence layer.

## Features implemented

- **F-001** — Monorepo scaffold: pnpm workspaces, `@webforge/app` with React + Vite + Zustand + Immer, `@webforge/shared-types` with Zod, ESLint/Prettier/Vitest toolchain, baseline "Hello WebForge" screen.
