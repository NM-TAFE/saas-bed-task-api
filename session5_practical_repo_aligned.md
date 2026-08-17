# Session 5 Practical — MongoDB Migration for the Small Task API

## Purpose

In this session, you will migrate the small Task API's runtime persistence from MySQL to MongoDB for:

- `users`
- `tasks`
- Sanctum `personal_access_tokens`

The goal is to keep the public Task API familiar while changing where the application stores its data.

## Repository Note

This practical matches the repository currently open in VS Code.

Important facts about this codebase:

- The live Task flow is currently:

```text
Route
→ Request
→ Controller
→ Model
→ Resource
→ Response
```

- `TaskController` does **not** currently use the Task jobs.
- `NewTask` and the Task jobs exist in the repository, but they are not part of the live Task request flow.
- The current automated test harness is SQL/SQLite-based baseline coverage, not a MongoDB verification suite.

For this introductory migration, we will update the live runtime path used by the application and keep the scope small.

## Scope

### We Will Change

- install Laravel MongoDB support
- register the MongoDB provider
- add a MongoDB connection
- move `User` to MongoDB
- move Sanctum personal access tokens to MongoDB
- remove the login transaction that assumes relational database behaviour
- move `Task` to MongoDB
- add `tag_ids` to Task documents
- update Task validation
- expose `tag_ids` through `TaskResource`
- manually test authentication and Task routes
- inspect the resulting MongoDB documents

### We Will Not Change

- API route structure
- controller responsibilities
- create a `Tag` model
- create a `tags` collection
- redesign the Task feature around the existing unused jobs/payload classes
- migrate cache, session, or queue infrastructure to MongoDB
- redesign relationships beyond a simple `tag_ids` array

## Tags in This Practical

We will add this field to a Task document:

```json
{
  "tag_ids": [
    "01TAG...",
    "01TAG..."
  ]
}
```

We will **not** create a `Tag` model or `tags` collection in this session.

For now, `tag_ids` is only a simple array of future Tag IDs.

---

# Checkpoint 1 — Establish the Baseline

## Open

```text
app/Models/Task.php
app/Models/User.php
app/Models/PersonalAccessToken.php
app/Http/Api/Controllers/Auth/LoginController.php
app/Http/Api/Controllers/Tasks/TaskController.php
app/Http/Api/Requests/Tasks/StoreTaskRequest.php
app/Http/Api/Requests/Tasks/UpdateTaskRequest.php
app/Http/Api/Resources/TaskResource.php
routes/api.php
routes/api/tasks.php
bootstrap/providers.php
config/database.php
```

## Observe

Before the migration:

- `Task` extends Laravel's SQL Eloquent model
- `User` extends Laravel's SQL authentication model
- the custom `PersonalAccessToken` model pins tokens to MySQL
- `LoginController` wraps token creation in a database transaction
- `TaskController` performs direct model persistence
- Task validation currently checks `assigned_to` against `users,id`
- `TaskResource` does not expose `tag_ids`

## Run the Existing App

Use the existing Session 4 flow as a baseline.

Useful commands:

```bash
php artisan route:list
php artisan test
```

Important:

- `php artisan test` is a **baseline SQL/SQLite check only**
- it is not the final MongoDB verification step for this practical

Confirm that you can currently:

```text
POST   /api/v1/auth/login
GET    /api/v1/tasks
POST   /api/v1/tasks
GET    /api/v1/tasks/{task}
PATCH  /api/v1/tasks/{task}
DELETE /api/v1/tasks/{task}
```

Record one successful Task response so you can compare it later.

---

# Checkpoint 2 — Install and Configure MongoDB

## Check the PHP Extension

Laravel MongoDB requires the MongoDB PHP extension.

Run:

```bash
php --ri mongodb
```

If PHP reports that the extension is missing, stop and enable/install it in the PHP version used by this project.

## Install the Package

Run:

```bash
composer require mongodb/laravel-mongodb:^5.8
```

## Register the Providers

Open:

```text
bootstrap/providers.php
```

Update it so the application registers:

- `AppServiceProvider`
- `MongoDB\Laravel\MongoDBServiceProvider`
- `App\Providers\SanctumServiceProvider`

This repository already contains `SanctumServiceProvider`, but it is not currently registered.

## Add the MongoDB Connection

Open:

```text
config/database.php
```

Add a `mongodb` connection inside `connections`:

