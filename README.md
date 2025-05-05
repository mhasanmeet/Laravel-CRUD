# Laravel megamind CRUD

## Project Requirements

* There will be two types of users:

    1. Admin
    2. Seller

* Sellers can create 'Offers!
* Offers will have following properties:

    1. Title
    2. Price
    3. Categories
    4. Locations
    5. Image (optional)
    6. Description

* Sellers can edit, delete their offers
* Sellers can see the list of their offers
* Admin can see the list of all the offers
* Admin can edit, delete any offers

## Work with Model & Database First

1. Install [Breeze](https://laravel.com/docs/10.x/starter-kits#laravel-breeze) Package for Authentication while Laravel installation.

2. Install [Debugger](https://github.com/barryvdh/laravel-debugbar) for Debug

3. First, create `model`, `migration`, `factory` and `seeder` for **offer**: run artisan command `php artisan make:model Offer -mfs`. By [`-mfs`](https://laravel.com/docs/12.x/eloquent#generating-model-classes) flag, we can get all migration, factory and seeder together.

4. Add required table in Offer migration file,

    ```php

    //database/migrations/2025_05_04_171204_create_offers_table.php
    Schema::create('offers', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->float('price');
        $table->text('description');
        $table->string('status')->default(ApprovalStatus::DRAFT);
        $table->foreignId('author_id')->constrained('users');
        $table->timestamps();
        $table->softDeletes(); //we also add soft delete feature
    });
    ```

5. Now for "Location" and "Category" do same as "Offer": `php artisan make:model Location -mfs` & `php artisan make:model Category -mfs`

6. Now if we see the requirements relationship database connectivity, we found,

* a Offer can have multiple category
* a Offer can have multiple location
* A category can exist in multiple Offer
* A location can exist in multiple Offer

that why, we need to have `many to many` relationship "pivot" migration tables

* `php artisan make:migration create_category_offer_table`
* `php artisan make:migration create_location_offer_table`

7. For `UserRole` and `ApprovalStatus` we create constant variables in `app/Constants` directory

8. Now run `php artisan migrate`. As we just now migrate our tables, we can also check `rollback` commands if it works or not, run command: `php artisan migrate:rollback` or `php artisan migrate:fresh`. We should not run this command when we have tables with data exists.

9. Add `fillable` in Categories, Location, Offer & User **model**

```php

// app/Models/Offer.php
protected $fillable = [
    'title',
    'price',
    'description',
    'author_id',
    'status',
];

// app/Models/Category.php
protected $fillable = [
    'title',
];

// app/Models/Location.php
protected $fillable = [
    'title',
];
```

10. in "database/factories", we use `faker()` function to generate fake data based on `models` for category, location, user & Offer.

```php

// for categories & location
return [
    'title' => fake()->word(),
];

// for user
return [
    'name' => fake()->name(),
    'email' => fake()->unique()->safeEmail(),
    'email_verified_at' => now(),
    // 'role' => fake()->randomElement(['admin', 'user']),
    'role' => UserRole::USER,
    'password' => static::$password ??= Hash::make('password'),
    'remember_token' => Str::random(10),
];

// for Offer
return [
    'title' => fake()->sentence(),
    'description' => fake()->paragraph(),
    'price' => fake()->randomFloat(2, 100, 1000),
    'status' => ApprovalStatus::DRAFT,
    'author_id' => UserFactory::new()->create()->id,
    // 'author_id' => UserRole::factory(),
];
```

11. in "database/seeders", we define how many data should be entry into categories, location, user & Offer, based on factory.

```php

// Category Seeder
Category::factory()->count(10)->create();

// Location Seeder
Location::factory()->count(10)->create();

// Offer Seeder
Offer::factory()->count(10)->create();
```

12. We did not create `UserSeeder`, create it by run command, `php artisan make:seeder UserSeeder` and then add seeder,

```php

User::factory()->create([
    'name' => 'Admin',
    'email' => 'admin@email.com',
    'role' => UserRole::ADMIN,
]);

User::factory()->create([
    'name' => 'Seller',
    'email' => 'seller@email.com',
]);
```

13. Now, in `DatabaseSeeder`, we need to include seeders whose we call here

```php

// database/seeders/DatabaseSeeder.php
$this->call([
    UserSeeder::class,
    CategorySeeder::class,
    LocationSeeder::class,
    OfferSeeder::class,
]);
```

14. Later we add `many to many` relationship between the `Offer` model and two other models: `Category` and `Location`

```php

// app/Models/Offer.php
public function categories(): BelongsToMany
{
    return $this->belongsToMany(Category::class);
}

public function locations(): BelongsToMany
{
    return $this->belongsToMany(Location::class);
}
```

15. Update `Offer` seeder as,

```php

// database/seeders/OfferSeeder.php
$offers = Offer::factory()->count(5)->create();

foreach ($offers as $offer) {
    $categories = Category::inRandomOrder()->limit(5)->get();
    $offer->categories()->sync($categories->pluck('id'));
}

foreach ($offers as $offer) {
    $locations = Location::inRandomOrder()->limit(5)->get();
    $offer->locations()->sync($locations->pluck('id'));
}
```

16. Finally we run artisan seed command, `php artisan db:seed`, and we get all our fake data loaded into database (check it).

## Work with Views and Blade Template

## Resources

[Tutorial Video](https://www.youtube.com/playlist?list=PL3H43eIOtaDOW29Z6S-7AnZYwUqOqHCxZ)
