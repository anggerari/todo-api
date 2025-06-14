# Todo List API

## Getting Started

### Prerequisites

* PHP >= 8.1
* Composer
* A database (e.g., MySQL, PostgreSQL, SQLite)
* Laravel Valet, Docker, or a local PHP development server

### Installation and Setup

1. **Clone the repository:**

```bash
git clone <your-repository-url>
cd todo-api
```

2. **Install PHP dependencies:**

```bash
composer install
```

3. **Create your environment file:**

```bash
cp .env.example .env
```

4. **Generate an application key:**

```bash
php artisan key:generate
```

5. **Configure your database:**

Open the `.env` file and update the `DB_*` variables with your database credentials.

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=todo_app
DB_USERNAME=root
DB_PASSWORD=
```

6. **Run the database migrations:**

```bash
php artisan migrate
```

7. **Start the local server:**

```bash
php artisan serve
```

The API will be available at `http://127.0.0.1:8000`.

---

## API Documentation

All API requests must include the following headers:

```http
Accept: application/json
Content-Type: application/json
```

**Base URL:**
`http://127.0.0.1:8000/api`

---

### 1. Create a New Todo

* **Method:** `POST`
* **Endpoint:** `/todos`
* **Description:** Store a newly created todo.

#### Request Body Example

```json
{
    "title": "My First Todo",
    "assignee": "Angger Ari",
    "due_date": "2025-09-15",
    "priority": "high",
    "status": "pending",
    "time_tracked": 0
}
```

#### Success Response (201 Created)

```json
{
    "data": {
        "id": 1,
        "title": "My First Todo",
        "assignee": "Angger Ari",
        "dueDate": "2025-09-15",
        "status": "pending",
        "priority": "high",
        "timeTracked": 0,
        "createdAt": "2025-06-14T10:30:00.000000Z",
        "updatedAt": "2025-06-14T10:30:00.000000Z"
    }
}
```

#### Validation Error (422 Unprocessable Entity)

```json
{
    "message": "The due date cannot be in the past.",
    "errors": {
        "title": ["A title is required to create a new todo."],
        "due_date": ["The due date cannot be in the past."]
    }
}
```

---

### 2. Get Todo List (Paginated)

* **Method:** `GET`
* **Endpoint:** `/todos`
* **Description:** Retrieve a paginated list of todos.

#### Query Parameters

| Parameter | Type   | Description             |
| --------- | ------ | ----------------------- |
| page      | Int    | Page number             |
| status    | String | Filter by todo status   |
| priority  | String | Filter by todo priority |

#### Example Request

```http
GET /todos?page=2&status=pending&priority=high
```

#### Success Response (200 OK)

```json
{
    "data": [
        {
            "id": 1,
            "title": "My First Todo",
            "assignee": "Angger Ari"
        }
    ],
    "links": {
        "first": "http://127.0.0.1:8000/api/todos?page=1",
        "last": "http://127.0.0.1:8000/api/todos?page=5",
        "prev": null,
        "next": "http://127.0.0.1:8000/api/todos?page=2"
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 5,
        "per_page": 15,
        "to": 15,
        "total": 75
    }
}
```

---

### 3. Export Todos to Excel

* **Method:** `GET`
* **Endpoint:** `/todos/export`
* **Description:** Export todos to Excel with filtering.

#### Available Filters

| Parameter   | Type   | Description                             |
| ----------- | ------ | --------------------------------------- |
| title       | String | Partial match on title                  |
| assignee    | String | Filter by assignee(s) (comma-separated) |
| status      | String | Filter by status (comma-separated)      |
| priority    | String | Filter by priority (comma-separated)    |
| start\_date | Date   | Filter by due\_date start (YYYY-MM-DD)  |
| end\_date   | Date   | Filter by due\_date end (YYYY-MM-DD)    |
| min         | Int    | Minimum time\_tracked value             |
| max         | Int    | Maximum time\_tracked value             |

#### Example Request

```http
GET /todos/export?title=Test&assignee=John,Doe&status=pending,in_progress&priority=low,high&start_date=2025-01-01&end_date=2025-12-31&min=10&max=100
```

#### Response

* Triggers Excel file download (e.g., `todos_report_2025-06-14_10-45.xlsx`).

---

### 4. Get Chart Data

* **Method:** `GET`
* **Endpoint:** `/chart`
* **Description:** Retrieve aggregated data for charts.

#### Query Parameter

| Parameter | Type   | Rules                                                |
| --------- | ------ | ---------------------------------------------------- |
| type      | String | Required. Must be one of: status, priority, assignee |

#### Example Requests & Responses

**By Status**

```http
GET /chart?type=status
```

```json
{
    "status_summary": {
        "pending": 15,
        "open": 0,
        "in_progress": 5,
        "completed": 22
    }
}
```

**By Priority**

```http
GET /chart?type=priority
```

```json
{
    "priority_summary": {
        "low": 25,
        "medium": 10,
        "high": 0
    }
}
```

**By Assignee**

```http
GET /chart?type=assignee
```

```json
{
    "assignee_summary": {
        "John Doe": {
            "total_todos": 10,
            "total_pending_todos": 3,
            "total_timetracked_completed_todos": 120
        },
        "Jane Smith": {
            "total_todos": 12,
            "total_pending_todos": 1,
            "total_timetracked_completed_todos": 250
        }
    }
}
```

---

## Running Tests

To run the test suite:

```bash
php artisan test
```

For a more readable output:

```bash
php artisan test --testdox
```
