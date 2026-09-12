<?php
namespace App\Domains\Metadata\Services;
use App\Domains\Metadata\Models\MetadataArtifact;
use Illuminate\Support\Facades\Cache;
class MetadataResolutionService
{
    /** Resolve the most specific published artifact; scopes are ordered user, department, tenant, then platform. */
    public function artifact(string $tenantId,string $type,string $key,?string $departmentId=null,?string $userId=null):?MetadataArtifact
    {
        $scopes=[['user',$userId],['department',$departmentId],['tenant',$tenantId],['platform',null]]; $cache="metadata:resolve:{$tenantId}:{$type}:{$key}:".($departmentId??'').':'.($userId??'');
        return Cache::remember($cache,now()->addMinutes(30),function()use($scopes,$type,$key){foreach($scopes as [$scopeType,$scopeId]){if($scopeType!=='platform'&&!$scopeId)continue;$artifact=MetadataArtifact::query()->where('artifact_type',$type)->where('key',$key)->where('scope_type',$scopeType)->where('scope_id',$scopeId)->where('status','published')->latest('version')->first();if($artifact)return $artifact;}return null;});
    }
}
