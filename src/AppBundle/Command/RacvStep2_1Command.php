<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;


/**
 * Insert PAR_ID, total_rows, AD1, AD2 in helper table
 * Update DD1, DD2 in helper table
 *
 */
class RacvStep2_1Command extends ContainerAwareCommand
{

    protected $endDate;
    protected $totalRows;
    protected $ad;
    protected $dd;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('racv:insert:step2_1');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $conn = $this->getContainer()->get('doctrine')->getConnection();

        //get total rows as PAR_ID => array(total_rows => [number])
        $query = "SELECT PAR_ID, count(*) as total_rows FROM `union_ad_dd` GROUP BY PAR_ID";
        $collection = $conn->fetchAll($query);


        $tempObj = [];
        foreach ($collection as $c ) {
            $tempObj[$c['PAR_ID']]['total_rows'] = $c['total_rows'];
        }


        $output->setVerbosity($output::VERBOSITY_DEBUG);
        $progress = new ProgressBar($output, count($tempObj));

        foreach ($tempObj as $parId => $data) {
            $sql = "SELECT * FROM `union_ad_dd` WHERE `PAR_ID` = '$parId' ORDER BY AD DESC";
            $result = $conn->fetchAll($sql);

            $ad1 = $result[0]['AD'];
            $dd1 = $result[0]['DD'];
            if ($data['total_rows'] == 1 ) {
                $ad2 = null;
                $dd2 = null;
            } elseif ($data['total_rows'] > 1) {
                $ad2 = $result[1]['AD'];
                $dd2 = $result[1]['DD'];
            }

            $stmt = $conn->prepare('INSERT INTO `helper` (`PAR_ID`,`total_rows`,`AD1`,`AD2`,`DD1`,`DD2`) VALUES (:data1,:data2,:data3,:data4,:data5,:data6)');
            $stmt->execute([
                ':data1' => $parId,
                ':data2' => $data['total_rows'],
                ':data3' => $ad1,
                ':data4' => $ad2,
                ':data5' => $dd1,
                ':data6' => $dd2
            ]);

            $progress->advance();
        }
        $progress->finish();
    }
}
