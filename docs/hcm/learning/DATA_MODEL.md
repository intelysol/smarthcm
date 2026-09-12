# Learning Data Model

The Learning module operates over 45 database tables:

| Domain Area | Tables | Purpose |
|---|---|---|
| **Catalog & Structure** | `learning_categories`, `learning_providers`, `learning_instructors`, `learning_venues` | Catalog taxonomies, training vendors, internal/external trainers, classrooms |
| **Courses & Content** | `learning_courses`, `learning_course_versions`, `learning_course_objectives`, `learning_course_prerequisites`, `learning_course_modules`, `learning_course_lessons`, `learning_content`, `learning_items` | Versioned courses, syllabus hierarchy, SCORM/video/PDF content items |
| **Programs & Paths** | `learning_programs`, `learning_program_courses`, `learning_paths`, `learning_path_items` | Multi-course programs and career development learning paths |
| **Sessions & Attendance**| `learning_sessions`, `learning_session_attendance` | Scheduled physical/virtual classes and separate training attendance |
| **Enrollment & Workflow**| `learning_enrollments`, `learning_waitlists`, `learning_nominations` | Enrollment state machine, approval routing, manager nominations |
| **Requirements & Progress**| `learning_requirements`, `learning_requirement_assignments`, `learning_progress`, `employee_learning_records` | Mandatory compliance training, progress % tracking, permanent transcripts |
| **Assessments & Exams** | `learning_assessments`, `learning_questions`, `learning_question_options`, `learning_assessment_attempts`, `learning_assessment_answers` | Question banks, randomized question ordering, attempt limits, server scoring |
| **Certificates & Renewal**| `learning_certificates`, `learning_certification_renewals`, `learning_credits`, `learning_credit_transactions` | Unique certificates, public verification hashes, recertification cycles, CEU credits |
| **Development Plans (IDP)**| `hcm_learning_development_plans`, `hcm_learning_development_activities` | Career development goals, mentoring, coaching, stretch assignments |
| **External Learning** | `hcm_learning_external_records`, `hcm_learning_evidence` | Off-platform courses, university programs, uploaded proof documents |
| **Budgets & Feedback** | `learning_budgets`, `learning_budget_allocations`, `learning_training_costs`, `learning_feedback`, `learning_recommendations`, `learning_course_evaluations` | Departmental budget limits, cost items (Money decimal), participant evaluations |
