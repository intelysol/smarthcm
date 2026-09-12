<?php
namespace App\Domains\Operations\Http\Controllers;
use App\Domains\Operations\Models\{OpsAlert, OpsAlertRule, OpsIncident, OpsMetric};
use App\Domains\Operations\Services\OperationsService;
use App\Domains\Platform\Contracts\TenantContext;
use Illuminate\Http\{JsonResponse, Request};
class OperationsController
{
    public function __construct(private readonly OperationsService $ops, private readonly TenantContext $tenant) {}
    public function dashboard(Request $request): JsonResponse { $this->allow($request,'operations.view'); $id=$this->tenant->id(); return response()->json(['data'=>['status'=>'healthy','metrics'=>OpsMetric::query()->where(fn($q)=>$q->whereNull('tenant_id')->orWhere('tenant_id',$id))->latest('recorded_at')->limit(100)->get(),'open_alerts'=>OpsAlert::query()->where('status','open')->where(fn($q)=>$q->whereNull('tenant_id')->orWhere('tenant_id',$id))->count(),'open_incidents'=>OpsIncident::query()->where('status','open')->where(fn($q)=>$q->whereNull('tenant_id')->orWhere('tenant_id',$id))->count()]]); }
    public function metric(Request $request): JsonResponse { $this->allow($request,'operations.manage'); $data=$request->validate(['metric'=>['required','string','max:100'],'value'=>['required','numeric'],'unit'=>['nullable','string'],'dimensions'=>['nullable','array']]); return response()->json(['data'=>$this->ops->metric($this->tenant->id(),$data['metric'],(float)$data['value'],$data['unit']??null,$data['dimensions']??[])],201); }
    public function rules(Request $request): JsonResponse { $this->allow($request,'operations.view'); return response()->json(['data'=>OpsAlertRule::query()->where(fn($q)=>$q->whereNull('tenant_id')->orWhere('tenant_id',$this->tenant->id()))->get()]); }
    public function createRule(Request $request): JsonResponse { $this->allow($request,'operations.manage'); $data=$request->validate(['name'=>['required','string'],'metric'=>['required','string'],'operator'=>['required','in:>,>=,<,<=,='],'threshold'=>['required','numeric'],'severity'=>['required','in:info,warning,critical'],'channels'=>['nullable','array']]); return response()->json(['data'=>OpsAlertRule::query()->create([...$data,'tenant_id'=>$this->tenant->id()])],201); }
    public function evaluate(Request $request): JsonResponse { $this->allow($request,'operations.manage'); return response()->json(['data'=>['alerts_created'=>$this->ops->evaluate($this->tenant->id())]]); }
    public function incidents(Request $request): JsonResponse { $this->allow($request,'operations.view'); return response()->json(['data'=>OpsIncident::query()->where(fn($q)=>$q->whereNull('tenant_id')->orWhere('tenant_id',$this->tenant->id()))->latest()->paginate(25)]); }
    public function createIncident(Request $request): JsonResponse { $this->allow($request,'operations.manage'); $data=$request->validate(['title'=>['required','string'],'description'=>['nullable','string'],'severity'=>['required','in:minor,major,critical']]); return response()->json(['data'=>OpsIncident::query()->create([...$data,'tenant_id'=>$this->tenant->id(),'timeline'=>[['event'=>'created','at'=>now()->toIso8601String()]]])],201); }
    public function resolve(Request $request,string $incident): JsonResponse { $this->allow($request,'operations.manage'); $model=OpsIncident::query()->where('tenant_id',$this->tenant->id())->findOrFail($incident); $data=$request->validate(['root_cause'=>['required','string']]); return response()->json(['data'=>$this->ops->resolveIncident($model,$data['root_cause'])]); }
    private function allow(Request $request,string $permission):void { $request->user()->hasPermission($permission)||abort(403); }
}
