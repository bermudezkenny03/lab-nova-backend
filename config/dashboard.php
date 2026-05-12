<?php

return [
    'cache_ttl_minutes' => (int) env('DASHBOARD_CACHE_TTL_MINUTES', 60),

    'roles' => [
        'super_admin' => (int) env('ROLE_SUPER_ADMIN_ID', 1),
        'admin' => (int) env('ROLE_ADMIN_ID', 2),
        'lab_manager' => (int) env('ROLE_LAB_MANAGER_ID', 3),
        'teacher' => (int) env('ROLE_TEACHER_ID', 4),
        'student' => (int) env('ROLE_STUDENT_ID', 5),
    ],

    'staff_role_ids' => [
        (int) env('ROLE_SUPER_ADMIN_ID', 1),
        (int) env('ROLE_ADMIN_ID', 2),
        (int) env('ROLE_LAB_MANAGER_ID', 3),
        (int) env('ROLE_TEACHER_ID', 4),
    ],

    'relevant_modules' => array_filter(
        explode(',', env('DASHBOARD_RELEVANT_MODULES', 'reports,reservations,equipment,users,categories'))
    ),
];
