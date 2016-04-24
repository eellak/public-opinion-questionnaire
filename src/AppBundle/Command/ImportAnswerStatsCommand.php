<?php
namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Entity\AnswerStat;

class ImportAnswerStatsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('poq:importanswerstats')
            ->setDescription('Import XLS file')
            ->addArgument('file', InputArgument::REQUIRED)
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Starting ImportSPSS process');

        $allData = array_map('str_getcsv', file($input->getArgument('file')));

        $questionsMap = array( // question_id => answerRows
            1 => array( // answer_id => csv row
                1 => 6,
                2 => 7,
                3 => 8,
                4 => 9,
                99 => 10,
            ),
        );

        // Stats
        $map = array(
            'total' => array(
                'TOTAL' => 'C',
            ),
            'gender' => array(
                'MALE' => 'E',
                'FEMALE' => 'F',
            ),
            'age' => array(
                '18_24' => 'H',
                '25_39' => 'I',
                '40-54' => 'J',
                '55-64' => 'K',
                '65+' => 'L',
            ),
            'educationLevel' => array(
                'PRIMARY' => 'N',
                'SECONDARY' => 'O',
                'TERTIARY' => 'P',
                'MASTER' => 'Q',
            ),
            'income' => array(
                '500-' => 'S',
                '501_1000' => 'T',
                '1001_1500' => 'U',
                '1501_2000' => 'V',
                '2001_3000' => 'W',
                '3001+' => 'X',
                'UNKNOWN' => 'Y',
            ),
            'profession' => array(
                'CIVIL_SERVANT' => 'AA',
                'PRIVATE_EMPLOYEE' => 'AB',
                'FREELANCER_SCIENTIST' => 'AC',
                'FREELANCER_NON_SCIENTIST' => 'AD',
                'ENTREPRENEUR' => 'AE',
                'FARMER' => 'AF',
                'STUDENT' => 'AG',
                'HOUSEWIFE' => 'AH',
                'RETIRED' => 'AI',
                'UNEMPLOYED' => 'AJ',
            ),
            'socialClass' => array(
                'LOWER' => 'AL',
                'MIDDLE' => 'AM',
                'UPPER' => 'AN',
            ),
            'region' => array(
                'ATTICA' => 'AP',
                'THESSALONIKI' => 'AQ',
                'CENTRAL_GREECE' => 'AS',
                'NORTH_AEGEAN' => 'AR',
                'CRETE' => 'AT',
            ),
            'region' => array(
                'URBAN' => 'AV',
                'RURAL' => 'AW',
            ),
            'politicalView' => array(
                'LEFT' => 'AY',
                'CENTER_LEFT' => 'AZ',
                'CENTER' => 'BA',
                'CENTER_RIGHT' => 'BB',
                'RIGHT' => 'BC',
                'UNKNOWN' => 'BD',
            ),
        );
        $toFlush = array();
        foreach($questionsMap as $questionId => $answerRows) {
            $output->writeln('Working on question '.$questionId);
            foreach($answerRows as $answerId => $dataRow) {
                // Fetch data
                $data = $allData[$dataRow-1];
                $question = $this->getContainer()->get('doctrine')->getRepository('AppBundle\Entity\Question')->findOneBy(array(
                    'questionId' => $questionId,
                ));
                if(!isset($question)) { throw new \Exception('Question '.$questionId.' not found'); }
                $answer = $this->getContainer()->get('doctrine')->getRepository('AppBundle\Entity\Answer')->findOneBy(array(
                    'question' => $question,
                    'answerId' => $answerId,
                ));
                if(!isset($answer)) { throw new \Exception('Answer '.$answerId.' for question '.$questionId.' not found'); }

                foreach($map as $curDimension => $curDimensionValues) {
                    foreach($curDimensionValues as $curValue => $curCol) {
                        $answerStat = new AnswerStat();
                        $answerStat->setAnswer($answer);
                        $answerStat->setDimension($curDimension);
                        $answerStat->setDimensionValue($curValue);
                        $answerStat->setPercentage($data[$this->toNumber($curCol)-1]);
                        $this->getContainer()->get('doctrine')->getManager()->persist($answerStat);
                        $toFlush[] = $answerStat;
                    }
                }
            }
        }
        $this->getContainer()->get('doctrine')->getManager()->flush($toFlush);
    }

    private function toNumber($dest)
    {
        if ($dest)
            return ord(strtolower($dest)) - 96;
        else
            return 0;
    }
}