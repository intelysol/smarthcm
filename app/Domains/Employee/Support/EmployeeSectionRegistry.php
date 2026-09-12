<?php

namespace App\Domains\Employee\Support;

use App\Domains\Employee\Models\EmployeeBankAccount;
use App\Domains\Employee\Models\EmployeeCertification;
use App\Domains\Employee\Models\EmployeeDocument;
use App\Domains\Employee\Models\EmployeeEducation;
use App\Domains\Employee\Models\EmployeeEmergencyContact;
use App\Domains\Employee\Models\EmployeeFamilyMember;
use App\Domains\Employee\Models\EmployeeLanguage;
use App\Domains\Employee\Models\EmployeeSalary;
use App\Domains\Employee\Models\EmployeeSkill;
use App\Domains\Employee\Models\EmployeeWorkExperience;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EmployeeSectionRegistry
{
    public function get(string $key): EmployeeSectionDefinition
    {
        $definitions = $this->all();

        if (! isset($definitions[$key])) {
            throw new ModelNotFoundException("Unsupported employee section [{$key}].");
        }

        return $definitions[$key];
    }

    public function all(): array
    {
        return [
            'emergency-contacts' => new EmployeeSectionDefinition('emergency-contacts', 'employee.update', EmployeeEmergencyContact::class, [
                'name' => ['required', 'string', 'max:255'], 'relationship' => ['required', 'string', 'max:120'], 'phone' => ['nullable', 'string', 'max:40'], 'mobile' => ['nullable', 'string', 'max:40'], 'email' => ['nullable', 'email:rfc'], 'address' => ['nullable', 'string', 'max:1000'], 'priority' => ['nullable', 'integer', 'min:1'],
            ]),
            'family' => new EmployeeSectionDefinition('family', 'employee.update', EmployeeFamilyMember::class, [
                'name' => ['required', 'string', 'max:255'], 'date_of_birth' => ['nullable', 'date'], 'relationship' => ['required', 'string', 'max:120'], 'occupation' => ['nullable', 'string', 'max:255'],
            ]),
            'education' => new EmployeeSectionDefinition('education', 'employee.update', EmployeeEducation::class, [
                'degree' => ['required', 'string', 'max:255'], 'institution' => ['required', 'string', 'max:255'], 'board_university' => ['nullable', 'string', 'max:255'], 'major' => ['nullable', 'string', 'max:255'], 'start_date' => ['nullable', 'date'], 'end_date' => ['nullable', 'date', 'after_or_equal:start_date'], 'grade' => ['nullable', 'string', 'max:80'], 'certificate_path' => ['nullable', 'string', 'max:255'],
            ]),
            'experience' => new EmployeeSectionDefinition('experience', 'employee.update', EmployeeWorkExperience::class, [
                'company' => ['required', 'string', 'max:255'], 'position' => ['required', 'string', 'max:255'], 'industry' => ['nullable', 'string', 'max:255'], 'start_date' => ['nullable', 'date'], 'end_date' => ['nullable', 'date', 'after_or_equal:start_date'], 'responsibilities' => ['nullable', 'string'], 'salary' => ['nullable', 'numeric', 'min:0'], 'reason_for_leaving' => ['nullable', 'string', 'max:1000'],
            ]),
            'skills' => new EmployeeSectionDefinition('skills', 'employee.update', EmployeeSkill::class, [
                'skill_name' => ['required', 'string', 'max:255'], 'skill_type' => ['required', 'in:technical,soft'], 'skill_level' => ['required', 'in:beginner,intermediate,advanced,expert'], 'years_of_experience' => ['nullable', 'integer', 'min:0'], 'certification_id' => ['nullable', 'string', 'max:120'],
            ]),
            'certifications' => new EmployeeSectionDefinition('certifications', 'employee.update', EmployeeCertification::class, [
                'certification_name' => ['required', 'string', 'max:255'], 'issuing_organization' => ['nullable', 'string', 'max:255'], 'issue_date' => ['nullable', 'date'], 'expiry_date' => ['nullable', 'date', 'after_or_equal:issue_date'], 'credential_number' => ['nullable', 'string', 'max:120'], 'attachment_path' => ['nullable', 'string', 'max:255'],
            ]),
            'languages' => new EmployeeSectionDefinition('languages', 'employee.update', EmployeeLanguage::class, [
                'language' => ['required', 'string', 'max:120'], 'reading_level' => ['nullable', 'in:none,basic,good,fluent'], 'writing_level' => ['nullable', 'in:none,basic,good,fluent'], 'speaking_level' => ['nullable', 'in:none,basic,good,fluent'], 'is_native' => ['nullable', 'boolean'],
            ]),
            'bank-accounts' => new EmployeeSectionDefinition('bank-accounts', 'employee.bank', EmployeeBankAccount::class, [
                'bank' => ['required', 'string', 'max:255'], 'branch' => ['nullable', 'string', 'max:255'], 'iban' => ['nullable', 'string', 'max:80'], 'account_number' => ['required', 'string', 'max:120'], 'account_title' => ['required', 'string', 'max:255'], 'is_primary' => ['nullable', 'boolean'],
            ]),
            'salary' => new EmployeeSectionDefinition('salary', 'employee.salary', EmployeeSalary::class, [
                'salary_structure' => ['required', 'string', 'max:255'], 'basic_salary' => ['required', 'numeric', 'min:0'], 'gross_salary' => ['required', 'numeric', 'gte:basic_salary'], 'currency' => ['required', 'string', 'size:3'], 'payroll_group' => ['nullable', 'string', 'max:120'],
            ]),
            'documents' => new EmployeeSectionDefinition('documents', 'employee.documents', EmployeeDocument::class, [
                'document_type' => ['required', 'string', 'max:120'], 'title' => ['required', 'string', 'max:255'], 'file_path' => ['required', 'string', 'max:255'], 'mime_type' => ['nullable', 'string', 'max:120'], 'file_size' => ['nullable', 'integer', 'min:0'], 'version' => ['nullable', 'integer', 'min:1'], 'expires_at' => ['nullable', 'date'], 'metadata' => ['nullable', 'array'],
            ]),
        ];
    }
}
