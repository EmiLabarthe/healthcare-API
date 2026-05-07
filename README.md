# Healthcare API — Endpoint Reference

**Base URL**: `http://localhost/api`  
*(Use `http://localhost:8000/api` when running via `php artisan serve`)*

All requests and responses use `application/json`. List endpoints return paginated results with `data`, `links`, and `meta` keys.

---

## Auth

### GET /me
Get the currently authenticated user. Requires a Bearer token.

```bash
curl -X GET http://localhost/api/me \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

**Response 200**
```json
{
  "data": {
    "id": 1,
    "name": "Jane Smith",
    "email_address": "jane@example.com"
  }
}
```

---

## Users

### GET /users
List all users (paginated).

```bash
curl -X GET http://localhost/api/users \
  -H "Accept: application/json"
```

**Response 200**
```json
{
  "data": [
    { "id": 1, "name": "Jane Smith", "email_address": "jane@example.com" }
  ],
  "links": { "first": "...", "last": "...", "prev": null, "next": null },
  "meta": { "current_page": 1, "total": 1 }
}
```

---

### POST /users
Create a new user.

| Field | Type | Rules |
|---|---|---|
| `name` | string | required, min:4, max:80 |
| `email_address` | string | required, unique, valid email, max:100 |
| `password` | string | required, confirmed, meets default password rules |
| `password_confirmation` | string | required |

```bash
curl -X POST http://localhost/api/users \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Jane Smith",
    "email_address": "jane@example.com",
    "password": "Secret1234!",
    "password_confirmation": "Secret1234!"
  }'
```

**Response 201**
```json
{
  "data": {
    "id": 1,
    "name": "Jane Smith",
    "email_address": "jane@example.com"
  }
}
```

---

### GET /users/{id}
Get a single user. Also returns soft-deleted users.

```bash
curl -X GET http://localhost/api/users/1 \
  -H "Accept: application/json"
```

**Response 200**
```json
{
  "data": {
    "id": 1,
    "name": "Jane Smith",
    "email_address": "jane@example.com"
  }
}
```

---

### PATCH /users/{id}
Update an existing user. All fields are optional.

| Field | Type | Rules |
|---|---|---|
| `name` | string | optional, min:4, max:80 |
| `email_address` | string | optional, unique (ignores self), max:100 |
| `password` | string | optional, confirmed |
| `password_confirmation` | string | required if `password` sent |

```bash
curl -X PATCH http://localhost/api/users/1 \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Jane Doe"
  }'
```

**Response 200**
```json
{
  "data": {
    "id": 1,
    "name": "Jane Doe",
    "email_address": "jane@example.com"
  }
}
```

---

### DELETE /users/{id}
Delete a user.

```bash
curl -X DELETE http://localhost/api/users/1 \
  -H "Accept: application/json"
```

**Response 204** — No content.

---

## Patients

### GET /patients
List all patients (paginated).

```bash
curl -X GET http://localhost/api/patients \
  -H "Accept: application/json"
```

**Response 200**
```json
{
  "data": [
    { "id": 1, "name": "John Doe", "email": "john@example.com" }
  ],
  "links": { "first": "...", "last": "...", "prev": null, "next": null },
  "meta": { "current_page": 1, "total": 1 }
}
```

---

### POST /patients
Create a new patient.

| Field | Type | Rules |
|---|---|---|
| `name` | string | required, min:4, max:80 |
| `email` | string | required, unique, valid email, max:100 |

```bash
curl -X POST http://localhost/api/patients \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com"
  }'
```

**Response 201**
```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

---

### GET /patients/{id}
Get a single patient.

```bash
curl -X GET http://localhost/api/patients/1 \
  -H "Accept: application/json"
```

**Response 200**
```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

---

### PATCH /patients/{id}
Update a patient. All fields are optional.

| Field | Type | Rules |
|---|---|---|
| `name` | string | optional, min:4, max:80 |
| `email` | string | optional, unique (ignores self), max:100 |

```bash
curl -X PATCH http://localhost/api/patients/1 \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john.updated@example.com"
  }'
