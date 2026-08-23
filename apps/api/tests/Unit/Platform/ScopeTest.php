<?php

declare(strict_types=1);

namespace Tests\Unit\Platform;

use App\Platform\Support\Scope;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ScopeTest extends TestCase
{
    #[Test]
    public function a_narrow_scope_requires_a_target(): void
    {
        // Without this, `new Scope('department')` would be an unbounded grant wearing the label
        // of a narrow one — the most dangerous possible failure in this class.
        $this->expectException(InvalidArgumentException::class);

        new Scope(Scope::DEPARTMENT);
    }

    #[Test]
    public function institution_and_self_scopes_need_no_target(): void
    {
        $this->assertTrue(Scope::institution()->isInstitutionWide());
        $this->assertTrue(Scope::self()->isSelf());
    }

    #[Test]
    public function unknown_scope_types_are_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Scope('galaxy', 'some-id');
    }

    #[Test]
    public function institution_scope_contains_every_narrower_scope(): void
    {
        $institution = Scope::institution();

        $this->assertTrue($institution->contains(Scope::campus('c1')));
        $this->assertTrue($institution->contains(Scope::faculty('f1')));
        $this->assertTrue($institution->contains(Scope::department('d1')));
    }

    #[Test]
    public function institution_scope_does_not_contain_self_scope(): void
    {
        // SELF sits outside the hierarchy deliberately. "Everything in the institution" must not
        // silently mean "and also anything scoped to an individual".
        $this->assertFalse(Scope::institution()->contains(Scope::self()));
    }

    #[Test]
    public function a_faculty_scope_does_not_contain_another_faculty(): void
    {
        $this->assertFalse(Scope::faculty('science')->contains(Scope::faculty('nursing')));
    }

    #[Test]
    public function a_department_scope_does_not_contain_its_own_faculty(): void
    {
        // Containment runs downward only. A Head of Department must never acquire the Dean's
        // reach by virtue of sitting inside the faculty.
        $this->assertFalse(Scope::department('cs')->contains(Scope::faculty('science')));
    }

    #[Test]
    public function a_faculty_scope_contains_departments_by_type(): void
    {
        $this->assertTrue(Scope::faculty('science')->contains(Scope::department('cs')));
    }

    #[Test]
    public function self_scope_contains_nothing_but_itself(): void
    {
        $self = Scope::self();

        $this->assertTrue($self->contains(Scope::self()));
        $this->assertFalse($self->contains(Scope::department('cs')));
        $this->assertFalse($self->contains(Scope::institution()));
    }

    #[Test]
    public function scopes_render_predictably(): void
    {
        $this->assertSame('institution', (string) Scope::institution());
        $this->assertSame('self', (string) Scope::self());
        $this->assertSame('department:cs', (string) Scope::department('cs'));
    }
}
