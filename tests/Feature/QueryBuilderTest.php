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

    public function testQueryBuilderWhereInsert()
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
        $this->testQueryBuilderWhereInsert();

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
        $this->testQueryBuilderWhereInsert();

        $collection = DB::table('categories')->whereBetween('created_at', ['2023-09-24 00:00:00', '2023-09-24 23:59:59'])->get();
        self::assertCount(4, $collection);

        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testWhereIn()
    {
        $this->testQueryBuilderWhereInsert();

        $collection = DB::table('categories')->whereIn('id', ['SMARTPHONE', 'LAPTOP'])->get();

        self::assertCount(2, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testWhereNull()
    {
        $this->testQueryBuilderWhereInsert();

        $collection = DB::table('categories')->whereNull('description')->get();

        self::assertCount(4, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testWhereDate()
    {
        $this->testQueryBuilderWhereInsert();

        $collection = DB::table('categories')->whereDate('created_at', ['2023-09-24'])->get();

        self::assertCount(4, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testQueryBuilderUpdate()
    {
        $this->testQueryBuilderWhereInsert();

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

        self::assertCount(1, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }
}
