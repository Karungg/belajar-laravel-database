<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RawQueryTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        DB::delete("DELETE FROM categories");
    }

    public function testExample(): void
    {
        DB::insert("INSERT INTO categories(id, name, description, created_at) VALUES (?, ?, ?, ?)", [
            'GADGET', 'Gadget', 'Gadget Category', '2023-09-24 00:00:00'
        ]);

        $result = DB::select("SELECT * FROM categories WHERE id = ?", ['GADGET']);

        self::assertEquals(1, count($result));
        self::assertEquals('GADGET', $result[0]->id);
        self::assertEquals("Gadget", $result[0]->name);
        self::assertEquals("Gadget Category", $result[0]->description);
        self::assertEquals("2023-09-24 00:00:00", $result[0]->created_at);
    }

    public function testNamedBinding(): void
    {
        DB::insert("INSERT INTO categories(id, name, description, created_at) VALUES (:id, :name, :description, :created_at)", [
            "id" => 'GADGET',
            "name" => 'Gadget',
            "description" => 'Gadget Category',
            "created_at" => '2023-09-24 00:00:00'
        ]);

        $result = DB::select("SELECT * FROM categories WHERE id = :id", ["id" => 'GADGET']);

        self::assertEquals(1, count($result));
        self::assertEquals('GADGET', $result[0]->id);
        self::assertEquals("Gadget", $result[0]->name);
        self::assertEquals("Gadget Category", $result[0]->description);
        self::assertEquals("2023-09-24 00:00:00", $result[0]->created_at);
    }
}
