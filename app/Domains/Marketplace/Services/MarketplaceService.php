<?php
namespace App\Domains\Marketplace\Services;
use App\Domains\Marketplace\Models\{Extension,ExtensionVersion,Installation};
class MarketplaceService
{
 public function install(string $tenantId, Extension $extension, ExtensionVersion $version): Installation { abort_unless($extension->status==='published'&&$version->status==='published',422,'Extension version is not installable.'); return Installation::query()->updateOrCreate(['tenant_id'=>$tenantId,'extension_id'=>$extension->id],['version_id'=>$version->id,'status'=>'enabled','installed_at'=>now()]); }
 public function verify(ExtensionVersion $version): bool { return $version->checksum !== null && $version->signature !== null; }
}
