<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

class RacvStep2_5Command extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('racv:update:step2_5')
            ->setDescription('update breakdown');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $conn = $this->getContainer()->get('doctrine')->getConnection();
        $dateHelper = $this->getContainer()->get('date.helper');
        $query = "SELECT * FROM `change_of_vehicle`";
        $collection = $conn->fetchAll($query);

        $output->setVerbosity($output::VERBOSITY_DEBUG);
        $progress = new ProgressBar($output, count($collection));
        foreach ($collection as $data) {
            $dateOfInterest = $data['date_of_interest'];
            $parId = $data['PAR_ID'];
            $sql = "SELECT * FROM `f` WHERE `INCDNT_DTE` <= '$dateOfInterest' AND  `PAR_ID` = '$parId' ORDER BY INCDNT_DTE DESC";
            $results = $conn->fetchAll($sql);

            if ($results != null) {
                $latestBreakdownDate = $results[0]['INCDNT_DTE'];

                $lastOneYear = date('Y-m-d',strtotime($dateOfInterest.'-1 year'));
                $lastFineYear = date('Y-m-d',strtotime($dateOfInterest.'-5 year'));

                $numOfBreakdownPastOneYear = 0;
                $numOfBreakdownPastFiveYear = 0;

                foreach ($results as $result) {
                    if ($dateHelper->isDateStrAGreaterThanDateStrB($result['INCDNT_DTE'],$lastOneYear)) {
                        $numOfBreakdownPastOneYear++;
                    }

                    if ($dateHelper->isDateStrGreaterThanOrEqualsToDateStrB($result['INCDNT_DTE'],$lastFineYear)) {
                        $numOfBreakdownPastFiveYear++;
                    }
                }

                $sql = "UPDATE `change_of_vehicle` SET `num_breakdown_past_one_year` = ?, `num_breakdown_past_five_year` = ?, `days_since_last_breakdown` =? WHERE `PAR_ID` = ? ";
                $stmt = $conn->prepare($sql);
                $stmt->execute(array($numOfBreakdownPastOneYear, $numOfBreakdownPastFiveYear, $latestBreakdownDate, $parId));

            }
            $progress->advance();
        }
        $progress->finish();
    }
}
