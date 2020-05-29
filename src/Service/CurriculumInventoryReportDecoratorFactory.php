<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\DTO\CurriculumInventoryReportDTO;
use App\Entity\CurriculumInventoryReportInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RouterInterface;

class CurriculumInventoryReportDecoratorFactory
{

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param $report
     * @return CurriculumInventoryReportDTO
     * @throws \Exception
     */
    public function create(
        $report
    ) {
        if ($report instanceof CurriculumInventoryReportInterface) {
            $dto = CurriculumInventoryReportDTO::createFromEntity($report);
            return $this->decorateDto($dto);
        }

        if ($report instanceof CurriculumInventoryReportDTO) {
            return $this->decorateDto($report);
        }

        throw new \Exception(get_class($report) . " cannot be decorated");
    }

    /**
     * @param CurriculumInventoryReportDTO $reportDTO
     *
     * @return CurriculumInventoryReportDTO
     */
    protected function decorateDto(CurriculumInventoryReportDTO $reportDTO)
    {
        $reportDTO->absoluteFileUri = $this->router->generate(
            'ilios_downloadcurriculuminventoryreport',
            ['token' => $reportDTO->token],
            UrlGenerator::ABSOLUTE_URL
        );

        return $reportDTO;
    }
}
