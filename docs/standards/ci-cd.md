# CI/CD Standard

The required pipeline is: commit → lint → static analysis → architecture tests → unit tests → feature tests → security scan → performance scan → Docker build → deploy. The committed workflow establishes the executable quality, architecture, and deployment gates; environment-specific scans and deployment credentials are configured by the delivery environment.
