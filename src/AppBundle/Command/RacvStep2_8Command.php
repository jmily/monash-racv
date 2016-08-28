<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

class RacvStep2_8Command extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('racv:update:step2_8')
            ->setDescription('update vehicle model');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $conn = $this->getContainer()->get('doctrine')->getConnection();
        $query = "SELECT * FROM `change_of_vehicle`";
        $collection = $conn->fetchAll($query);


        $output->setVerbosity($output::VERBOSITY_DEBUG);
        $progress = new ProgressBar($output, count($collection));
        foreach ($collection as $data) {
            $parId = $data['PAR_ID'];

            $query = "SELECT * FROM `union_ad_dd` WHERE `PAR_ID` = '$parId' ORDER BY AD DESC";
            $results = $conn->fetchAll($query);

            $totalRows = count($results);

            if ($data['transition_type'] == 'AC' ||$data['transition_type'] == 'TR' ) {
                $currentModelYear = $results[0]['model_year'];
                $previousModelYear = $results[1]['model_year'];
            } elseif ($data['transition_type'] == 'DI') {
                $currentModelYear = $results[0]['model_year'];
                $previousModelYear = '-1';
                if ($totalRows > 1) {
                    $previousModelYear = $results[1]['model_year'];
                }
            } elseif ($data['transition_type'] == 'NULL') {
                $currentModelYear = '-1';
                $previousModelYear = $results[0]['model_year'];
            }

            $sql = "UPDATE `change_of_vehicle` SET `current_vehicle_model_year` = ?, `previous_vehicle_model_year` = ? WHERE `PAR_ID` = ? ";
            $stmt = $conn->prepare($sql);
            $stmt->execute(array($currentModelYear, $previousModelYear,$parId));
            $progress->advance();
        }
        $progress->finish();
    }

}