```php
'mongodb' => [
    'driver' => 'mongodb',
    'dsn' => env('DB_URI', 'mongodb://127.0.0.1:27017/'),
    'database' => env('DB_DATABASE', 'myjamjar'),
],
```

Keep the existing SQL connections in the file for reference.

## Update the Environment

Update `.env`:

```text
DB_CONNECTION=mongodb
DB_URI=mongodb://127.0.0.1:27017/
DB_DATABASE=myjamjar
```

Keep unrelated persistence out of scope for this practical:

```text
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

## Clear Config

Run:

```bash
php artisan config:clear
php artisan about
```

Do **not** run:

```bash
php artisan migrate
```

For this introductory practical, MongoDB collections will be created when documents are first written.

---

# Checkpoint 3 — Move User Authentication to MongoDB

## Update the User Model

Open:

```text
app/Models/User.php
```

Replace:

```php
use Illuminate\Foundation\Auth\User as Authenticatable;
```

with:

```php
use MongoDB\Laravel\Auth\User as Authenticatable;
```

Add:

```php
protected $connection = 'mongodb';

protected $table = 'users';
```

Preserve the existing:

- `HasUlids`
- `HasApiTokens`
- `HasFactory`
- `Notifiable`
- `#[Fillable(...)]`
- `#[Hidden(...)]`
- `casts()`

## Why `id` Still Works

MongoDB stores the primary key in `_id`, but the Laravel MongoDB package exposes it through the model's normal `id` property.

That means the application can continue to work with `id` instead of teaching `_id` as a public API detail in this session.

## Update the Sanctum Token Model

Open:

```text
app/Models/PersonalAccessToken.php
```

Replace the SQL-specific model with a MongoDB-compatible version that:

- extends `Laravel\Sanctum\PersonalAccessToken`
- uses `MongoDB\Laravel\Eloquent\DocumentModel`
- sets `protected $connection = 'mongodb';`
- sets `protected $table = 'personal_access_tokens';`
- sets `protected $keyType = 'string';`

## Remove the Login Transaction

Open:

```text
app/Http/Api/Controllers/Auth/LoginController.php
```

This controller currently wraps token creation in a relational database transaction.

For this practical:

- remove `DatabaseManager` from the constructor
- remove the transaction wrapper around `createToken()`
- call `createToken()` directly
- keep the existing JSON token response shape

The goal here is not to make a broader transaction statement about MongoDB. It is to remove an unnecessary relational assumption from this small API.

## Seed MongoDB

Run:

```bash
php artisan db:seed
```

The existing seeder creates:

- sample Tasks
- the seeded client User

Current seeded credentials:

```text
client@example.com
password
```

## Confirm Authentication

Test the existing login route:

```text
POST /api/v1/auth/login
```

Confirm that:

1. the User is read from MongoDB
2. the password is accepted
3. Sanctum issues a token
4. `personal_access_tokens` is created in MongoDB
5. the token can access a protected Task route

---

# Checkpoint 4 — Move Task Persistence to MongoDB

## Update the Task Model

Open:

```text
app/Models/Task.php
```

Replace:

```php
use Illuminate\Database\Eloquent\Model;
```

with:

```php
use MongoDB\Laravel\Eloquent\Model;
```

Add:

```php
protected $connection = 'mongodb';

protected $table = 'tasks';
```

Keep:

```php
use HasFactory, HasUlids;
```

## Add `tag_ids`

Add `tag_ids` to `$fillable`.

Add an array cast so the Task model treats `tag_ids` as an array field.

The Task document should now be able to store:

```json
{
  "name": "Prepare MongoDB migration",
  "status": "in_progress",
  "tag_ids": ["01TAG-1", "01TAG-2"]
}
```

## Keep the User Relationship

The Task still stores a User ID in `assigned_to`.

Keep the `assignedTo()` relationship, but make sure its imports match the MongoDB-backed model setup used in the file.

The application-facing relationship remains:

```text
Task.assigned_to
→ User.id
```

Remember:

- MongoDB stores the actual key as `_id`
- this practical continues to work with Laravel's `id` property

---

# Checkpoint 5 — Update Task Validation

## StoreTaskRequest

Open:

```text
app/Http/Api/Requests/Tasks/StoreTaskRequest.php
```

Keep the repository's application-facing User validation style:

```php
'assigned_to' => ['nullable', 'string', 'exists:users,id']
```

Add validation for tags:

```php
'tag_ids' => ['nullable', 'array'],
'tag_ids.*' => ['string'],
```

