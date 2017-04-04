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
                1 => 3,
                2 => 4,
                3 => 5,
                4 => 6,
                88 => 7,
            ),
            3 => array(
                1 => 11,
                2 => 12,
                88 => 13,
            ),
            4 => array(
                1 => 17,
                2 => 18,
                88 => 19,
            ),
            10 => array(
                1 => 24,
                2 => 25,
                3 => 26,
                4 => 27,
                5 => 28,
            ),
            13 => array(
                1 => 32,
                2 => 33,
                3 => 34,
                88 => 35,
            ),
            14 => array(
                1 => 39,
                2 => 40,
                3 => 41,
                88 => 42,
            ),
            17 => array(
                1 => 46,
                2 => 47,
                88 => 48,
            ),
            18 => array(
                1 => 52,
                2 => 53,
                3 => 54,
                4 => 55,
                88 => 56,
            ),
            19 => array(
                1 => 60,
                2 => 61,
                3 => 62,
            ),
            20 => array(
                1 => 66,
                2 => 67,
                88 => 68,
            ),
            21 => array(
                1 => 72,
                2 => 73,
                3 => 74,
                4 => 75,
                88 => 76,
            ),
            22 => array(
                1 => 80,
                2 => 81,
                3 => 82,
                4 => 83,
                88 => 84,
            ),
            23 => array(
                1 => 88,
                2 => 89,
                88 => 90,
            ),
            '24.1' => array(
                1 => 95,
            ),
            '24.2' => array(
                1 => 96,
            ),
            '24.3' => array(
                1 => 97,
            ),
            '24.4' => array(
                1 => 98,
            ),
            '24.5' => array(
                1 => 99,
            ),
            '24.6' => array(
                1 => 100,
            ),
            29 => array(
                1 => 106,
                2 => 107,
                3 => 108,
                4 => 109,
                5 => 110,
                6 => 111,
                7 => 112,
                8 => 113,
                50 => 114,
                88 => 115,
            ),
            30 => array(
                1 => 119,
                2 => 120,
                3 => 121,
                4 => 122,
                88 => 123,
            ),
            31 => array(
                1 => 127,
                2 => 128,
                3 => 129,
                4 => 130,
                88 => 131,
            ),
            32 => array(
                1 => 135,
                2 => 136,
                88 => 137,
            ),
        );

        // Stats
        $map = array(
            'total' => array(
                'TOTAL' => 'B',
            ),
            'gender' => array(
                'MALE' => 'D',
                'FEMALE' => 'E',
            ),
            'age' => array(
                '18_24' => 'G',
                '25_39' => 'H',
                '40-54' => 'I',
                '55-64' => 'J',
                '65+' => 'K',
            ),
            'educationLevel' => array(
                'PRIMARY' => 'M',
                'SECONDARY' => 'N',
                'TERTIARY' => 'O',
                'MASTER' => 'P',
            ),
            'income' => array(
                '500-' => 'R',
                '501_1000' => 'S',
                '1001_1500' => 'T',
                '1501_2000' => 'U',
                '2001_3000' => 'V',
                '3001+' => 'W',
                'UNKNOWN' => 'X',
            ),
            'profession' => array(
                'CIVIL_SERVANT' => 'Z',
                'PRIVATE_EMPLOYEE' => 'AA',
                'FREELANCER_SCIENTIST' => 'AB',
                'FREELANCER_NON_SCIENTIST' => 'AC',
                'ENTREPRENEUR' => 'AD',
                'FARMER' => 'AE',
                'STUDENT' => 'AF',
                'HOUSEWIFE' => 'AG',
                'RETIRED' => 'AH',
                'UNEMPLOYED' => 'AI',
            ),
            'socialClass' => array(
                'LOWER' => 'AK',
                'MIDDLE' => 'AL',
                'UPPER' => 'AM',
            ),
            'region' => array(
                'ATTICA' => 'AO',
                'THESSALONIKI' => 'AP',
                'NORTH_GREECE' => 'AQ',
                'CENTRAL_GREECE' => 'AR',
                'CRETE' => 'AS',
            ),
            'region' => array(
                'URBAN' => 'AU',
                'RURAL' => 'AV',
            ),
            'politicalView' => array(
                'LEFT' => 'BH',
                'CENTER_LEFT' => 'BI',
                'CENTER' => 'BJ',
                'CENTER_RIGHT' => 'BK',
                'RIGHT' => 'BL',
                'UNKNOWN' => 'BM',
            ),
        );
        foreach($questionsMap as $questionId => $answerRows) {
            $output->writeln('Working on question '.$questionId);
            $toFlush = array();
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
                        $answerStat->setPercentage($data[self::columnIndexFromString($curCol)-1]);
                        $this->getContainer()->get('doctrine')->getManager()->persist($answerStat);
                        $toFlush[] = $answerStat;
                    }
                }
            }
            $this->getContainer()->get('doctrine')->getManager()->flush($toFlush);
        }
    }

    private static function columnIndexFromString($pString = 'A')
    {
        //    Using a lookup cache adds a slight memory overhead, but boosts speed
        //    caching using a static within the method is faster than a class static,
        //        though it's additional memory overhead
        static $_indexCache = array();
        if (isset($_indexCache[$pString])) {
            return $_indexCache[$pString];
        }
        //    It's surprising how costly the strtoupper() and ord() calls actually are, so we use a lookup array rather than use ord()
        //        and make it case insensitive to get rid of the strtoupper() as well. Because it's a static, there's no significant
        //        memory overhead either
        static $_columnLookup = array(
            'A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5, 'F' => 6, 'G' => 7, 'H' => 8, 'I' => 9, 'J' => 10, 'K' => 11, 'L' => 12, 'M' => 13,
            'N' => 14, 'O' => 15, 'P' => 16, 'Q' => 17, 'R' => 18, 'S' => 19, 'T' => 20, 'U' => 21, 'V' => 22, 'W' => 23, 'X' => 24, 'Y' => 25, 'Z' => 26,
            'a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5, 'f' => 6, 'g' => 7, 'h' => 8, 'i' => 9, 'j' => 10, 'k' => 11, 'l' => 12, 'm' => 13,
            'n' => 14, 'o' => 15, 'p' => 16, 'q' => 17, 'r' => 18, 's' => 19, 't' => 20, 'u' => 21, 'v' => 22, 'w' => 23, 'x' => 24, 'y' => 25, 'z' => 26
        );
        //    We also use the language construct isset() rather than the more costly strlen() function to match the length of $pString
        //        for improved performance
        if (isset($pString{0})) {
            if (!isset($pString{1})) {
                $_indexCache[$pString] = $_columnLookup[$pString];
                return $_indexCache[$pString];
            } elseif (!isset($pString{2})) {
                $_indexCache[$pString] = $_columnLookup[$pString{0}] * 26 + $_columnLookup[$pString{1}];
                return $_indexCache[$pString];
            } elseif (!isset($pString{3})) {
                $_indexCache[$pString] = $_columnLookup[$pString{0}] * 676 + $_columnLookup[$pString{1}] * 26 + $_columnLookup[$pString{2}];
                return $_indexCache[$pString];
            }
        }
        throw new PHPExcel_Exception("Column string index can not be " . ((isset($pString{0})) ? "longer than 3 characters" : "empty"));
    }
}