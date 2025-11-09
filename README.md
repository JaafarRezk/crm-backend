
CRM System — Laravel 12 + Vue  (API + SPA)
A production-ready Customer Relationship Management (CRM) backend (Laravel 12 API) and frontend (Vue 3 SPA) scaffold.
 Implements role-based access (Admin / Manager / Sales Rep), client management, communications logging, follow-ups, notifications, scheduled jobs, audit logging, exports and a clean API → Controller → Service → Request → Resource architecture with Jobs, Events, Listeners.

Table of contents
Project overview


Tech stack


Architecture & patterns


Database schema (summary)


Installation (local)


Environment variables (.env)


Run & development commands


Authentication (JWT)


Important Artisan commands


Testing


API examples (basic)


Folder structure


How to add a new API endpoint (pattern)


Events / Jobs / Listeners (example flow)


Contributing


License



Project overview
This repository contains a backend REST API implemented with Laravel 12 and a frontend SPA built with Vue 3 + Vite. The backend follows a modular separation of concerns:
Controllers: thin, request-specific logic and response shaping.


Services: core business logic and domain operations.


Form Request classes: validation and authorization.


API Resources: transform models to JSON responses.


Jobs / Events / Listeners: asynchronous and decoupled logic (email, notifications, status update jobs).


Audit logging capturing create/update/delete and automated changes.


The schema and migrations are included in database/migrations (users, clients, communications, follow_ups, audit_logs, settings, notifications, spatie permission tables).

Tech stack
PHP 8.2+


Laravel 12


MySQL (recommended) / SQLite (test)


Vue 3 + Vite (frontend)


Pinia (state), Axios (HTTP)


Spatie Laravel Permission (roles & permissions)


tymon/jwt-auth (JWT tokens)


Laravel Queue, Scheduler, Notifications


Composer & NPM


composer.json top packages used:
"laravel/framework": "^12.0"


"spatie/laravel-permission": "^6.23"


"tymon/jwt-auth": "^2.2"



Architecture & patterns
The project uses a consistent flow for API endpoints:
Request -> Controller -> Service -> (Model / Repo) -> Resource -> Response

Requests (app/Http/Requests) contain validation + authorization rules.


Controllers (app/Http/Controllers/Api) do request to service mapping, handle status codes.


Services (app/Services) implement use-cases (createClient, assignClient, recordCommunication, etc).


Resources (app/Http/Resources) serialize models for API responses.


Jobs (app/Jobs) perform heavy/async work (export CSV, send notifications).


Events & Listeners (app/Events, app/Listeners) decouple side effects (dispatch on Communication created → listener updates client last_communication_at, triggers status-check event).


Audit Log: recorded at model events or via services (writes to audit_logs).



Database schema (summary)
Key tables (see database/migrations for full column details):
users


id, name, email (unique), password, phone, user_type (admin|manager|sales_rep), last_login, failed_login_attempts, locked_until, timestamps, softDeletes.


clients


id, name, email, phone, status (New|Active|Hot|Inactive|Cold), assigned_to (foreign users), last_communication_at, timestamps, softDeletes.


communications


id, client_id, created_by, type (call|email|meeting), date, notes, timestamps, softDeletes.


follow_ups


id, client_id, assigned_to, created_by, due_date, status (pending|completed|cancelled), priority, notes, completed_by, completed_at, timestamps, softDeletes.


audit_logs


id, actor_id, resource_type, resource_id, action, changes (json), timestamps.


settings, notifications, plus Spatie permission tables.



Installation (local)
Assuming you have PHP, Composer, Node.js installed.
# clone
git clone https://github.com/your-org/crm-backend.git
cd crm-backend

# install php deps
composer install

# copy env and configure
cp .env.example .env
# edit .env -> DB_*, MAIL_*, REDIS_*, JWT_SECRET etc.

# generate app key
php artisan key:generate

# install node deps for frontend (if in same repo)
npm install

# set up database and migrate
php artisan migrate

# seed initial data (roles + admin user)
php artisan db:seed --class=InitialRolesAndAdminSeeder

# build front-end (or run dev server)
npm run build     # for production build
npm run dev       # for dev (Vite)


Environment variables (.env) — important keys
APP_NAME=CRM
APP_ENV=local
APP_KEY=base64:...
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=crm
DB_USERNAME=root
DB_PASSWORD=

CACHE_DRIVER=file
QUEUE_CONNECTION=database

JWT_SECRET= # generate with php artisan jwt:secret

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null

Run php artisan jwt:secret to generate JWT_SECRET.


Publish Spatie config and migrations if necessary:

 php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate



Run & development commands
Use composer scripts in composer.json:
# full setup (composer + migrate + npm build) - defined in composer scripts
composer run-script setup

# run application (backend)
php artisan serve

# run vite dev server (frontend)
npm run dev

# run scheduler worker (dev)
php artisan schedule:work

# run queue worker
php artisan queue:work --tries=3

