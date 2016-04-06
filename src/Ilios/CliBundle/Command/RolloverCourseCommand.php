<?php

namespace Ilios\CliBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

//get the course entities
use Ilios\CoreBundle\Entity\Course;
use Ilios\CoreBundle\Entity\CourseLearningMaterial;

//sessions
use Ilios\CoreBundle\Entity\Session;
use Ilios\CoreBundle\Entity\SessionLearningMaterial;

//offerings
use Ilios\CoreBundle\Entity\Offering;

//and the rest
use Ilios\CoreBundle\Entity\Objective;
use Ilios\CoreBundle\Entity\Term;

/**
 * RolloverCourse Rolls over a course using original course_id and specified year.
 *
 * Class RolloverCourseCommand
 * @package Ilios\CoreBundle\Command
 */
class RolloverCourseCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ilios:maintenance:rollover-course')
            ->setDescription('Roll over a course to a new year using its course_id')
            //required arguments
            ->addArgument(
                'courseId',
                InputArgument::REQUIRED,
                'The course_id value of the course to rollover'
            )
            ->addArgument(
                'newAcademicYear',
                InputArgument::REQUIRED,
                'The academic start year of the new course formatted as \'YYYY\''
            )
            ->addArgument(
                'newStartDate',
                InputArgument::REQUIRED,
                'The start date of the new course formatted as \'YYYY-MM-DD\''
            )
            //optional flags
            ->addOption(
                'skip-course-learning-materials',
                null,
                InputOption::VALUE_NONE,
                'Do not associate course learning materials'
            )
            ->AddOption(
                'skip-course-objectives',
                null,
                InputOption::VALUE_NONE,
                'Do not copy/recreate course objectives'
            )
            ->addOption(
                'skip-course-topics',
                null,
                InputOption::VALUE_NONE,
                'Do not copy course topics'
            )
            ->addOption(
                'skip-course-mesh',
                null,
                InputOption::VALUE_NONE,
                'Do not copy course mesh terms'
            )
            ->AddOption(
                'skip-sessions',
                null,
                InputOption::VALUE_NONE,
                'Do not copy/recreate the sessions'
            )
            ->addOption(
                'skip-session-learning-materials',
                null,
                InputOption::VALUE_NONE,
                'Do not associate session learning materials'
            )
            ->AddOption(
                'skip-session-objectives',
                null,
                InputOption::VALUE_NONE,
                'Do not copy/recreate session objectives'
            )
            ->addOption(
                'skip-session-topics',
                null,
                InputOption::VALUE_NONE,
                'Do not copy session topics'
            )
            ->addOption(
                'skip-session-mesh',
                null,
                InputOption::VALUE_NONE,
                'Do not copy session mesh terms'
            )
            ->AddOption(
                'skip-offerings',
                null,
                InputOption::VALUE_NONE,
                'Do not copy/recreate the offerings'
            )
            ->AddOption(
                'skip-instructors',
                null,
                InputOption::VALUE_NONE,
                'Do not copy instructor associations (default if --skip-offerings is set)'
            )
            ->AddOption(
                'skip-instructor-groups',
                null,
                InputOption::VALUE_NONE,
                'Do not copy instructor group associations, (default if --skip-offerings or --skip-instructors are set)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        //$cm = $this->getContainer()->get('ilioscore.course.manager');
        $courses = $em->getRepository('IliosCoreBundle:Course');

        //set the values from the input arguments
        $originalCourseId = $input->getArgument('courseId');
        $newCourseAcademicYear = $input->getArgument('newAcademicYear');
        $newStartDate = $input->getArgument('newStartDate');

        //get the course object by its course id
        $originalCourse = $courses->find($originalCourseId);

        //get the necessary attributes
        $originalCourseTitle = $originalCourse->getTitle();
        $originalCourseAcademicYear = $originalCourse->getYear();
        $originalCourseStartDate = $originalCourse->getStartDate()->format('Y-m-d');


        //check to see if the title and the new course year already exist
        $dql = 'SELECT DISTINCT c.id FROM IliosCoreBundle:Course c WHERE c.year = ?1 AND c.title = ?2';
        $query = $em->createQuery($dql);
        $query->setParameter(1, $newCourseAcademicYear);
        $query->setParameter(2, $originalCourseTitle);
        $results = $query->getResult();

        //if the title and requested year already exist, warn and exit
        if(!empty($results)) {

            $totalResults = count($results);
            $existingCourseIdArray = array();
            foreach ($results as $result) {
                $existingCourseIdArray[] = $result['id'];
            }
            $existingCourseIdString = implode(',',$existingCourseIdArray);
            $error_string = ($totalResults > 1) ? ' courses already exist' : ' course already exists';
            exit('Please check your requirements: ' . $totalResults  . $error_string . ' with that year and title (' . $existingCourseIdString . ').' . "\n");
        }

        //if there are not any duplicates, create a new course with the relevant info
        $newCourse = new Course();
        $newCourse->setTitle($originalCourseTitle);
        $newCourse->setYear($newCourseAcademicYear);

        //output for debug
        \Doctrine\Common\Util\Debug::dump($newStartDate);
        \Doctrine\Common\Util\Debug::dump($originalCourse);
        \Doctrine\Common\Util\Debug::dump($originalCourseStartDate);
        \Doctrine\Common\Util\Debug::dump($newCourse);

    }
}


