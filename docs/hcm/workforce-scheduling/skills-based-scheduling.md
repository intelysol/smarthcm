# Skills-Based Scheduling & Compliance

## Overview

A schedule that matches headcount but ignores skills creates operational failure.
SmartHCM provides multi-dimensional skill and certification validation:
1. **Skill Possession**: Verifies that the employee possesses the required `CareerSkill` (`EmployeeSkill`).
2. **Proficiency Tier**: Checks that employee `current_level` meets or exceeds `min_proficiency_level` (1–5 scale).
3. **Verification Status**: Only considers verified skills for critical operations.
4. **Compliance & Licensing**: Verifies active status of mandatory certifications (`LearningRequirement`). Expired licenses create a hard constraint violation that blocks shift assignment.

## Zero Duplicate Skills Master
Scheduling references existing career skills from `CareerSkill` (`career_skills` table) and learning requirements from `LearningRequirement` (`learning_requirements` table). It does not create its own skill catalog.
