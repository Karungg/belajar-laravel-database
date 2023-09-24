<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

use function PHPUnit\Framework\assertEquals;

class TransactionTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        DB::delete("DELETE FROM categories");
    }

    public function testTransactionSuccess()
    {
        DB::transaction(function () {
            DB::insert("INSERT INTO categories(id, name, description, created_at) VALUES (?, ?, ?, ?)", [
                "GADGET", "Gadget", "Gadget Category", "2020-10-10 10:10:10"
            ]);
            DB::insert("INSERT INTO categories(id, name, description, created_at) VALUES (?, ?, ?, ?)", [
                "FOOD", "Food", "Food Category", "2020-10-10 10:10:10"
            ]);
        });

        $results = DB::select("SELECT * FROM categories");
        assertEquals(2, count($results));
    }

    public function testTransactionFailed()
    {
        try {
            DB::transaction(function () {
                DB::insert("INSERT INTO categories(id, name, description, created_at) VALUES (?, ?, ?, ?)", [
                    "GADGET", "Gadget", "Gadget Category", "2020-10-10 10:10:10"
                ]);
                DB::insert("INSERT INTO categories(id, name, description, created_at) VALUES (?, ?, ?, ?)", [
                    "GADGET", "Food", "Food Category", "2020-10-10 10:10:10"
                ]);
            });
        } catch (QueryException $error) {
            //throw $th;
        }


        $results = DB::select("SELECT * FROM categories");
        assertEquals(0, count($results));
    }

    public function testManualTransactionSuccess()
    {
        try {
            DB::beginTransaction();
            DB::insert("INSERT INTO categories(id, name, description, created_at) VALUES (?, ?, ?, ?)", [
                "GADGET", "Gadget", "Gadget Category", "2020-10-10 10:10:10"
            ]);
            DB::insert("INSERT INTO categories(id, name, description, created_at) VALUES (?, ?, ?, ?)", [
                "FOOD", "Food", "Food Category", "2020-10-10 10:10:10"
            ]);
            DB::commit();
        } catch (QueryException $error) {
            DB::rollBack();
        }

        $results = DB::select("SELECT * FROM categories");
        self::assertCount(2, $results);
    }

    public function testManualTransactionFailed()
    {
        try {
            DB::beginTransaction();
            DB::insert("INSERT INTO categories(id, name, description, created_at) VALUES (?, ?, ?, ?)", [
                "GADGET", "Gadget", "Gadget Category", "2020-10-10 10:10:10"
            ]);
            DB::insert("INSERT INTO categories(id, name, description, created_at) VALUES (?, ?, ?, ?)", [
                "GADGET", "Food", "Food Category", "2020-10-10 10:10:10"
            ]);
            DB::commit();
        } catch (QueryException $error) {
            DB::rollBack();
        }

        $results = DB::select("SELECT * FROM categories");
        self::assertCount(0, $results);
    }
}
