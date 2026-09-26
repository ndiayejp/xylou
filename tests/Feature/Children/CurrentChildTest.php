<?php

declare(strict_types=1);

use App\Domain\Children\Actions\CreateChildProfile;
use App\Domain\Children\Enums\Grade;
use App\Domain\Children\Models\ChildProfile;
use App\Domain\Identity\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('l’espace parent reçoit ses enfants par ordre alphabétique, le premier est courant', function (): void {
    $parent = User::factory()->parent()->create();
    $lucas = ChildProfile::factory()->for($parent, 'owner')->create(['first_name' => 'Lucas', 'grade' => Grade::Sixieme, 'birth_year' => 2015]);
    $emma = ChildProfile::factory()->for($parent, 'owner')->create(['first_name' => 'Emma', 'grade' => Grade::Ce2, 'birth_year' => 2018]);
    ChildProfile::factory()->create(['first_name' => 'Enfant d’un autre']);

    $this->actingAs($parent)
        ->get(route('parent.dashboard'))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('parent.children', [
                ['id' => $emma->id, 'firstName' => 'Emma', 'grade' => 'ce2', 'birthYear' => 2018],
                ['id' => $lucas->id, 'firstName' => 'Lucas', 'grade' => '6e', 'birthYear' => 2015],
            ])
            ->where('parent.currentChildId', $emma->id));
});

test('le parent change d’enfant courant', function (): void {
    $parent = User::factory()->parent()->create();
    ChildProfile::factory()->for($parent, 'owner')->create(['first_name' => 'Emma']);
    $lucas = ChildProfile::factory()->for($parent, 'owner')->create(['first_name' => 'Lucas']);

    $this->actingAs($parent)
        ->from(route('parent.dashboard'))
        ->post(route('parent.current-child'), ['child_id' => $lucas->id])
        ->assertRedirect(route('parent.dashboard'));

    $this->get(route('parent.dashboard'))
        ->assertInertia(fn (Assert $page): Assert => $page->where('parent.currentChildId', $lucas->id));
});

test('un parent ne peut pas choisir l’enfant d’un autre', function (): void {
    $other = ChildProfile::factory()->create();

    $this->actingAs(User::factory()->parent()->create())
        ->post(route('parent.current-child'), ['child_id' => $other->id])
        ->assertForbidden();
});

test('un enfant inexistant ou supprimé donne 404', function (): void {
    $parent = User::factory()->parent()->create();
    $deleted = ChildProfile::factory()->for($parent, 'owner')->create();
    $deleted->delete();

    $this->actingAs($parent)
        ->post(route('parent.current-child'), ['child_id' => $deleted->id])
        ->assertNotFound();
});

test('le choix d’enfant est réservé aux parents', function (): void {
    $child = ChildProfile::factory()->create();

    $this->actingAs(User::factory()->professional()->withTwoFactor()->create())
        ->post(route('parent.current-child'), ['child_id' => $child->id])
        ->assertForbidden();

    $this->post('/logout');
    $this->post(route('parent.current-child'), ['child_id' => $child->id])
        ->assertRedirect(route('login'));
});

test('un pro ne reçoit aucune donnée d’enfant partagée', function (): void {
    ChildProfile::factory()->create();

    $this->actingAs(User::factory()->professional()->withTwoFactor()->create())
        ->get(route('pro.dashboard'))
        ->assertInertia(fn (Assert $page): Assert => $page->where('parent', null));
});

test('CreateChildProfile rattache l’enfant à son parent', function (): void {
    $parent = User::factory()->parent()->create();

    $child = resolve(CreateChildProfile::class)($parent, 'Inès', Grade::Cm1, 2016);

    expect($child->fresh())
        ->first_name->toBe('Inès')
        ->grade->toBe(Grade::Cm1)
        ->birth_year->toBe(2016)
        ->owner_id->toBe($parent->id);
});