# run tests
php artisan test


Authentication (JWT)
This project uses tymon/jwt-auth:
Install package (already in composer.json) and publish config:

 composer require tymon/jwt-auth
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
php artisan jwt:secret


Login route returns access_token and token type Bearer. Protect API routes with auth:api (or jwt.auth) middleware.


Example header for requests:

 Authorization: Bearer <token>


Alternatively you can use Laravel Sanctum (replace auth flow) — choose one method and implement consistently.

Important Artisan commands
php artisan crm:update-client-statuses — run daily (or manually) to set Hot / Inactive statuses.


php artisan queue:work — process queued jobs.


php artisan schedule:work — run scheduler in dev.


php artisan db:seed --class=InitialRolesAndAdminSeeder — seed initial roles & admin.


Add the scheduled command to app/Console/Kernel.php:
$schedule->command('crm:update-client-statuses')->dailyAt('01:00')->withoutOverlapping();
$schedule->job(new \App\Jobs\SendDueFollowUpNotifications())->dailyAt('08:00')->withoutOverlapping();


Testing
Project uses PHPUnit (phpunit ^11) and includes unit + integration tests.
# run all tests
php artisan test

# or vendor/bin/phpunit
vendor/bin/phpunit

Make sure .env.testing or the phpunit.xml DB settings point to a fresh sqlite or test DB to avoid polluting developer DB.

API examples (basic)
Register
POST /api/auth/register
Body: { name, email, password, password_confirmation }

Login
POST /api/auth/login
Body: { email, password }
Response: { access_token, token_type: "Bearer", expires_in }

Clients
GET    /api/clients
POST   /api/clients
GET    /api/clients/{id}
PUT    /api/clients/{id}
DELETE /api/clients/{id}

Communications
POST /api/clients/{client}/communications
GET  /api/clients/{client}/communications

FollowUps
POST /api/follow-ups
GET  /api/follow-ups?assigned_to=...

Run status update
php artisan crm:update-client-statuses

Use Authorization: Bearer <token> header for protected routes.
(Consider publishing a full Postman Collection or OpenAPI/Swagger JSON in /docs.)

Folder structure (selected)
app/
├── Console/
│   └── Commands/UpdateClientStatusCommand.php
├── Events/
├── Jobs/
├── Listeners/
├── Http/
│   ├── Controllers/Api/
│   ├── Requests/
│   └── Resources/
├── Models/
├── Policies/
├── Providers/
├── Repositories/   # optional, if you adopt repo pattern
├── Services/       # business logic
└── Notifications/
database/
├── migrations/
└── seeders/
tests/
├── Feature/
└── Unit/


How to add a new API endpoint (pattern)
Create a Request (app/Http/Requests/Client/StoreClientRequest.php) — validation + authorize().


Create a Service (app/Services/ClientService.php) — implement create(array $data).


Create a Controller (app/Http/Controllers/Api/ClientController.php) — inject service and call method.


Create a Resource (app/Http/Resources/ClientResource.php) — shape the JSON output.


Add route in routes/api.php:

 Route::apiResource('clients', ClientController::class)->middleware('auth:api');


Write tests: unit test for service, feature test for endpoint.


Minimal example (skeleton)
Request
class StoreClientRequest extends FormRequest {
    public function rules() {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
        ];
    }
}

Service
class ClientService {
    public function create(array $data) {
        return Client::create($data);
    }
}

Controller
class ClientController extends Controller {
    protected ClientService $service;
    public function __construct(ClientService $service) { $this->service = $service; }

    public function store(StoreClientRequest $request) {
        $client = $this->service->create($request->validated());
        return new ClientResource($client);
    }
}


Events / Jobs / Listeners (example flow)
Event: CommunicationCreated — fired after a communication is stored.


Listener: UpdateClientLastCommunication — sets client last_communication_at and creates audit log.


Job: SendFollowUpNotificationJob — queued job used to notify user(s) (e.g., when due date approaches).


Use php artisan queue:work to process jobs.



Exports & Large Jobs
Exports (CSV/JSON) are processed as queued jobs to avoid long HTTP requests.


Include an exports table or notifications to notify the user when export is ready and provide download link.



Contributing
Fork repo → create feature branch feature/your-feature → push.


Open PR with description & related issue (if any).


Follow PSR-12 coding style; run composer test and npm run lint (if configured).


Include tests for new features.


Add a CONTRIBUTING.md for extended contribution rules, PR template and code review checklist.

Useful composer / npm scripts (present in composer.json)
composer run-script setup — installs PHP deps, copies .env, migrates and builds frontend.


composer run-script dev — concurrently runs server, queue, pail (logs), and Vite dev.


composer run-script test — runs test suit.



Troubleshooting & notes
If you change Spatie config, run:

 php artisan config:clear
php artisan cache:clear
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate


If tests hit SQLite enum issues, ensure migrations match test expectations or adapt factories/tests accordingly (SQLite implements enum as CHECK).


