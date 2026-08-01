<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/db.php';

use Slim\Factory\AppFactory;

$app = AppFactory::create();
$app->addBodyParsingMiddleware();

$app->setBasePath('/filipino-cookbook-api-aquino/public');

header("Content-Type: application/json");

// CORS: allow the frontend to call this API even when served from a different
// origin/port (e.g. VS Code Live Server on 127.0.0.1:5500 vs the API on localhost).
// For local dev this is wide open; lock this down to a specific origin in production.
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Browsers send a preflight OPTIONS request before cross-origin POST/PUT/DELETE
// calls with custom headers (like Authorization). Respond to it here, before
// routing, or Slim will 404/401 it and the browser will block the real request.
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}


$authMiddleware = function ($request, $handler) {
    $authHeader = $request->getHeaderLine('Authorization');

    if (!hash_equals("Bearer dmmmsu-cookbook-token-2026", $authHeader)) {
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode([
            "status" => "error",
            "message" => "Unauthorized"
        ]));
        return $response->withStatus(401);
    }

    return $handler->handle($request);
};


$app->get('/', function (Request $request, Response $response) {
    $data = [
        "message" => "Welcome to the Secured Filipino Cookbook API",
        "note" => "This endpoint is public. All /api/* endpoints require a valid Bearer token."
    ];

    $response->getBody()->write(json_encode($data));
    return $response;
});


