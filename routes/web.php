<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

require base_path('app/Domains/Learning/Routes/web.php');
require base_path('app/Domains/Career/Routes/web.php');
require base_path('app/Domains/Engagement/Routes/web.php');
require base_path('app/Domains/EmployeeRelations/Routes/web.php');
require base_path('app/Domains/Attendance/Routes/web.php');
require base_path('app/Domains/Payroll/Routes/web.php');
require base_path('app/Domains/Benefits/Routes/web.php');
require base_path('app/Domains/Expenses/Routes/web.php');
require base_path('app/Domains/SelfService/Routes/web.php');
require base_path('app/Domains/Analytics/Routes/web.php');
require base_path('app/Domains/WorkforcePlanning/Routes/web.php');
require base_path('app/Domains/Recruitment/Routes/web.php');
require base_path('app/Domains/Onboarding/Routes/web.php');
require base_path('app/Domains/Lifecycle/Routes/web.php');
require base_path('app/Domains/Offboarding/Routes/web.php');
require base_path('app/Domains/EmployeeDocuments/Routes/web.php');
require base_path('app/Domains/EmployeeProfile/Routes/web.php');
require base_path('app/Domains/PersonalData/Routes/web.php');
require base_path('app/Domains/Compliance/Routes/web.php');
require base_path('app/Domains/HealthSafety/Routes/web.php');
require base_path('app/Domains/Performance/Routes/web.php');
require base_path('app/Domains/Mobility/Routes/web.php');
require base_path('app/Domains/WorkforceAdmin/Routes/web.php');
require base_path('app/Domains/WorkforceProductivity/Routes/web.php');
require base_path('app/Domains/WorkforceOptimization/Routes/web.php');
require base_path('app/Domains/WorkforceIntelligence/Routes/web.php');
require base_path('app/Domains/WorkforceGovernance/Routes/web.php');
require base_path('app/Domains/EmployeeAi/Routes/web.php');
require base_path('app/Domains/ResponsibleAi/Routes/web.php');
