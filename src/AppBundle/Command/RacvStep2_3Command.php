<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

class RacvStep2_3Command extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('racv:insert:step2_3');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $endDate = '2016-06-01 00:00:00';
        $conn = $this->getContainer()->get('doctrine')->getConnection();
        $dateHelper = $this->getContainer()->get('date.helper');

        $query = "SELECT * FROM `helper`";
        $collection = $conn->fetchAll($query);

        $output->setVerbosity($output::VERBOSITY_DEBUG);
        $progress = new ProgressBar($output, count($collection));
        foreach ($collection as $data) {
            $parId = $data['PAR_ID'];
            $cc = $data['CC'];
            if ($data['total_rows'] >1 ) {
                if ($dateHelper->isDateStrAGreaterThanDateStrB($data['DD1'], $endDate)) {
                    if ($dateHelper->isDateStrAGreaterThanDateStrB($data['DD2'], $endDate)) {
                        $cen = 0;
                        $transitionType = 'AC';
                        $duration = $dateHelper->dateStringAMinusDateStringB($data['AD1'], $data['AD2']);
                        $dateOfInterest = $data['AD1'];
                        $oc = $data['OC1'] + $data['OC2'];
                    } else {
                        if ($dateHelper->isDateStrAGreaterThanDateStrB($data['DD2'], $dateHelper->getDateStrByMonthDiff($data['AD1'],'+1 month') )) {
                            $cen = 0;
                            $transitionType = 'AC';
                            $duration = $dateHelper->dateStringAMinusDateStringB($data['AD1'],$data['AD2']);
                            $dateOfInterest = $data['AD1'];
                            $oc = $data['OC1'] + $data['OC2'];
                        } else {
                            if ($dateHelper->isDateStrAGreaterThanDateStrB($data['DD2'],$dateHelper->getDateStrByMonthDiff($data['AD1'],'-1 month')) &&
                                $dateHelper->isDateStrAGreaterThanDateStrB($dateHelper->getDateStrByMonthDiff($data['AD1'],'+1 month'),$data['DD2'])
                            ) {
                                $cen = 0;
                                $transitionType = 'TR';
                                $duration = $dateHelper->dateStringAMinusDateStringB($data['AD1'],$data['AD2']);
                                $dateOfInterest = $data['AD1'];
                                $oc = $data['OC1'] + $data['OC2'] + 1;
                            } else {
                                $cen = 1;
                                $transitionType = 'NULL';
                                $duration = $dateHelper->dateStringAMinusDateStringB($endDate,$data['AD1']);
                                $dateOfInterest = $endDate;
                                $oc = $data['OC1'] + $data['OC2'] + 1;
                            }
                        }

                    }
                } else {
                    if ($dateHelper->isDateStrAGreaterThanDateStrB($data['DD2'], $endDate)) {
                        $cen = 0;
                        $transitionType = 'AC';
                        $duration = $dateHelper->dateStringAMinusDateStringB($data['AD1'],$data['AD2']);
                        $dateOfInterest = $data['AD1'];
                        $oc = $data['OC1'] + $data['OC2'] + 1;
                    } else {
                        if ($dateHelper->isDateStrAGreaterThanDateStrB($data['DD2'], $dateHelper->getDateStrByMonthDiff($data['AD1'],'+1 month'))) {
                            $cen = 0;
                            $transitionType = 'AC';
                            $duration = $dateHelper->dateStringAMinusDateStringB($data['AD1'],$data['AD2']);
                            $dateOfInterest = $data['AD1'];
                            $oc = $data['OC1'] + $data['OC2'] + 1;
                        } else {
                            //left green
                            if ($dateHelper->isDateStrAGreaterThanDateStrB($data['DD2'],$dateHelper->getDateStrByMonthDiff($data['AD1'],'-1 month')) &&
                                $dateHelper->isDateStrAGreaterThanDateStrB($dateHelper->getDateStrByMonthDiff($data['AD1'],'+1 month'), $data['DD2'])
                            ) {
                                $cen = 0;
                                $transitionType = 'TR';
                                $duration = $dateHelper->dateStringAMinusDateStringB($data['AD1'],$data['AD2']);
                                $dateOfInterest = $data['AD1'];
                                $oc = $data['OC1'] + $data['OC2'] + 1;
                            } else {
                                $cen = 0;
                                $transitionType = 'DI';
                                $duration = $dateHelper->dateStringAMinusDateStringB($data['DD1'],$data['AD1']);
                                $dateOfInterest = $data['DD1'];
                                $oc = $data['OC1'] + $data['OC2'] + 1;
                            }
                        }
                    }

                }

            } else {
                if ($dateHelper->isDateStrAGreaterThanDateStrB($endDate,$data['DD1'])) {
                    $cen = 0;
                    $transitionType = 'DI';
                    $duration = $dateHelper->dateStringAMinusDateStringB($data['DD1'],$data['AD1']);
                    $dateOfInterest = $data['DD1'];
                    $oc = $data['OC1'] + $data['OC2'];
                } else {
                    $cen = 1;
                    $transitionType = 'NULL';
                    $duration = $dateHelper->dateStringAMinusDateStringB($endDate,$data['AD1']);
                    $dateOfInterest = $endDate;
                    $oc = $data['OC1'] + $data['OC2'] + 1;
                }

            }

            $yearOfTransaction = date('Y',strtotime($dateOfInterest));

            $sql = "INSERT INTO `change_of_vehicle` (`PAR_ID`,`censor`,`duration`,`transition_type`,`open_contract`,`completed_contract`,`year_transaction`,`date_of_interest`) 
                    VALUES ('$parId',$cen,$duration,'$transitionType',$oc,$cc,$yearOfTransaction,'$dateOfInterest') ";
            $conn->executeQuery($sql);

            $progress->advance();
        }

        $progress->finish();


    }
}
