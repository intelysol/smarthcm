param(
    [Parameter(Mandatory = $true)]
    [ValidatePattern('^[a-z][a-z0-9-]*$')]
    [string] $Name,
    [ValidateSet('package', 'module')]
    [string] $Kind = 'module'
)

$root = Join-Path $PSScriptRoot "..\\$($Kind)s\\$Name"
if (Test-Path -LiteralPath $root) {
    throw "The $Kind '$Name' already exists."
}

$layers = 'Application', 'Domain', 'Infrastructure', 'Presentation', 'Routes', 'Config', 'Database', 'Resources', 'Tests'
foreach ($layer in $layers) {
    New-Item -ItemType Directory -Path (Join-Path $root $layer) -Force | Out-Null
}

$displayName = (Get-Culture).TextInfo.ToTitleCase($Name.Replace('-', ' '))
$readme = @"
# $displayName

## Bounded context

Describe the business capability owned by this $Kind and its public contracts.

## Delivery checklist

- [ ] API and permissions documented
- [ ] Domain events documented
- [ ] Factories, seeders, and tenant isolation implemented
- [ ] Unit, feature, policy, and API tests added
- [ ] Changelog updated
"@
Set-Content -LiteralPath (Join-Path $root 'README.md') -Value $readme -NoNewline
New-Item -ItemType File -Path (Join-Path $root 'CHANGELOG.md') | Out-Null

if ($Kind -eq 'module') {
    New-Item -ItemType Directory -Path (Join-Path $root 'Docs') -Force | Out-Null
}
