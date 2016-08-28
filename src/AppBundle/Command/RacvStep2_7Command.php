<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

class RacvStep2_7Command extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('racv:update:step2_7')
            ->setDescription('update membership information');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $conn = $this->getContainer()->get('doctrine')->getConnection();
        $dateHelper = $this->getContainer()->get('date.helper');
        $query = "SELECT * FROM `change_of_vehicle`";
        $collection = $conn->fetchAll($query);

        $query = "SELECT * FROM `a`";
        $memberInfo = $conn->fetchAll($query);

        $memberArray = [];

        foreach ($memberInfo as $member) {
            $memberArray[$member['PAR_ID']] = $member;
        }

        $output->setVerbosity($output::VERBOSITY_DEBUG);
        $progress = new ProgressBar($output, count($collection));
        foreach ($collection as $data) {
            if (isset($memberArray[$data['PAR_ID']])) {
                $parId = $data['PAR_ID'];
                $birthday = $memberArray[$data['PAR_ID']]['BTH_DTE'];
                $dateOfInterest = $data['date_of_interest'];

                $age = null;
                if ($birthday != '0000-00-00') {
                    $age = $dateHelper->dateDiffOfYear($dateOfInterest,$birthday);
                }

                $gender = $memberArray[$data['PAR_ID']]['GND_CD'];
                $membershipStartDate = $memberArray[$data['PAR_ID']]['MSHP_JND_DTE'];
                $postcode = $memberArray[$data['PAR_ID']]['PSTL_ADDR_PCDE'];

                $sql = "UPDATE `change_of_vehicle` SET `member_age` = ?, `member_gender` = ?, `membership_start_date` =?, `address_post_code` =? WHERE `PAR_ID` = ? ";
                $stmt = $conn->prepare($sql);
                $stmt->execute(array($age, $gender, $membershipStartDate, $postcode,$parId));

            }
            $progress->advance();
        }

        $progress->finish();
    }
}
