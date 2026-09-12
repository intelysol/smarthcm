<?php
namespace App\Domains\Metadata\Http\Controllers;
use App\Domains\Metadata\Services\MetadataResolutionService; use App\Domains\Platform\Contracts\TenantContext; use Illuminate\Http\{JsonResponse,Request};
class MetadataResolutionController { public function __construct(private readonly MetadataResolutionService $resolver,private readonly TenantContext $tenant){} public function artifact(Request $r,string $type,string $key):JsonResponse{$r->user()->hasPermission('metadata.view')||abort(403);return response()->json(['data'=>$this->resolver->artifact($this->tenant->id(),$type,$key,$r->query('department_id'),(string)$r->user()->id)]);}}
