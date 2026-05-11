# Design — JWT Auth Integration (Patients)

**Date:** 2026-05-11
**Branch:** `feature/auth`
**Status:** Approved

## Overview

Integrate JWT-based authentication into the Healthcare API using `light-it-labs/lightit-auth-laravel` (which wraps `php-open-source-saver/jwt-auth`). The `Patient` model becomes the authenticatable entity. Only authenticated patients may access `/appointments/*` endpoints, and each authenticated patient is restricted to their own appointments. All other resource endpoints (`/clinics`, `/doctors`, `/users`, `/patients` non-auth actions) remain publicly accessible.

### Locked decisions

| Topic | Decision |
|---|---|
| Auth driver | JWT (package's JWT installer; not Sanctum) |
| Authenticatable model | `Lightit\Patients\Domain\Models\Patient` |
| Registration | Extend `POST /patients` to require `password` (no separate `/auth/register`) |
| Ownership scope | Authenticated patient sees/modifies **only their own** appointments |
| Endpoints | `POST /auth/login`, `POST /auth/logout`, `POST /auth/refresh`, `GET /auth/me` |
| Scaffolding | Run `php artisan auth:setup` (JWT), then adapt the generated files for `Patient` |
| Password storage | Laravel `hashed` cast (bcrypt). No ciphersweet on password/email. |
| TTLs | Package defaults: access 60min, refresh 2 weeks |
| Existing `/me` Sanctum route | Removed; replaced by `GET /auth/me` for the Patient guard |

## Architecture

```
Client                 API
  │  POST /auth/login {email, password}
  ├─────────────────────► LoginController → LoginAction
  │                         └─ guard('api')->attempt() → JWT
  │  200 { access_token, token_type: "bearer", expires_in }
  │ ◄─────────────────────┘
  │
  │  GET /appointments    Authorization: Bearer <jwt>
  ├─────────────────────► auth:api middleware → JWTGuard
  │                         └─ resolves Patient from `sub` claim
  │                       ListAppointmentController
  │                         └─ Appointment::where('patient_id', $patient->id)
  │  200 [...]
  │ ◄─────────────────────┘
  │
  │  GET /appointments/{appointment}    Bearer <jwt>
  ├─────────────────────► auth:api → route binding scoped to patient
  │                         └─ $patient->appointments()->findOrFail($id)
  │                       (other patient's id → 404)
  │  GetAppointmentController → 200
  │ ◄─────────────────────┘
  │
  │  POST /auth/refresh   Bearer <jwt>
  │  POST /auth/logout    Bearer <jwt>   (invalidates current token)
```

- **Guard:** `api` — driver `jwt`, provider `patients`. Default guard switched from `web` to `api`.
- **Provider:** new `patients` Eloquent provider for `Patient::class`.
- **Middleware:** `auth:api` on `/auth/logout`, `/auth/refresh`, `/auth/me`, and the entire `/appointments` group.

## Components

### Patient model
`src/Patients/Domain/Models/Patient.php`

- Change base class from `Illuminate\Database\Eloquent\Model` to `Lightitlabs\Models\JWTAuthenticatable` (provided by the package; extends Laravel `Authenticatable` + implements `JWTSubject`).
- Add `use Notifiable;`.
- `$hidden = ['password', 'remember_token']`.
- `casts()` returns `['password' => 'hashed', 'email_verified_at' => 'immutable_datetime']`.
- Keep the existing lowercase-email accessor and the `appointments()` HasMany relation.

### Migration
`database/migrations/2026_05_11_000000_add_auth_columns_to_patients_table.php`

```php
Schema::table('patients', function (Blueprint $table): void {
    $table->string('password')->after('email');
    $table->timestamp('email_verified_at')->nullable()->after('password');
    $table->rememberToken()->after('email_verified_at');
});
```

Down: drop the three columns.

### `config/auth.php`

- New guard `api`: `driver=jwt`, `provider=patients`.
- New provider `patients`: `eloquent`, model `\Lightit\Patients\Domain\Models\Patient::class`.
- Default guard: `'guard' => env('AUTH_GUARD', 'api')`.

`.env.example` additions:
- `AUTH_GUARD=api`
- `JWT_SECRET=` (left blank; populated by `php artisan jwt:secret` during install)

### One-time scaffolding

Run `php artisan auth:setup` and pick JWT. The installer will:
- Add `php-open-source-saver/jwt-auth:^2.0` to composer.
- Publish `config/jwt.php`.
- Run `jwt:secret` and `jwt:generate-certs --algo=rsa --bits=4096 --sha=512`.
- Write the following files (templated for the `Users` model — we adapt them after):
  - `src/Authentication/App/Controllers/{Login,Logout,Refresh}Controller.php`
  - `src/Authentication/App/Requests/LoginRequest.php`
  - `src/Authentication/App/Resources/LoginResource.php`
  - `src/Authentication/Domain/Actions/{Login,LoginByUser,Logout}Action.php`
  - `src/Authentication/Domain/DataTransferObjects/{Login,Credentials}Dto.php`

#### Post-install edits we own

1. **`LoginByUserAction`** — replace `use Lightit\Users\Domain\Models\User;` with `use Lightit\Patients\Domain\Models\Patient;` and update the type-hint.
2. **`config/auth.php`** — edit by hand to the structure described above. The installer publishes `config/jwt.php` but does not modify `config/auth.php`, so we add the `api` guard and `patients` provider ourselves (preserving the existing `users` provider; making `api` the default).
3. **Add `RefreshController` + `RefreshAction`** — not scaffolded by the package. Action calls `auth('api')->refresh()` and returns a `LoginDto`.
4. **Add `MeController`** under `src/Authentication/App/Controllers/MeController.php` returning `PatientResource::make(auth('api')->user())`.

### Updated `POST /patients` (registration)

- `StorePatientRequest`: add `'password' => ['required', 'string', \Illuminate\Validation\Rules\Password::default()]`.
- `StorePatientDto`: add `public readonly string $password`.
- `StorePatientAction`: pass `password` through to `Patient::create(...)`. Hashing handled by the model's `hashed` cast.
- Endpoint stays public (no `auth:api`) — this *is* the registration flow.
- Response shape unchanged (`PatientResource`, no token issued — client logs in separately).

### Routes (`routes/api.php`)

```php
use Lightit\Authentication\App\Controllers\LoginController;
use Lightit\Authentication\App\Controllers\LogoutController;
use Lightit\Authentication\App\Controllers\RefreshController;
use Lightit\Authentication\App\Controllers\MeController;

Route::prefix('auth')->group(function (): void {
    Route::post('/login', LoginController::class);

    Route::middleware('auth:api')->group(function (): void {
        Route::post('/logout',  LogoutController::class);
        Route::post('/refresh', RefreshController::class);
        Route::get('/me',       MeController::class);
    });
});

Route::prefix('appointments')->middleware('auth:api')->group(function (): void {
    Route::get('/',  ListAppointmentController::class);
    Route::post('/', StoreAppointmentController::class);
    Route::prefix('{appointment}')->group(function (): void {
        Route::get('/',        GetAppointmentController::class);
        Route::patch('/',      UpdateAppointmentController::class);
        Route::delete('/',     DeleteAppointmentController::class);
        Route::post('/cancel', CancelAppointmentController::class);
    })->whereNumber('appointment');
});
```

The previously existing Sanctum `/me` route on `User` is removed.

### Ownership enforcement (option A — route-model binding)

On the `Appointment` model:

```php
public function resolveRouteBinding($value, $field = null): Model
{
    return $this->newQuery()
        ->where('patient_id', auth('api')->id())
        ->where($field ?? $this->getRouteKeyName(), $value)
        ->firstOrFail();
}
```

- Any `/appointments/{appointment}` route automatically returns 404 for IDs that belong to another patient (no ownership leak).
- `ListAppointmentAction` accepts the authenticated `Patient` and scopes the query: `->where('patient_id', $patient->id)`.
- `StoreAppointmentAction` sets `patient_id := $patient->id` server-side and **ignores** any `patient_id` provided in the request body. Remove `patient_id` from the StoreAppointmentRequest rules and DTO (or hard-overwrite it). If `UpdateAppointmentRequest` accepts `patient_id`, it must also be removed (a patient cannot reassign).

## Data shapes

### `POST /auth/login`

Request:
```json
{ "email": "patient@example.com", "password": "secret123" }
```
200:
```json
{ "data": { "access_token": "<jwt>", "token_type": "bearer", "expires_in": 3600 } }
```
401 (invalid creds, generic — no field-level distinction):
```json
{ "error": { "code": "unauthenticated", "message": "Invalid credentials." } }
```
422 (validation): standard `ValidationFailedException` shape from `Shared\App\Exceptions\Http`.

### `POST /auth/refresh`
Header `Authorization: Bearer <jwt>` → 200 same shape as login. Old token blacklisted.

### `POST /auth/logout`
Header `Authorization: Bearer <jwt>` → 200:
```json
{ "message": "Successfully logged out." }
```
Subsequent use of that token → 401.

### `GET /auth/me`
200: `PatientResource` (current shape: id, name, email).

### `POST /patients` (registration, updated)
Request:
```json
{ "name": "Jane Doe", "email": "jane@example.com", "password": "Str0ng!Pass" }
```
201: `PatientResource`.

### Appointments
No request/response shape changes other than removing `patient_id` from the create/update payloads (server-injected from JWT).

## Error handling

| Scenario | HTTP | Source |
|---|---|---|
| Missing/invalid/expired JWT on protected route | 401 | existing `ExceptionHandler` → `UnauthenticatedException` |
| Invalid login credentials | 401 | `LoginAction` throws `UnauthorizedException` (existing in Shared) |
| Login validation failure | 422 | `ValidationFailedException` (existing) |
| Accessing another patient's appointment | 404 | `firstOrFail` in `resolveRouteBinding` → `ModelNotFoundHttpException` |
| Token blacklisted post-logout | 401 | JWT guard → `UnauthenticatedException` |
| Refresh with invalid token | 401 | JWT guard |

No new exception classes — everything routes through existing handlers.

## Correctness properties

1. **Password hashed at rest.** Any plaintext password assigned to a `Patient` ends up stored as a hash that does not equal the plaintext.
2. **Sensitive attributes hidden.** Serializing a `Patient` (array/JSON) never exposes `password` or `remember_token`.
3. **Login success.** A `Patient` whose email + password match a stored record receives a 200 with a non-empty `access_token`, `token_type: "bearer"`, and a positive `expires_in`.
4. **Login failure is opaque.** Any credential mismatch (unknown email, wrong password, or both) returns 401 with the same generic message — no field-level enumeration.
5. **Case-insensitive email match.** Any case variation of a stored email, with the correct password, logs in successfully.
6. **Logout invalidates the used token.** After `POST /auth/logout`, the token used in that request returns 401 on any subsequent request.
7. **Logout isolates tokens.** A patient with multiple active tokens loses only the one used to logout; other tokens still authenticate.
8. **Ownership isolation.** A patient authenticated as A receives 404 for any `/appointments/{id}` where the appointment's `patient_id ≠ A`. `GET /appointments` (list) returns only A's rows. `POST /appointments` ignores any `patient_id` in the body and writes `patient_id = A`.
9. **Public endpoints stay public.** `/clinics/*`, `/doctors/*`, `/users/*`, and the non-auth `/patients` reads (list, get) continue to return 200 without an `Authorization` header.
10. **Refresh rotates tokens.** `POST /auth/refresh` with a valid token returns a new token; the previous token is rejected on subsequent use.

## Testing strategy

Pest v4 with the Laravel plugin, `RefreshDatabase`, feature tests in `tests/Feature/`.

### Files

```
tests/Feature/Auth/
├── LoginTest.php
├── LogoutTest.php
├── RefreshTest.php
├── MeTest.php
├── RegistrationTest.php
└── PatientModelTest.php

tests/Feature/Appointments/
└── AppointmentAuthTest.php
```

### Coverage

- **LoginTest:** success returns token; wrong email → 401; wrong password → 401; missing email/password → 422; invalid email format → 422; case-insensitive match.
- **LogoutTest:** token works before logout; 401 after logout with same token; a second token belonging to the same patient continues to authenticate.
- **RefreshTest:** refresh issues a new token; the old token is rejected on the next call.
- **MeTest:** returns the authenticated patient resource; 401 without token.
- **RegistrationTest:** `POST /patients` without password → 422; with password creates a patient; subsequent login with that password succeeds.
- **PatientModelTest:** password column does not equal plaintext input; `password` and `remember_token` absent from `toArray()`/JSON.
- **AppointmentAuthTest:**
  - All six appointment routes → 401 without a Bearer token and with a malformed token.
  - `GET /appointments` returns only the authenticated patient's appointments.
  - `GET/PATCH/DELETE /appointments/{id}` and `POST /appointments/{id}/cancel` on another patient's appointment → 404.
  - `POST /appointments` ignores `patient_id` in the body; stored `patient_id` equals the authenticated patient's id.
- **PublicEndpointsTest** (small) — confirm `/clinics`, `/doctors`, `/users`, `GET /patients`, `GET /patients/{id}` still return 200 without auth.

### Factories
- Update `PatientFactory` (or create one if missing) to set a default hashed password (`bcrypt('password')`) so tests can call `actingAs($patient, 'api')` and `LoginTest` can assert against a known plaintext.

## Out of scope

- Email verification flow (column exists but no verification endpoints).
- Password reset / forgot-password (package supports it; not in this slice).
- Role/permission enforcement beyond ownership.
- Patient `/auth/register` separate from `POST /patients`.
- Encrypting email or other PII with ciphersweet.
- JWT auth for the `User` model (only `Patient` is authenticatable in this slice).
- Removing the deprecated `.kiro/specs/auth-integration/` Sanctum design (left in place; this spec supersedes it).
