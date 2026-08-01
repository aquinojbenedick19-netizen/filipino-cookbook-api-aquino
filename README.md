## Filipino Cookbook API

## Description
The Filipino Cookbook API is a RESTful API designed to manage and retrieve information about Filipino dishes. It provides structured data including food names, categories, origins, ingredients, and cooking instructions.

### Purpose of the API
To provide a centralized system for storing and retrieving Filipino recipe data.

### Type of Information Provided
- Food names
- Categories (e.g., Main Dish, Dessert)
- Ingredients
- Cooking instructions

### Intended Users
- Developers building food or recipe applications
- Students learning API development
- Anyone interested in Filipino cuisine

### Main Functions
- Retrieve all foods
- Retrieve a specific food
- Add new food
- Update existing food
- Delete food
- Authenticate API requests

### Technologies Used
- PHP
- Slim Framework
- MySQL Workbench
- Composer
- JSON
- Apache (XAMPP)

## Features

- Retrieve Filipino foods
- Retrieve food categories
- Retrieve food origins
- Retrieve ingredients
- View details of a specific food
- Add new food entries
- Update food records
- Delete food records
- Token-based authentication
- JSON formatted responses

## Technologies Used

- PHP
- Slim Framework
- MySQL Workbench
- Composer
- JSON
- Apache
- XAMPP
- Thunder Client
- Git
- GitHub

## Installation Instructions

1. Clone the repository:
git clone https://github.com/aquinojbenedick19-netizen/filipino-cookbook-api-aquino.git

2. Navigate to the project folder:
cd filipino-cookbook-api-aquino

3. Install dependencies:
composer install

4. Move the project to your server directory (e.g., htdocs for XAMPP)

5. Start Apache and MySQL using XAMPP

6. Open the project in your browser:
http://localhost/filipino-cookbook-api-aquino/public

## Database Setup

- Database Name: cookbook_db
- SQL File: /database/cookbook_db.sql

### Tables:
- foods
- categories
- origins
- ingredients
- food_ingredients

### Relationships:
categories -> foods <- origins  
foods -> food_ingredients <- ingredients  

### Steps:
1. Open phpMyAdmin or MySQL Workbench
2. Create a database named `cookbook_db`
3. Import the SQL file located in `/database/cookbook_db.sql`

## Base URL
http://localhost/filipino-cookbook-api-aquino/public/api

## Authentication
This API uses token-based authentication.

### Header Format:
Authorization: Bearer dmmmsu-cookbook-token-2026

### Notes:
- Requests without a token will return 401 Unauthorized
- Invalid tokens will also return 401

### GET /api/foods
Description: Retrieve all foods

Headers:
Authorization: Bearer dmmmsu-cookbook-token-2026
Accept: application/json

Example Request:
GET http://localhost/filipino-cookbook-api-aquino/public/api/foods

Example Response:
{
  "status": "success",
  "data": [
    {
      "food_id": 1,
      "food_name": "Adobo",
      "category": "Main Dish",
      "origin": "Philippines"
    }
  ]
}

### GET /api/foods/{id}
Description: Retrieve a specific food

Example:
GET /api/foods/1

### POST /api/foods
Description: Add a new food

Body (JSON):
{
  "food_name": "Sinigang",
  "category_id": 1,
  "origin_id": 1,
  "instructions": "Cook with tamarind broth",
  "ingredient_ids": [1,2,3]
}

### PUT /api/foods/{id}
Description: Update a food entry

### DELETE /api/foods/{id}
Description: Delete a food entry

## HTTP Status Codes
| Code | Meaning |
|------|--------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 500 | Server Error |

## Developer Information
Name: AQUINO, John Benedick C.  
Course & Section: BSInfoTech 4-A  
GitHub: https://github.com/aquinojbenedick19-netizen  
Repository: https://github.com/aquinojbenedick19-netizen/filipino-cookbook-api-aquino  
Date Completed: 

-----------

1. GET /api/foods/search?name=
- Purpose: Search foods by name

2. GET /api/categories/{id}/foods
- Purpose: Get foods by category

3. GET /api/foods/{id}/ingredients
- Purpose: Get ingredients of a food
