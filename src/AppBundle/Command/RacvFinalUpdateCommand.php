<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

class RacvFinalUpdateCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('racv:final:update');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $conn = $this->getContainer()->get('doctrine')->getConnection();
        $sql = "SELECT * FROM `empty_veh`";

        $collection = $conn->fetchAll($sql);

        $progress = new ProgressBar($output, count($collection));
        foreach ($collection as $veh) {
            $PAR_ID = $veh['PAR_ID'];

            $query = "SELECT * FROM `c_e_all_old` WHERE `PAR_ID` = '$PAR_ID'";
            $oldData = $conn->fetchAll($query);

            $find = false;
            foreach ($oldData as $old) {
                if ($old['MAKE_MDL_TXT'] == $veh['MAKE_MDL_TXT'] && $old['MANUF_YR'] == $veh['MANUF_YR']) {
                    $find = true;
                }
            }

            if (!$find) {
                $sql = "UPDATE `final` SET `NEW_CAR` = '1' WHERE `PAR_ID` = $PAR_ID ";
                $conn->query($sql);
            }

            $progress->advance();
        }

        $progress->finish();
    }
}
