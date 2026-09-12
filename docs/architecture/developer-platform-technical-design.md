# Developer Platform technical design

The DevEx toolkit is delivered as first-party Artisan commands so it works in
the same local and CI environments as the application. `flow:module:create`
creates the bounded-context skeleton, `flow:entity:create` creates a typed
entity starting point, `flow:test:architecture` checks high-risk boundary
violations, `flow:docs:generate` produces a source inventory, and
`flow:package:build` validates an installable `fep-plugin.json` manifest.

Generators refuse to overwrite an existing module. Package manifests require a
name, semantic version, and existing entrypoint; this is the base contract for
future signatures, dependency compatibility, SDK generation, and marketplace
publishing. Commands are intentionally deterministic and safe for CI use.
