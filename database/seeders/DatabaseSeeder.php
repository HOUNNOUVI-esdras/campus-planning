<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Enums\Weekday;
use App\Models\Course;
use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->seedUser('Admin Campus', 'admin@univ.test', Role::Admin);
        $awa = $this->seedUser('Awa Sossou', 'awa.sossou@univ.test', Role::Professor);
        $koffi = $this->seedUser('Koffi Dossou', 'koffi.dossou@univ.test', Role::Professor);
        $alice = $this->seedUser('Alice Houngbo', 'alice.houngbo@univ.test', Role::Student);
        $bob = $this->seedUser('Bob Adjovi', 'bob.adjovi@univ.test', Role::Student);
        $chloe = $this->seedUser('Chloé Kpadonou', 'chloe.kpadonou@univ.test', Role::Student);
        $david = $this->seedUser('David Tossou', 'david.tossou@univ.test', Role::Student);

        $extraStudents = collect([
            ['Emmanuel Ahouansou-Gbèdji', 'emmanuel.ahouansou@univ.test'],
            ['Fatou Zinsou', 'fatou.zinsou@univ.test'],
            ['Grâce Agossou', 'grace.agossou@univ.test'],
            ['Hervé Dossa', 'herve.dossa@univ.test'],
            ['Inès Hounkpè', 'ines.hounkpe@univ.test'],
            ['Jean-Baptiste Akpovi', 'jean-baptiste.akpovi@univ.test'],
        ])->map(fn (array $student): User => $this->seedUser($student[0], $student[1], Role::Student));

        [$emmanuel, $fatou, $grace, $herve, $ines] = $extraStudents->all();

        $promotion = $this->seedGroup('L3 Informatique', [$alice, $bob, $chloe, $david, $fatou, $grace, $herve, $ines]);
        $firstTutorial = $this->seedGroup('L3 Informatique · TD 1', [$alice, $chloe, $fatou, $grace]);
        $secondTutorial = $this->seedGroup('L3 Informatique · TD 2', [$bob, $david, $herve, $ines]);
        $this->seedGroup('M1 Génie logiciel · Alternance (rythme 2 semaines école / 2 semaines entreprise)', [$emmanuel]);
        $this->seedGroup('L2 Informatique', []);

        $this->seedCourse($awa, 'Algorithmique avancée', 'Amphi B', Weekday::Monday, '08:00', '10:00', $promotion);
        $this->seedCourse($awa, 'Bases de données', 'Salle 12', Weekday::Tuesday, '10:00', '12:00', $firstTutorial);
        $this->seedCourse($koffi, 'Réseaux informatiques', 'Salle 07', Weekday::Wednesday, '14:00', '16:00', $secondTutorial);
        $this->seedCourse($awa, 'Génie logiciel', 'Amphi A', Weekday::Thursday, '08:00', '10:00', $promotion);
        $this->seedCourse($koffi, 'Architecture logicielle', 'Salle 21', Weekday::Friday, '10:00', '12:00', $promotion);
        $this->seedCourse($awa, 'Mathématiques discrètes', 'Salle 05', Weekday::Saturday, '08:00', '10:00', $secondTutorial);
    }

    /**
     * Seed a verified demo user.
     */
    private function seedUser(string $name, string $email, Role $role): User
    {
        return User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => 'password',
                'role' => $role,
                'email_verified_at' => now(),
            ],
        );
    }

    /**
     * Seed a group and its students.
     *
     * @param  array<int, User>  $students
     */
    private function seedGroup(string $name, array $students): Group
    {
        $group = Group::query()->firstOrCreate(['name' => $name]);

        $group->students()->sync(
            collect($students)->map(fn (User $student): int => $student->id)->all(),
        );

        return $group;
    }

    /**
     * Seed a course followed by a group.
     */
    private function seedCourse(
        User $professor,
        string $title,
        string $room,
        Weekday $weekday,
        string $startsAt,
        string $endsAt,
        Group $group,
    ): void {
        Course::query()->updateOrCreate(
            [
                'professor_id' => $professor->id,
                'title' => $title,
            ],
            [
                'group_id' => $group->id,
                'room' => $room,
                'day_of_week' => $weekday,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
            ],
        );
    }
}
