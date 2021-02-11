<?php

declare(strict_types=1);

namespace App\Classes;

use App\Annotation as IS;

/**
 * Class AcademicYear
 *
 * @IS\DTO("academicYears")
 */

class AcademicYear
{
    /**
     * @var int
     *
     * @IS\Id
     * @IS\Expose
     * @IS\Type("string")
     */
    public int $id;

    /**
     * @var string
     *
     * @IS\Expose
     * @IS\Type("string")
     */
    public string $title;

    public function __construct(int $year, string $title)
    {
        $this->id = $year;
        $this->title = $title;
    }
}
