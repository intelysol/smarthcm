# Marketplace and extension framework technical design

Marketplace records separate a publisher's extension identity from immutable
version artifacts. Each version carries platform constraints, dependencies,
checksums, and signatures; installation is tenant-scoped and only published
versions may be enabled. The DevEx package validator provides the baseline
`fep-plugin.json` contract, while this registry supplies catalog, lifecycle,
installation, and future licensing/update-manager integration points.