Preserve the repository's current date rule in this file:

```php
'due_date' => ['nullable', 'date_format:d-m-Y']
```

## UpdateTaskRequest

Open:

```text
app/Http/Api/Requests/Tasks/UpdateTaskRequest.php
```

Add:

```php
'tag_ids' => ['sometimes', 'nullable', 'array'],
'tag_ids.*' => ['string'],
```

Preserve the current partial-update style already used in this file.

For this repository-aligned practical, keep the file's existing date rule as it currently exists unless your instructor asks you to standardise date formats separately.

## Important Repository Note

This repository also contains:

```text
app/Http/Payloads/NewTask.php
app/Jobs/Tasks/CreateNewTask.php
app/Jobs/Tasks/UpdateTask.php
app/Jobs/Tasks/DeleteTask.php
```

Those classes are not currently part of the live Task request flow.

To keep Session 5 focused on the actual running application, do **not** redesign the feature around them in this practical.

---

# Checkpoint 6 — Keep the Controller Simple

## TaskController

Open:

```text
app/Http/Api/Controllers/Tasks/TaskController.php
```

This controller already:

- lists Tasks
- creates Tasks from `$request->validated()`
- updates Tasks from `$request->validated()`
- deletes Tasks directly
- returns `TaskResource`

That is the live runtime flow in this repository, so keep that structure.

Because `tag_ids` is now validated and fillable, the controller does not need a new architectural layer for this session.

## What Stays the Same

Keep:

- route structure
- controller method responsibilities
- route model binding
- `with('assignedTo')`
- `TaskResource`
- Sanctum route protection

---

# Checkpoint 7 — Expose `tag_ids`

## Update TaskResource

Open:

```text
app/Http/Api/Resources/TaskResource.php
```

Add:

```php
'tag_ids' => $this->tag_ids ?? [],
```

Keep the rest of the resource shape familiar to API consumers.

The goal is to expose the new field without redesigning the Task response.

---

# Checkpoint 8 — Manual Verification

## Seed and Authenticate

If needed, seed again:

```bash
php artisan db:seed
```

Log in:

```text
POST /api/v1/auth/login
```

Use the returned bearer token for the protected Task routes.

## Test the Task Routes

Confirm that you can still use:

```text
GET    /api/v1/tasks
POST   /api/v1/tasks
GET    /api/v1/tasks/{task}
PATCH  /api/v1/tasks/{task}
DELETE /api/v1/tasks/{task}
```

## Example Create Body

```json
{
  "name": "Prepare MongoDB migration",
  "description": "Move the small Task API to MongoDB",
  "status": "in_progress",
  "due_date": "24-08-2026",
  "tag_ids": [
    "01TAG-MONGODB",
    "01TAG-API"
  ]
}
```

If you include `assigned_to`, use the seeded User's Laravel `id` value.

## Inspect MongoDB

Inspect the MongoDB database and confirm that:

- `users` contains User documents
- `personal_access_tokens` contains Sanctum token documents
- `tasks` contains Task documents

In a Task document, look for:

- `_id`
- `name`
- `description`
- `status`
- `due_date`
- `assigned_to`
- `tag_ids`
- `created_at`
- `updated_at`

---

# Checkpoint 9 — Testing Note

This repository's automated tests are not the final MongoDB verification step for Session 5.

Why:

- the current feature test uses `RefreshDatabase`
- the current `phpunit.xml` is pinned to SQLite in memory
- Laravel MongoDB does not support `RefreshDatabase`

For this practical:

- use automated tests only as the **pre-migration baseline**
- use manual API testing and MongoDB inspection as the **post-migration verification**

---

# Final Discussion

Be prepared to explain:

1. What changed when Users, Tasks, and Sanctum tokens moved to MongoDB?
2. Why does the application still use Laravel-style `id` even though MongoDB stores `_id`?
3. Why did we remove the login transaction?
4. Why did we keep `assigned_to` validating against `users,id`?
5. What does `tag_ids` represent?
6. Why did we not create a `Tag` model or `tags` collection?
7. Why did we leave the unused payload/job classes out of the live migration?
8. Why is manual API verification the important test step for this repository?

## Summary

The completed Session 5 practical for this repository should leave the API recognisable while changing runtime persistence to MongoDB for:

```text
users
personal_access_tokens
tasks
```

and adding:

```text
Task.tag_ids
```

without expanding into deeper NoSQL modelling or broader architectural refactoring.
