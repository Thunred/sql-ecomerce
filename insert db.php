<?php
require 'vendor/autoload.php';

use Faker\Factory;

$faker = Factory::create();
$dbFile = 'e-comerce.db';

try {
    $pdo = new PDO("sqlite:$dbFile");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Insert fake users
    $users = [];
    for ($i = 0; $i < 10; $i++) {
        $users[] = [
            'name' => $faker->firstName,
            'surname' => $faker->lastName,
            'mail' => $faker->unique()->safeEmail,
            'password' => password_hash($faker->password, PASSWORD_DEFAULT),
            'cell_number' => $faker->unique()->phoneNumber,
            'photo_type_id' => rand(1, 10) // Assuming photo_type_id will be generated in advance
        ];
    }

    $stmt = $pdo->prepare("INSERT INTO user (name, surname, mail, password, cell_number, photo_type_id) VALUES (:name, :surname, :mail, :password, :cell_number, :photo_type_id)");
    foreach ($users as $user) {
        $stmt->execute($user);
    }

    // Insert fake addresses
    $addresses = [];
    foreach ($users as $key => $user) {
        $addresses[] = [
            'street' => $faker->streetAddress,
            'city' => $faker->city,
            'country' => $faker->country,
            'user_id' => $key + 1
        ];
    }

    $stmt = $pdo->prepare("INSERT INTO adress (street, city, country, user_id) VALUES (:street, :city, :country, :user_id)");
    foreach ($addresses as $address) {
        $stmt->execute($address);
    }

    // Insert fake products
    $products = [];
    for ($i = 0; $i < 20; $i++) {
        $products[] = [
            'name' => $faker->word,
            'category' => $faker->word,
            'description' => $faker->paragraph,
            'price' => $faker->randomFloat(2, 1, 100),
            'stock' => $faker->numberBetween(1, 50)
        ];
    }

    $stmt = $pdo->prepare("INSERT INTO product (name, category, description, price, stock) VALUES (:name, :category, :description, :price, :stock)");
    foreach ($products as $product) {
        $stmt->execute($product);
    }

    // Insert fake carts
    $carts = [];
    foreach ($users as $key => $user) {
        $carts[] = [
            'user_id' => $key + 1,
            'status' => $faker->randomElement(['active', 'completed', 'cancelled'])
        ];
    }

    $stmt = $pdo->prepare("INSERT INTO panier_full (user_id, status) VALUES (:user_id, :status)");
    foreach ($carts as $cart) {
        $stmt->execute($cart);
    }

    // Insert fake cart products
    foreach ($carts as $cartKey => $cart) {
        $numProductsInCart = rand(1, 5);
        $productIds = array_rand(range(1, count($products)), $numProductsInCart);

        foreach ($productIds as $productId) {
            $stmt = $pdo->prepare("INSERT INTO panier_product (cart_id, product_id, quantity) VALUES (:cart_id, :product_id, :quantity)");
            $stmt->execute([
                'cart_id' => $cartKey + 1,
                'product_id' => $productId + 1,
                'quantity' => rand(1, 3)
            ]);
        }
    }

    // Insert fake commands
    $commands = [];
    foreach ($carts as $cartKey => $cart) {
        $commands[] = [
            'command_date' => $faker->dateTimeThisYear->format('Y-m-d H:i:s'),
            'delivered' => $faker->boolean,
            'user_id' => $cart['user_id'],
            'cart_id' => $cartKey + 1,
            'adress_id' => $cart['user_id']
        ];
    }

    $stmt = $pdo->prepare("INSERT INTO command (command_date, delivered, user_id, cart_id, adress_id) VALUES (:command_date, :delivered, :user_id, :cart_id, :adress_id)");
    foreach ($commands as $command) {
        $stmt->execute($command);
    }

    // Insert fake invoices
    $invoices = [];
    foreach ($commands as $key => $command) {
        $invoices[] = [
            'delivered_date' => $faker->dateTimeThisYear->format('Y-m-d H:i:s'),
            'command_id' => $key + 1,
            'user_id' => $command['user_id'],
            'adress_id' => $command['adress_id']
        ];
    }

    $stmt = $pdo->prepare("INSERT INTO invoices (delivered_date, command_id, user_id, adress_id) VALUES (:delivered_date, :command_id, :user_id, :adress_id)");
    foreach ($invoices as $invoice) {
        $stmt->execute($invoice);
    }

    // Insert fake photos
    $photos = [];
    for ($i = 0; $i < 10; $i++) {
        $photos[] = [
            'photo_link' => $faker->imageUrl(640, 480)
        ];
    }

    $stmt = $pdo->prepare("INSERT INTO photo (photo_link) VALUES (:photo_link)");
    foreach ($photos as $photo) {
        $stmt->execute($photo);
    }

    // Insert fake photo types
    $photoTypes = [];
    for ($i = 0; $i < 10; $i++) {
        $photoTypes[] = [
            'photo_type' => $faker->word,
            'photo_id' => $i + 1
        ];
    }

    $stmt = $pdo->prepare("INSERT INTO photo_type (photo_type, photo_id) VALUES (:photo_type, :photo_id)");
    foreach ($photoTypes as $photoType) {
        $stmt->execute($photoType);
    }

    echo "Database populated with fake data successfully!";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
