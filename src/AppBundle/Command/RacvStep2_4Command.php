<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

class RacvStep2_4Command extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('racv:update:step2_4')
            ->setDescription('update price index and inflation');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $conn = $this->getContainer()->get('doctrine')->getConnection();
        $query = "SELECT * FROM `change_of_vehicle`";
        $collection = $conn->fetchAll($query);

        $query = "SELECT * FROM `figures`";
        $figures = $conn->fetchAll($query);

        $figuresArray = [];
        foreach ($figures as $figure) {
            $figuresArray[date('Y',strtotime($figure['date']))] = $figure;
        }


        $output->setVerbosity($output::VERBOSITY_DEBUG);
        $progress = new ProgressBar($output, count($collection));
        foreach ($collection as $data) {
            $parId = $data['PAR_ID'];
            $yearOfInterest = date('Y',strtotime($data['date_of_interest']));
            if (isset($figuresArray[$yearOfInterest])) {
                $index = $figuresArray[$yearOfInterest]['price_index'];
                $inflation = $figuresArray[$yearOfInterest]['inflation'];
                $sql = "UPDATE `change_of_vehicle` SET `customer_price_index` = ?, `inflation` = ? WHERE `PAR_ID` = ? ";
                $stmt = $conn->prepare($sql);
                $stmt->execute(array($index, $inflation, $parId));
            }
            $progress->advance();
        }
        $progress->finish();

    }
}
