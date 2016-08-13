<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RacvReviseCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('racv:revise:data')
            ->setDescription('Hello PhpStorm');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $conn = $this->getContainer()->get('doctrine')->getConnection();
        $sql = "SELECT `final`.*,`c_e_all`.* FROM `final` LEFT JOIN `c_e_all` on `c_e_all`.`PAR_ID` = `final`.`PAR_ID` WHERE `NEW_CAR` = \"\" AND `c_e_all`.`ST_DTE` != '0000-00-00 00:00:00'";

        $data = $conn->fetchAll($sql);


        foreach ($data as $d) {
            $PAR_ID = $d['PAR_ID'];
            $sql = "UPDATE `final` SET `NEW_CAR` = '0' WHERE `PAR_ID` = '$PAR_ID'";
            $conn->query($sql);
        }

    }
}
