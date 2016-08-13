<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Update OC1, OC2 and CC in helper table
 *
 */
class RacvStep2_2Command extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('racv:update:step2_2');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $conn = $this->getContainer()->get('doctrine')->getConnection();
        $dateHelper = $this->getContainer()->get('date.helper');

        $query = "SELECT * FROM `helper`";
        $collection = $conn->fetchAll($query);



        $output->setVerbosity($output::VERBOSITY_DEBUG);
        $progress = new ProgressBar($output, count($collection));

        $sql = "SELECT * FROM `c_e_all` WHERE `ST_DTE` != '0000-00-00 00:00:00' ";
        $results = $conn->fetchAll($sql);


        foreach ($collection as $c) {
            $parId = $c['PAR_ID'];


            $oc1 = 0;
            $oc2 = 0;
            $cc = 0;
            foreach ($results as $data) {

                if ($data['PAR_ID'] == $c['PAR_ID']) {
                    if ($data['CONTR_STS_TYP'] == 'ACTIVE') {
                        $oc2++;
                    }

                    if ($data['CONTR_STS_TYP'] == 'INACTIVE') {
                        if ($dateHelper->isDateStrAGreaterThanDateStrB($data['CANCD_DTE'],$c['AD1'])) {
                            $oc1++;
                        } else {
                            $cc++;
                        }
                    }
                }
            }

            $sql = "UPDATE `helper` SET `OC1` = $oc1, `OC2` = $oc2, `CC` = $cc WHERE `PAR_ID` = '$parId' ";
            $conn->executeUpdate($sql);

            $progress->advance();
        }
        $progress->finish();
    }
}
