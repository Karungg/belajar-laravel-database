<?php

namespace Tests\Feature;

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
    }

    public function testInsert()
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
        $this->testInsert();

        $collection = DB::table('categories')->select(['id', 'name'])->get();
        self::assertNotNull($collection);

        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testQueryBuilderWhere()
    {
        DB::table('categories')->insert([
            'id' => 'SMARTPHONE',
            'name' => 'Smartphone',
            'created_at' => '2023-09-24 00:00:00'
        ]);
        DB::table('categories')->insert([
            'id' => 'FOOD',
            'name' => 'Food',
            'created_at' => '2023-09-24 00:00:00'
        ]);
        DB::table('categories')->insert([
            'id' => 'LAPTOP',
            'name' => 'Laptop',
            'created_at' => '2023-09-24 00:00:00'
        ]);
        DB::table('categories')->insert([
            'id' => 'FASHION',
            'name' => 'Fashion',
            'created_at' => '2023-09-24 00:00:00'
        ]);

        assertTrue(true);
    }

    public function testWhere()
    {
        $this->testQueryBuilderWhere();

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
        $this->testQueryBuilderWhere();

        $collection = DB::table('categories')->whereBetween('created_at', ['2023-09-24 00:00:00', '2023-09-24 23:59:59'])->get();
        self::assertCount(4, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testWhereIn()
    {
        $this->testQueryBuilderWhere();

        $collection = DB::table('categories')->whereIn('id', ['SMARTPHONE', 'LAPTOP'])->get();
        self::assertCount(2, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testWhereNull()
    {
        $this->testQueryBuilderWhere();

        $collection = DB::table('categories')->whereNull('description')->get();
        self::assertCount(4, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testWhereDate()
    {
        $this->testQueryBuilderWhere();

        $collection = DB::table('categories')->whereDate('created_at', ['2023-09-24'])->get();
        self::assertCount(4, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }
}
