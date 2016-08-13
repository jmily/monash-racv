<?php

namespace AppBundle\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

class RacvCompareCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('racv:compare')
            ->setDescription('Comparing');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $conn = $this->getContainer()->get('doctrine')->getConnection();

        $query = "SELECT * FROM `union_ad_dd` GROUP BY `PAR_ID`,`VEH_REG_ID`";
        $userCollection = $conn->fetchAll($query);

        $idCollection = [];
        foreach ($userCollection as $u) {
            $idCollection[$u['PAR_ID']] = '0';
        }

        $query = "SELECT * FROM `union_ad_dd`";
        $newData = $conn->fetchAll($query);

        $new = [];
        foreach ($newData as $n) {
            $new[$n['PAR_ID'].'-'.$n['VEH_REG_ID']] = $n['PAR_ID'];
        }

        $query = "SELECT * FROM `old_union_ad_dd`";
        $oldData = $conn->fetchAll($query);

        $old = [];
        foreach ($oldData as $o) {
            $old[$o['PAR_ID'].'-'.$o['VEH_REG_ID']] = $o['PAR_ID'];
        }


        foreach ($new as $key => $value) {
            if (!isset($old[$key])) {
                $idCollection[$value] = '1';
            }
        }


        $output->setVerbosity($output::VERBOSITY_DEBUG);
        $progress = new ProgressBar($output, count($idCollection));
        foreach ($idCollection as $k => $v) {
            $q = "INSERT INTO `step1.5` (PAR_ID,NEW_CAR) VALUES ('$k','$v')";
            $conn->query($q);
            $progress->advance();
        }

        $progress->finish();
    }
}