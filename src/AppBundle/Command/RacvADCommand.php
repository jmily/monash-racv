<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

class RacvADCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('racv:ad-dd')
            ->addOption('PAR_ID',null,InputOption::VALUE_OPTIONAL)
            ->addOption('VEH_ID',null,InputOption::VALUE_OPTIONAL)
            ->setDescription('Appending AD DD');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $conn = $this->getContainer()->get('doctrine')->getConnection();

        $inputParId = $input->getOption('PAR_ID');
        $inputVehID = $input->getOption('VEH_ID');

        $where = '';
        if ($inputParId) {
            $where = "WHERE `PAR_ID` ='$inputParId'";
            if ($inputVehID) {
                $where .= " AND `VEH_REG_ID` = '$inputVehID'";
            }
        }

        $query = "SELECT * FROM `c_e` ".$where." GROUP BY `PAR_ID`,`VEH_REG_ID`";

        $dataToAppend = $conn->fetchAll($query);

        $numberOfQuery = (count($dataToAppend));

        $output->setVerbosity($output::VERBOSITY_DEBUG);
        $progress = new ProgressBar($output, $numberOfQuery);


        $output->writeln([
            'Inserting data',
            '============',
            '',
        ]);
        foreach ($dataToAppend as $d) {
            $parId = $d['PAR_ID'];
            $vehId = $d['VEH_REG_ID'];
            $q = "SELECT * FROM `c_e` WHERE `PAR_ID` = '$parId' AND `VEH_REG_ID`='$vehId' ORDER BY `PAR_ID` DESC, `VEH_REG_ID` DESC, `ST_DTE` DESC";
            $data = $conn->fetchAll($q);

            $numberOfResult = count($data);
            if ($numberOfResult == 1) {
                $newestCancellationDate = '2100-01-01';
                if ($data[0]['CANCD_DTE'] != '0000-00-00 00:00:00') {
                    $newestCancellationDate = $data[0]['CANCD_DTE'];
                }

                $newestStartDate = $data[0]['ST_DTE'];
                $oldestStartDate = $newestStartDate;

            } else if ($numberOfResult > 1) {

                $newestStartDate = $data[0]['ST_DTE'];
                $oldestStartDate = $data[$numberOfResult - 1]['ST_DTE'];

                $q2 = "SELECT `CANCD_DTE` FROM `c_e` WHERE `PAR_ID` = '$parId' AND `VEH_REG_ID` = '$vehId' ORDER BY `CANCD_DTE` DESC ";
                $result = $conn->fetchAll($q2);

                $newestCancellationDate = $result[0]['CANCD_DTE'];
                if ($newestCancellationDate == '0000-00-00 00:00:00') {
                    $newestCancellationDate = '2100-01-01';
                }
            }

            $ad = $oldestStartDate;
            if (strtotime($newestCancellationDate) > strtotime($newestStartDate)) {
                $dd = $newestCancellationDate;
            } else {
                $dd = '2100-01-01';
            }


            $insertSql = "INSERT INTO `union_ad_dd` (`PAR_ID`,`VEH_REG_ID`,`AD`,`DD`) VALUES ( '$parId', '$vehId', '$ad', '$dd') ";
            $conn->query($insertSql);

            $progress->advance();
        }
        $progress->finish();
    }
}
