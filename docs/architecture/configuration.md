# Configuration

`ConfigurationService` is the canonical configuration resolver. Values resolve in user, company, tenant, then platform order; a definition's default is used if no override exists. Sensitive definition values are encrypted before storage and are never returned by the resolver in raw database form.
