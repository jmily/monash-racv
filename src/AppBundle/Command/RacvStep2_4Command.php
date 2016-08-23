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
            $figuresArray[date('Y-m-01',strtotime($figure['date']))] = $figure;
        }


        $output->setVerbosity($output::VERBOSITY_DEBUG);
        $progress = new ProgressBar($output, count($collection));
        foreach ($collection as $data) {
            $parId = $data['PAR_ID'];
            $firstDay = date('Y-m-01',strtotime($data['date_of_interest']));
            if (isset($figuresArray[$firstDay])) {
                $index = $figuresArray[$firstDay]['price_index'];
                $inflation = $figuresArray[$firstDay]['inflation'];
                $sql = "UPDATE `change_of_vehicle` SET `customer_price_index` = ?, `inflation` = ? WHERE `PAR_ID` = ? ";
                $stmt = $conn->prepare($sql);
                $stmt->execute(array($index, $inflation, $parId));
            }
            $progress->advance();
        }
        $progress->finish();

    }
}