$app->get('/api/foods', function (Request $request, Response $response) use ($pdo) {

    $stmt = $pdo->query("
        SELECT f.food_id, f.food_name, c.category_name, o.origin_name, f.instructions
        FROM foods f
        JOIN categories c ON f.category_id = c.category_id
        JOIN origins o ON f.origin_id = o.origin_id
    ");

    $foods = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($foods as &$food) {
        $stmt2 = $pdo->prepare("
            SELECT i.ingredient_name
            FROM food_ingredients fi
            JOIN ingredients i ON fi.ingredient_id = i.ingredient_id
            WHERE fi.food_id = ?
        ");
        $stmt2->execute([$food['food_id']]);
        $food['ingredients'] = $stmt2->fetchAll(PDO::FETCH_COLUMN);
    }

    $response->getBody()->write(json_encode($foods));
    return $response;

})->add($authMiddleware);


$app->get('/api/foods/{id}', function (Request $request, Response $response, $args) use ($pdo) {

    $stmt = $pdo->prepare("
        SELECT f.food_id, f.food_name, c.category_name, o.origin_name, f.instructions
        FROM foods f
        JOIN categories c ON f.category_id = c.category_id
        JOIN origins o ON f.origin_id = o.origin_id
        WHERE f.food_id = ?
    ");
    $stmt->execute([$args['id']]);
    $food = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$food) {
        $response->getBody()->write(json_encode([
            "status" => "error",
            "message" => "Food not found"
        ]));
        return $response->withStatus(404);
    }

    $stmt2 = $pdo->prepare("
        SELECT i.ingredient_name
        FROM food_ingredients fi
        JOIN ingredients i ON fi.ingredient_id = i.ingredient_id
        WHERE fi.food_id = ?
    ");
    $stmt2->execute([$args['id']]);
    $food['ingredients'] = $stmt2->fetchAll(PDO::FETCH_COLUMN);

    $response->getBody()->write(json_encode($food));
    return $response;

})->add($authMiddleware);


$app->get('/api/foods/search/{name}', function (Request $request, Response $response, $args) use ($pdo) {

    $stmt = $pdo->prepare("
        SELECT food_id, food_name
        FROM foods
        WHERE food_name LIKE ?
    ");
    $stmt->execute(["%" . $args['name'] . "%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($results));
    return $response;

})->add($authMiddleware);


$app->get('/api/categories', function (Request $request, Response $response) use ($pdo) {

    $stmt = $pdo->query("SELECT * FROM categories");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($data));
    return $response;

})->add($authMiddleware);


$app->get('/api/origins', function (Request $request, Response $response) use ($pdo) {

    $stmt = $pdo->query("SELECT * FROM origins");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($data));
    return $response;

})->add($authMiddleware);


$app->get('/api/ingredients', function (Request $request, Response $response) use ($pdo) {

    $stmt = $pdo->query("SELECT * FROM ingredients");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($data));
    return $response;

})->add($authMiddleware);


$app->post('/api/foods', function (Request $request, Response $response) use ($pdo) {

    $data = $request->getParsedBody();

    if (
        empty($data['food_name']) ||
        empty($data['category_id']) ||
        empty($data['origin_id']) ||
        empty($data['instructions']) ||
        empty($data['ingredient_ids']) ||
        !is_array($data['ingredient_ids'])
    ) {
        $response->getBody()->write(json_encode([
            "status" => "error",
            "message" => "Missing required fields, or ingredient_ids is not an array"
        ]));
        return $response->withStatus(400);
    }

    // Reject non-numeric ingredient IDs (e.g. from bad client-side parsing) before hitting the DB
    foreach ($data['ingredient_ids'] as $ingredient_id) {
        if (!is_numeric($ingredient_id)) {
            $response->getBody()->write(json_encode([
                "status" => "error",
                "message" => "ingredient_ids must contain only numeric IDs"
            ]));
            return $response->withStatus(400);
        }
    }

    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare("
            INSERT INTO foods (food_name, category_id, origin_id, instructions)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['food_name'],
            $data['category_id'],
            $data['origin_id'],
            $data['instructions']
        ]);

        $food_id = $pdo->lastInsertId();

        foreach ($data['ingredient_ids'] as $ingredient_id) {
            $stmt2 = $pdo->prepare("
                INSERT INTO food_ingredients (food_id, ingredient_id)
                VALUES (?, ?)
            ");
            $stmt2->execute([$food_id, $ingredient_id]);
        }

        $pdo->commit();

        $response->getBody()->write(json_encode([
            "status" => "success",
            "message" => "Food added successfully."
        ]));

        return $response->withHeader('Content-Type', 'application/json')
                        ->withStatus(201);

    } catch (Throwable $e) {
        $pdo->rollBack();

        $response->getBody()->write(json_encode([
            "status" => "error",
            "message" => "Failed to add food"
        ]));

        return $response->withStatus(500);
    }

})->add($authMiddleware);


$app->put('/api/foods/{id}', function (Request $request, Response $response, $args) use ($pdo) {

    $data = $request->getParsedBody();

    // Validate required fields (previously missing entirely, allowing silent overwrite with nulls)
    if (
        empty($data['food_name']) ||
        empty($data['category_id']) ||
        empty($data['origin_id']) ||
        empty($data['instructions'])
    ) {
        $response->getBody()->write(json_encode([
            "status" => "error",
            "message" => "Missing required fields"
        ]));
        return $response->withStatus(400);
    }

    if (isset($data['ingredient_ids']) && !is_array($data['ingredient_ids'])) {
        $response->getBody()->write(json_encode([
            "status" => "error",
            "message" => "ingredient_ids must be an array"
        ]));
        return $response->withStatus(400);
    }

    // Confirm the food exists before doing anything else
    $check = $pdo->prepare("SELECT food_id FROM foods WHERE food_id = ?");
    $check->execute([$args['id']]);
    if (!$check->fetch()) {
        $response->getBody()->write(json_encode([
            "status" => "error",
            "message" => "Food not found"
        ]));
        return $response->withStatus(404);
    }

    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare("
            UPDATE foods
            SET food_name = ?, category_id = ?, origin_id = ?, instructions = ?
            WHERE food_id = ?
        ");

        $stmt->execute([
            $data['food_name'],
            $data['category_id'],
            $data['origin_id'],
            $data['instructions'],
            $args['id']
        ]);

        // If ingredient_ids was supplied, replace the food's ingredient list
        if (isset($data['ingredient_ids'])) {
            $del = $pdo->prepare("DELETE FROM food_ingredients WHERE food_id = ?");
            $del->execute([$args['id']]);

            $insert = $pdo->prepare("
                INSERT INTO food_ingredients (food_id, ingredient_id)
                VALUES (?, ?)
            ");
            foreach ($data['ingredient_ids'] as $ingredient_id) {
                if (!is_numeric($ingredient_id)) {
                    throw new \InvalidArgumentException("Invalid ingredient_id");
                }
                $insert->execute([$args['id'], $ingredient_id]);
            }
        }

        $pdo->commit();

        $response->getBody()->write(json_encode([
            "status" => "success",
            "message" => "Food updated successfully."
        ]));

        return $response->withStatus(200);

    } catch (Throwable $e) {
        $pdo->rollBack();

        $response->getBody()->write(json_encode([
            "status" => "error",
            "message" => "Failed to update food"
        ]));

        return $response->withStatus(500);
    }

})->add($authMiddleware);


$app->delete('/api/foods/{id}', function (Request $request, Response $response, $args) use ($pdo) {

    // Confirm the food exists so callers get an honest 404 instead of a silent no-op 200
    $check = $pdo->prepare("SELECT food_id FROM foods WHERE food_id = ?");
    $check->execute([$args['id']]);
    if (!$check->fetch()) {
        $response->getBody()->write(json_encode([
            "status" => "error",
            "message" => "Food not found"
        ]));
        return $response->withStatus(404);
    }

    $pdo->beginTransaction();

    try {
        // Clean up child rows first in case there's no ON DELETE CASCADE on the FK
        $del = $pdo->prepare("DELETE FROM food_ingredients WHERE food_id = ?");
        $del->execute([$args['id']]);

        $stmt = $pdo->prepare("DELETE FROM foods WHERE food_id = ?");
        $stmt->execute([$args['id']]);

        $pdo->commit();

        $response->getBody()->write(json_encode([
            "status" => "success",
            "message" => "Food deleted successfully."
        ]));

        return $response->withStatus(200);

    } catch (Throwable $e) {
        $pdo->rollBack();

        $response->getBody()->write(json_encode([
            "status" => "error",
            "message" => "Failed to delete food"
        ]));

        return $response->withStatus(500);
    }

})->add($authMiddleware);

$app->post('/api/sample', function (Request $request, Response $response) {

    $data = $request->getParsedBody();

    if (empty($data['name']) || empty($data['description'])) {
        $response->getBody()->write(json_encode([
            "status" => "error",
            "message" => "Missing required fields"
        ]));
        return $response->withStatus(400);
    }

    $response->getBody()->write(json_encode([
        "status" => "success",
        "data" => $data
    ]));

    return $response->withHeader('Content-Type', 'application/json');
});


$app->run();