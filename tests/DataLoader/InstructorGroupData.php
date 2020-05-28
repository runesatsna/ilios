<?php

declare(strict_types=1);

namespace App\Tests\DataLoader;

class InstructorGroupData extends AbstractDataLoader
{
    protected function getData()
    {
        $arr = [];

        $arr[] = [
            'id' => 1,
            'title' => $this->faker->text(10),
            'school' => '1',
            'learnerGroups' => ['1'],
            'ilmSessions' => ['1'],
            'users' => ['2'],
            'offerings' => ['1']
        ];

        $arr[] = [
            'id' => 2,
            'title' => 'second instructor group',
            'school' => '1',
            'learnerGroups' => [],
            'ilmSessions' => [],
            'users' => ['2', '4'],
            'offerings' => ['3']
        ];

        $arr[] = [
            'id' => 3,
            'title' => $this->faker->text(10),
            'school' => '1',
            'learnerGroups' => [],
            'ilmSessions' => ['2'],
            'users' => ['2'],
            'offerings' => []
        ];

        $arr[] = [
            'id' => 4,
            'title' => $this->faker->text(10),
            'school' => '2',
            'learnerGroups' => [],
            'ilmSessions' => [],
            'users' => [],
            'offerings' => []
        ];


        return $arr;
    }

    public function create()
    {
        return [
            'id' => 5,
            'title' => $this->faker->text(10),
            'school' => '1',
            'learnerGroups' => ['1'],
            'ilmSessions' => ['1'],
            'users' => [],
            'offerings' => []
        ];
    }

    public function createInvalid()
    {
        return [];
    }
}
