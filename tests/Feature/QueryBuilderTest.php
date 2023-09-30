<?php

namespace Tests\Feature;

use Database\Seeders\CategorySeeder;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

use function PHPUnit\Framework\assertTrue;

class QueryBuilderTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        DB::delete('DELETE FROM categories');
        DB::delete('DELETE FROM products');
    }

    public function testSelectInsert()
    {
        DB::table('categories')->insert([
            'id' => 'GADGET',
            'name' => 'Gadget'
        ]);
        DB::table('categories')->insert([
            'id' => 'FOOD',
            'name' => 'Food'
        ]);

        $results = DB::select('SELECT COUNT(id) as total FROM categories');
        self::assertEquals(2, $results[0]->total);
    }

    public function testSelect()
    {
        $this->testSelectInsert();

        $collection = DB::table('categories')->select(['id', 'name'])->get();
        self::assertNotNull($collection);

        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function insertCategories()
    {
        $this->seed(CategorySeeder::class);
    }

    public function testWhere()
    {
        $this->insertCategories();

        $collection = DB::table('categories')->orWhere(function (Builder $builder) {
            $builder->where('id', '=', 'SMARTPHONE');
            $builder->orWhere('id', '=', 'LAPTOP');
        })->get();

        self::assertCount(2, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testWhereBetween()
    {
        $this->insertCategories();

        $collection = DB::table('categories')->whereBetween('created_at', ['2023-09-24 00:00:00', '2023-09-24 23:59:59'])->get();
        self::assertCount(4, $collection);

        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testWhereIn()
    {
        $this->insertCategories();

        $collection = DB::table('categories')->whereIn('id', ['SMARTPHONE', 'LAPTOP'])->get();

        self::assertCount(2, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testWhereNull()
    {
        $this->insertCategories();

        $collection = DB::table('categories')->whereNull('description')->get();

        self::assertCount(4, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testWhereDate()
    {
        $this->insertCategories();

        $collection = DB::table('categories')->whereDate('created_at', ['2023-09-24'])->get();

        self::assertCount(4, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testQueryBuilderUpdate()
    {
        $this->insertCategories();

        DB::table('categories')->where('name', '=', 'Smartphone')->update([
            'name' => 'Handphone'
        ]);

        $collection = DB::table('categories')->where('name', '=', 'Handphone')->get();
        self::assertCount(1, $collection);
    }

    public function testQueryBuilderUpdateOrInsert()
    {
        DB::table('categories')->updateOrInsert(
            [
                'id' => 'VOUCHER'
            ],
            [
                'name' => 'Voucher',
                'description' => 'Ticket and Voucher',
                'created_at' => '2023-09-24 00:00:00'
            ]
        );

        $collection = DB::table('categories')->where('id', '=', 'VOUCHER')->get();
        self::assertCount(1, $collection);
    }

    public function testQueryBuilderIncrement()
    {
        DB::table('counters')->where('id', '=', 'sample')->increment('counter', 1);
        $collection = DB::table('counters')->where('id', '=', 'sample')->get();

        self::assertCount(0, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testQueryBuilderDelete()
    {
        $this->insertCategories();

        DB::table('categories')->where('id', '=', 'SMARTPHONE')->delete();

        $collection = DB::table('categories')->where('id', '=', 'SMARTPHONE')->get();
        self::assertCount(0, $collection);
    }

    public function insertProducts()
    {
        $this->insertCategories();

        DB::table('products')->insert([
            "id" => "1",
            "name" => "Iphone 15 Pro Max",
            "category_id" => "SMARTPHONE",
            "price" => 10000000
        ]);
        DB::table('products')->insert([
            "id" => "2",
            "name" => "Samsung Galaxy S23 Ultra",
            "category_id" => "SMARTPHONE",
            "price" => 20000000
        ]);
    }

    public function insertProductFood()
    {
        DB::table('products')->insert([
            "id" => "3",
            "name" => "Baso",
            "category_id" => "FOOD",
            "price" => 20000
        ]);
        DB::table('products')->insert([
            "id" => "4",
            "name" => "Mie Ayam",
            "category_id" => "FOOD",
            "price" => 20000
        ]);
    }

    public function testQueryBuilderJoin()
    {
        $this->insertProducts();

        $collection = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('products.id', 'products.name', 'products.price', 'categories.name as category_name')
            ->get();

        self::assertCount(2, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testQueryBuilderOrderBy()
    {
        $this->insertProducts();

        $collection = DB::table('products')
            ->orderBy('price', 'desc')
            ->orderBy('name', 'asc')
            ->get();

        self::assertCount(2, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function insertManyCategories()
    {
        for ($i = 0; $i < 100; $i++) {
            DB::table('categories')->insert([
                "id" => "Category-$i",
                "name" => "Category $i"
            ]);
        }
    }

    public function testQueryBuilderPaging()
    {
        $this->insertProducts();

        $collection = DB::table('categories')
            ->skip(2)
            ->take(2)
            ->get();

        self::assertCount(2, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testQueryBuilderChunk()
    {
        $this->insertProducts();

        DB::table('categories')
            ->orderBy('id')
            ->chunk(1, function ($categories) {
                self::assertNotNull($categories);
                $categories->each(function ($item) {
                    Log::info(json_encode($item));
                });
            });
    }

    public function testQueryBuilderLazy()
    {
        $this->insertManyCategories();

        $collection = DB::table('categories')->orderBy('id')->lazy(10)->take(3);
        self::assertNotNull($collection);

        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testCursor()
    {
        $this->insertManyCategories();

        $collection = DB::table('categories')
            ->orderBy('id')
            ->cursor()
            ->each(function ($category) {
                self::assertNotNull($category);
                Log::info(json_encode($category));
            });
    }

    public function testQueryBuilderAggregate()
    {
        $this->insertProducts();

        $collection = DB::table('products')->count('id');
        self::assertEquals(2, $collection);

        $collection = DB::table('products')->max('price');
        self::assertEquals(20000000, $collection);

        $collection = DB::table('products')->min('price');
        self::assertEquals(10000000, $collection);

        $collection = DB::table('products')->avg('price');
        self::assertEquals(15000000.0000, $collection);

        $collection = DB::table('products')->sum('price');
        self::assertEquals(30000000, $collection);
    }

    public function testQueryBuilderRawAggregate()
    {
        $this->insertProducts();

        $collection = DB::table('products')
            ->select(
                DB::raw('count(*) as total_products'),
                DB::raw('min(price) as min_price'),
                DB::raw('max(price) as max_price')
            )
            ->get();

        self::assertEquals(2, $collection[0]->total_products);
        self::assertEquals(10000000, $collection[0]->min_price);
        self::assertEquals(20000000, $collection[0]->max_price);
    }

    public function testQueryBuilderGrouping()
    {
        $this->insertProducts();
        $this->insertProductFood();

        $collection = DB::table('products')
            ->select('category_id', DB::raw('count(*) as total_products'))
            ->groupBy('category_id')
            ->orderBy('category_id')
            ->get();

        self::assertCount(2, $collection);
        self::assertEquals('FOOD', $collection[0]->category_id);
        self::assertEquals('SMARTPHONE', $collection[1]->category_id);
        self::assertEquals(2, $collection[0]->total_products);
        self::assertEquals(2, $collection[1]->total_products);
    }

    public function testQueryBuilderHaving()
    {
        $this->insertProducts();
        $this->insertProductFood();

        $collection = DB::table('products')
            ->select('category_id', DB::raw('count(*) as total_products'))
            ->groupBy('category_id')
            ->orderBy('category_id', 'desc')
            ->having(DB::raw('count(*)'), '>', 2)
            ->get();

        self::assertCount(0, $collection);
    }

    public function testQueryBuilderLocking()
    {
        $this->insertProducts();

        DB::transaction(function () {
            $collection = DB::table('products')
                ->where('id', '=', '1')
                ->lockForUpdate()
                ->get();

            self::assertCount(1, $collection);
        });
    }

    public function testPagination()
    {
        $this->insertProducts();
        $this->insertProductFood();

        $paginate = DB::table('products')->paginate(2);

        self::assertEquals(1, $paginate->currentPage());
        self::assertEquals(2, $paginate->perPage());
        self::assertEquals(2, $paginate->lastPage());
        self::assertEquals(4, $paginate->total());

        $collection = $paginate->items();
        self::assertCount(2, $collection);
        foreach ($collection as $item) {
            Log::info(json_encode($item));
        }
    }

    public function testIterateAllPagination()
    {
        $this->insertProducts();
        $this->insertProductFood();

        $page = 1;
        while (true) {
            $paginate = DB::table('products')->paginate(2, page: $page);
            if ($paginate->isEmpty()) {
                break;
            } else {
                $page++;
                foreach ($paginate->items() as $item) {
                    self::assertNotNull($item);
                    Log::info(json_encode($item));
                }
            }
        }
    }

    public function testQueryBuilderCursorPagination()
    {
        $this->insertProducts();
        $this->insertProductFood();

        $cursor = "id";
        while (true) {
            $paginate = DB::table('products')->orderBy('id')->cursorPaginate(2, cursor: $cursor);

            foreach ($paginate->items() as $item) {
                self::assertNotNull($item);
                Log::info(json_encode($item));
            }

            $cursor = $paginate->nextCursor();
            if ($cursor == null) {
                break;
            }
        }
    }

    public function testSeeding()
    {
        $this->seed(CategorySeeder::class);

        $collection = DB::table('categories')->get();
        self::assertCount(4, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }
}