```

**Response 200**
```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john.updated@example.com"
  }
}
```

---

### DELETE /patients/{id}
Delete a patient.

```bash
curl -X DELETE http://localhost/api/patients/1 \
  -H "Accept: application/json"
```

**Response 204** — No content.

---

## Doctors

### GET /doctors
List all doctors (paginated).

```bash
curl -X GET http://localhost/api/doctors \
  -H "Accept: application/json"
```

**Response 200**
```json
{
  "data": [
    { "id": 1, "name": "Dr. Alice Brown" }
  ],
  "links": { "first": "...", "last": "...", "prev": null, "next": null },
  "meta": { "current_page": 1, "total": 1 }
}
```

---

### POST /doctors
Create a new doctor.

| Field | Type | Rules |
|---|---|---|
| `name` | string | required, min:4, max:80 |

```bash
curl -X POST http://localhost/api/doctors \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Dr. Alice Brown"
  }'
```

**Response 201**
```json
{
  "data": {
    "id": 1,
    "name": "Dr. Alice Brown"
  }
}
```

---

### GET /doctors/{id}
Get a single doctor.

```bash
curl -X GET http://localhost/api/doctors/1 \
  -H "Accept: application/json"
```

**Response 200**
```json
{
  "data": {
    "id": 1,
    "name": "Dr. Alice Brown"
  }
}
```

---

### PATCH /doctors/{id}
Update a doctor.

| Field | Type | Rules |
|---|---|---|
| `name` | string | optional, min:4, max:80 |

```bash
curl -X PATCH http://localhost/api/doctors/1 \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Dr. Alice Green"
  }'
```

**Response 200**
```json
{
  "data": {
    "id": 1,
    "name": "Dr. Alice Green"
  }
}
```

---

### DELETE /doctors/{id}
Delete a doctor.

```bash
curl -X DELETE http://localhost/api/doctors/1 \
  -H "Accept: application/json"
```

**Response 204** — No content.

---

## Clinics

### GET /clinics
List all clinics (paginated).

```bash
curl -X GET http://localhost/api/clinics \
  -H "Accept: application/json"
```

**Response 200**
```json
{
  "data": [
    { "id": 1, "name": "Central Clinic", "address": "123 Main St, Springfield" }
  ],
  "links": { "first": "...", "last": "...", "prev": null, "next": null },
  "meta": { "current_page": 1, "total": 1 }
}
```

---

### POST /clinics
Create a new clinic.

| Field | Type | Rules |
|---|---|---|
| `name` | string | required, min:4, max:80 |
| `address` | string | required, max:255 |

```bash
curl -X POST http://localhost/api/clinics \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Central Clinic",
    "address": "123 Main St, Springfield"
  }'
```

**Response 201**
```json
{
  "data": {
    "id": 1,
    "name": "Central Clinic",
    "address": "123 Main St, Springfield"
  }
}
```

---

### GET /clinics/{id}
Get a single clinic.

```bash
curl -X GET http://localhost/api/clinics/1 \
  -H "Accept: application/json"
```

**Response 200**
```json
{
  "data": {
    "id": 1,
    "name": "Central Clinic",
    "address": "123 Main St, Springfield"
  }
}
```

---

### PATCH /clinics/{id}
Update a clinic. All fields are optional.

| Field | Type | Rules |
|---|---|---|
| `name` | string | optional, min:4, max:80 |
| `address` | string | optional, max:255 |

```bash
curl -X PATCH http://localhost/api/clinics/1 \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "address": "456 Elm St, Springfield"
  }'
```

**Response 200**
```json
{
  "data": {
    "id": 1,
    "name": "Central Clinic",
    "address": "456 Elm St, Springfield"
  }
}
```

---

### DELETE /clinics/{id}
Delete a clinic.

```bash
curl -X DELETE http://localhost/api/clinics/1 \
  -H "Accept: application/json"
```

**Response 204** — No content.

---

### POST /clinics/{clinic}/doctors/{doctor}
Attach a doctor to a clinic. Both IDs must refer to existing records.

```bash
curl -X POST http://localhost/api/clinics/1/doctors/1 \
  -H "Accept: application/json"
