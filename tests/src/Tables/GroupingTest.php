<?php

use Filament\Tables;
use Filament\Tests\Models\Post;
use Filament\Tests\Tables\Fixtures\PostsTable;
use Filament\Tests\Tables\TestCase;
use Livewire\Features\SupportTesting\Testable;

use function Filament\Tests\livewire;

uses(TestCase::class);

it('can evaluate table groups', function () {
    $posts = Post::factory()->count(20)->create();

    livewire(PostsTable::class)
        ->tap(function (Testable $testable) {
            /** @var PostsTable $livewire */
            $livewire = $testable->instance();

            /** @var Tables\Table $table */
            $table = invade($livewire)->table;

            expect($table)
                ->getGrouping()->toBeNull();

            $groups = $table->getGroups();

            expect($groups['author.name'])
                ->getLabel()->toBe('Dynamic label');
        })
        ->set('tableGrouping', 'author.name')
        ->tap(function (Testable $testable) {
            /** @var PostsTable $livewire */
            $livewire = $testable->instance();

            /** @var Tables\Table $table */
            $table = invade($livewire)->table;

            expect($table)
                ->getGrouping()->toBeInstanceOf(Tables\Grouping\Group::class)
                ->and($table->getGrouping())->getLabel()->toBe('Dynamic label');
        });
});
