Permissions & Backend Setup

1) Run DB + seeds (local):
- composer install
- cp .env.example .env
- configure DB in .env
- php artisan key:generate
- php artisan migrate --seed

2) Tests:
- composer dump-autoload
- php artisan test

3) Permissions model (backend):
- Policies implemented for resources (Equipment, Reservation, Category, ReportRequest, Report, User).
- AuthController/login now returns 'permissions' (getPermissionMap) and 'modules' for UI.
- Endpoint: GET /api/me/permissions returns { modules, permissions } for current user.

4) How frontend should use it:
- Use /api/me/permissions after login to decide which buttons/actions to show.
- Still enforce permissions on actions: backend policies already protect all CRUD routes.

5) Additional recommendations:
- Cache permission maps per user (Redis) for high traffic.
- Add CI job to run php artisan migrate --env=testing and php artisan test on PRs.
- Add automated end-to-end tests to verify permission flows.

6) Notes for reviewers:
- Super Admin bypass implemented via Gate::before.
- Policies allow owner-specific rules (e.g., reservation owners can edit/cancel while pending).

If you want, I can add CI pipeline config (GitHub Actions) that runs migrations and phpunit on PRs. Decide and I implement.