```

**Response 201**
```json
{
  "data": {
    "clinic_id": 1,
    "doctor_id": 1
  }
}
```

---

### DELETE /clinics/{clinic}/doctors/{doctor}
Detach a doctor from a clinic.

```bash
curl -X DELETE http://localhost/api/clinics/1/doctors/1 \
  -H "Accept: application/json"
```

**Response 204** — No content.

---

## Appointments

### GET /appointments
List all appointments (paginated).

```bash
curl -X GET http://localhost/api/appointments \
  -H "Accept: application/json"
```

**Response 200**
```json
{
  "data": [
    {
      "id": 1,
      "doctor_id": 1,
      "patient_id": 1,
      "clinic_id": 1,
      "starts_at": "2026-06-01T09:00:00.000000Z",
      "ends_at": "2026-06-01T10:00:00.000000Z",
      "status": "scheduled"
    }
  ],
  "links": { "first": "...", "last": "...", "prev": null, "next": null },
  "meta": { "current_page": 1, "total": 1 }
}
```

---

### POST /appointments
Schedule a new appointment.

| Field | Type | Rules |
|---|---|---|
| `doctor_id` | integer | required, must exist in doctors |
| `patient_id` | integer | required, must exist in patients |
| `clinic_id` | integer | required, must exist in `clinic_doctor` for the given doctor |
| `starts_at` | datetime | required, after or equal to now |
| `ends_at` | datetime | required, after `starts_at` |

> The doctor and patient must not have another appointment overlapping the given time range.

```bash
curl -X POST http://localhost/api/appointments \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "doctor_id": 1,
    "patient_id": 1,
    "clinic_id": 1,
    "starts_at": "2026-06-01 09:00:00",
    "ends_at": "2026-06-01 10:00:00"
  }'
```

**Response 201**
```json
{
  "data": {
    "id": 1,
    "doctor_id": 1,
    "patient_id": 1,
    "clinic_id": 1,
    "starts_at": "2026-06-01T09:00:00.000000Z",
    "ends_at": "2026-06-01T10:00:00.000000Z",
    "status": "scheduled"
  }
}
```

---

### GET /appointments/{id}
Get a single appointment.

```bash
curl -X GET http://localhost/api/appointments/1 \
  -H "Accept: application/json"
```

**Response 200**
```json
{
  "data": {
    "id": 1,
    "doctor_id": 1,
    "patient_id": 1,
    "clinic_id": 1,
    "starts_at": "2026-06-01T09:00:00.000000Z",
    "ends_at": "2026-06-01T10:00:00.000000Z",
    "status": "scheduled"
  }
}
```

---

### PATCH /appointments/{id}
Update an existing appointment. All fields are optional.

| Field | Type | Rules |
|---|---|---|
| `doctor_id` | integer | optional, must exist in doctors |
| `patient_id` | integer | optional, must exist in patients |
| `clinic_id` | integer | optional, must be attached to the (updated) doctor |
| `starts_at` | datetime | optional |
| `ends_at` | datetime | optional, after `starts_at` |

> Overlap validation runs against the effective time range (merged with existing values), excluding the appointment being updated.

```bash
curl -X PATCH http://localhost/api/appointments/1 \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "ends_at": "2026-06-01 10:30:00"
  }'
```

**Response 200**
```json
{
  "data": {
    "id": 1,
    "doctor_id": 1,
    "patient_id": 1,
    "clinic_id": 1,
    "starts_at": "2026-06-01T09:00:00.000000Z",
    "ends_at": "2026-06-01T10:30:00.000000Z",
    "status": "scheduled"
  }
}
```

---

### DELETE /appointments/{id}
Cancel an appointment (soft-delete, sets status to `cancelled`).

```bash
curl -X DELETE http://localhost/api/appointments/1 \
  -H "Accept: application/json"
```

**Response 204** — No content.

---

## Common Error Responses

**422 Unprocessable Entity** — Validation failed.
```json
{
  "message": "The name field is required.",
  "errors": {
    "name": ["The name field is required."]
  }
}
```

**404 Not Found** — Resource does not exist.
```json
{
  "message": "No query results for model [...]."
}
```

**401 Unauthorized** — Missing or invalid Bearer token (auth-protected routes only).
```json
{
  "message": "Unauthenticated."
}
```
