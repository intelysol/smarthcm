<?php
namespace App\Domains\Employee\Http\Controllers;
use App\Domains\Platform\Contracts\TenantContext;
use Illuminate\Http\{JsonResponse,Request};
use Illuminate\Support\Facades\DB;
class HcmReferenceController
{
    public function __construct(private readonly TenantContext $tenant) {}
    public function dashboard(Request $request): JsonResponse { $this->allow($request,'employee.view'); $id=$this->tenant->id(); return response()->json(['data'=>['employees'=>DB::table('employees')->where('tenant_id',$id)->count(),'active_employees'=>DB::table('employees')->where('tenant_id',$id)->where('employment_status','active')->count(),'departments'=>DB::table('departments')->where('tenant_id',$id)->count(),'pending_leave'=>DB::table('leave_applications')->where('tenant_id',$id)->where('status','pending')->count(),'open_requisitions'=>DB::table('requisitions')->where('tenant_id',$id)->whereIn('status',['draft','pending','approved'])->count(),'payroll_runs'=>DB::table('payroll_runs')->where('tenant_id',$id)->latest()->limit(5)->get(),'learning_completions'=>DB::table('course_enrollments')->where('tenant_id',$id)->where('status','completed')->count()]]); }
    public function leave(Request $request): JsonResponse { $this->allow($request,'employee.view'); $id=$this->tenant->id(); return response()->json(['data'=>DB::table('leave_applications')->where('tenant_id',$id)->latest()->paginate(25)]); }
    public function payroll(Request $request): JsonResponse { $this->allow($request,'employee.view'); $id=$this->tenant->id(); return response()->json(['data'=>DB::table('payroll_runs')->where('tenant_id',$id)->latest()->paginate(25)]); }
    private function allow(Request $request,string $permission):void{$request->user()->hasPermission($permission)||abort(403);}
}
