# WebForge

Visual tool for designing and generating data-intensive web applications.

## Requirements

- Node.js `>=20` (see `.nvmrc`)
- pnpm `>=9`

## Quickstart

```bash
pnpm install
pnpm --filter @webforge/app dev
```

Open the URL printed by Vite — you should see **Hello WebForge**.

## Packages

- [`@webforge/app`](./packages/app) — the WebForge visual tool (React + Vite SPA)
- [`@webforge/shared-types`](./packages/shared-types) — shared Zod schemas and TypeScript types

## Common scripts

```bash
pnpm dev           # start the app in dev mode
pnpm -r build      # build every package that has a build step
pnpm -r typecheck  # TypeScript check across all packages
pnpm -r lint       # ESLint across all packages
pnpm -r test       # Vitest across all packages
pnpm format        # Prettier format the repo
```

## License

MIT — see [LICENSE](./LICENSE).
