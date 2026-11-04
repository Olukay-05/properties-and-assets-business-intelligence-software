<?php

use App\Data\SampleData;
use Inertia\Testing\AssertableInertia as Assert;

/*
|--------------------------------------------------------------------------
| Scaffold smoke tests
|--------------------------------------------------------------------------
|
| These prove the greenfield base is wired correctly before any feature code
| exists: the application boots and serves an Inertia page, and the Spatie Data
| contract layer behaves as the UI layer expects.
|
*/

test('the welcome page renders as an inertia response', function () {
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('welcome'));
});

test('a data object creates from an array and exposes typed properties', function () {
    $data = SampleData::from([
        'name' => 'Boundary Road Estate',
        'count' => 12,
    ]);

    expect($data)
        ->toBeInstanceOf(SampleData::class)
        ->name->toBe('Boundary Road Estate')
        ->count->toBe(12)
        ->description->toBeNull();
});

test('a data object serialises to the shape the generated typescript expects', function () {
    $data = new SampleData(
        name: 'Boundary Road Estate',
        count: 12,
        description: 'Pilot client',
    );

    expect($data->toArray())->toBe([
        'name' => 'Boundary Road Estate',
        'count' => 12,
        'description' => 'Pilot client',
    ]);
});
