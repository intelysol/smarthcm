# Search and indexing technical design

The `Search` bounded context owns a tenant-scoped indexed-record contract,
saved searches, query history and search analytics primitives. Producers from
Documents, Metadata, Workflow and other domains publish normalized records to
`SearchService::index`; consumers do not query source tables during a search.
This makes the API compatible with a later OpenSearch or semantic-vector
adapter while the local database driver remains deterministic and deployable.

Search always scopes entries by the server-resolved `TenantContext`. RBAC
permissions are checked before search, suggestions, history and saved-search
operations. Result ranking currently prioritizes exact title matches and title
prefix/substring matches, with snippets and facets represented in the response
shape for future weighted and AI-assisted ranking.
