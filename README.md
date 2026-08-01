# Filipino Cookbook API

## Description

The Filipino Cookbook API is a RESTful API designed to manage and retrieve information about Filipino dishes. It provides structured data including food names, categories, origins, ingredients, and cooking instructions.

### Purpose of the API

To provide a centralized system for storing and retrieving Filipino recipe data.

### Intended Users

- Developers building food or recipe applications
- Students learning API development
- Anyone interested in Filipino cuisine

## Features

- Retrieve all Filipino foods, with category, origin, and ingredients included
- Retrieve a specific food by ID
- Search foods by name
- Retrieve food categories, origins, and ingredients
- Add, update, and delete food entries
- Token-based authentication on all API routes
- JSON-formatted responses
- CORS-enabled for use with a separately hosted frontend (see `app.html`)

## Technologies Used

- PHP
- Slim Framework
- MySQL / MySQL Workbench
- Composer
- JSON
- Apache (XAMPP)
- Git & GitHub
- Thunder Client (for manual API testing)

## Installation Instructions

1. Clone the repository:
   ```
   git clone https://github.com/aquinojbenedick19-netizen/filipino-cookbook-api-aquino.git
   ```
2. Navigate to the project folder:
   ```
   cd filipino-cookbook-api-aquino
   ```
3. Install dependencies:
   ```
   composer install
   ```
4. Move the project into your server directory (e.g. `htdocs` for XAMPP).
5. Configure your database credentials in `config/db.php` (database name, username, password).
6. Start Apache and MySQL using the XAMPP Control Panel.
7. Open the project in your browser:
   ```
   http://localhost/filipino-cookbook-api-aquino/public
   ```

## Database Setup

- **Database name:** `cookbook_db`
- **SQL file:** `/database/cookbook_db.sql`

### Tables

- `foods`
- `categories`
- `origins`
- `ingredients`
- `food_ingredients`

### Relationships

```
categories → foods ← origins
foods → food_ingredients ← ingredients
```

### Setup steps

1. Open phpMyAdmin or MySQL Workbench.
2. Create a database named `cookbook_db`.
3. Import the SQL file located at `/database/cookbook_db.sql`.

## Base URL

```
http://localhost/filipino-cookbook-api-aquino/public/api
```

## Authentication

This API uses token-based authentication. Every `/api/*` route requires the header below; the root route (`/`) is public and does not require it.

**Header format:**
```
Authorization: Bearer dmmmsu-cookbook-token-2026
```

**Notes:**
- Requests without a token return `401 Unauthorized`.
- Requests with an invalid token also return `401 Unauthorized`.

## Endpoints

### GET /api/foods

Retrieve all foods, including their category, origin, and ingredient list.

**Headers:**
```
Authorization: Bearer dmmmsu-cookbook-token-2026
```

**Example request:**
```
GET http://localhost/filipino-cookbook-api-aquino/public/api/foods
```

**Example response:**
```json
[
  {
    "food_id": 1,
    "food_name": "Adobo",
    "category_name": "Main Dish",
    "origin_name": "Philippines",
    "instructions": "Marinate meat in soy sauce, vinegar, and garlic. Simmer until tender.",
    "ingredients": ["Pork", "Soy sauce", "Vinegar", "Garlic", "Bay leaves"]
  }
]
```

### GET /api/foods/{id}

Retrieve a specific food by ID. Returns the same shape as above (a single object, not an array), or a `404` with an error message if the ID doesn't exist.

**Example:**
```
GET /api/foods/1
```

### GET /api/foods/search/{name}

Search foods by name (partial match).

**Example:**
```
GET /api/foods/search/adobo
```

**Example response:**
```json
[
  { "food_id": 1, "food_name": "Chicken Adobo" }
]
```

### GET /api/categories

Retrieve all available categories (e.g. Main Dish, Dessert, Soup).

### GET /api/origins

Retrieve all available origins/regions (e.g. Bicol Region, Philippines).

### GET /api/ingredients

Retrieve all available ingredients.

### POST /api/foods

Add a new food entry.

**Body (JSON):**
```json
{
  "food_name": "Sinigang",
  "category_id": 1,
  "origin_id": 1,
  "instructions": "Cook with tamarind broth",
  "ingredient_ids": [1, 2, 3]
}
```

**Example response (201 Created):**
```json
{
  "status": "success",
  "message": "Food added successfully."
}
```

Returns `400 Bad Request` if any required field is missing or `ingredient_ids` isn't a valid array of numeric IDs.

### PUT /api/foods/{id}

Update an existing food entry. Accepts the same body shape as `POST /api/foods`; `ingredient_ids` is optional — if provided, it replaces the food's full ingredient list. Returns `404` if the food doesn't exist.

### DELETE /api/foods/{id}

Delete a food entry (and its ingredient associations). Returns `404` if the food doesn't exist.

## HTTP Status Codes

| Code | Meaning |
|------|---------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 404 | Not Found |
| 500 | Server Error |

## Frontend

A minimal frontend (`app.html`) is included for browsing, searching, and managing the cookbook through this API. It can be run independently (e.g. via VS Code's Live Server) as long as it points at a running instance of this API.

## Planned / Future Endpoints

Not yet implemented — ideas for future work:

- `GET /api/categories/{id}/foods` — retrieve all foods belonging to a specific category
- `GET /api/foods/{id}/ingredients` — retrieve just the ingredient list for a specific food (currently included as part of `GET /api/foods/{id}`, but could be split out as its own endpoint)

## Developer Information

- **Name:** Aquino, John Benedick C.
- **Course & Section:** BSInfoTech 4-A
- **GitHub:** https://github.com/aquinojbenedick19-netizen
- **Repository:** https://github.com/aquinojbenedick19-netizen/filipino-cookbook-api-aquino
- **Date Completed:** August 1, 2026
