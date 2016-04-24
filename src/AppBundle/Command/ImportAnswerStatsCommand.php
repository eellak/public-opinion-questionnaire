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
            6 => array(
                1 => 120,
                2 => 121,
                3 => 122,
                4 => 123,
                99 => 124,
            ),
            8 => array(
                1 => 134,
                2 => 135,
                3 => 136,
                4 => 137,
                99 => 138,
            ),
            9 => array(
                1 => 142,
                2 => 143,
                99 => 144,
            ),
            '12.1' => array(
                1 => 165,
                2 => 166,
                3 => 167,
                4 => 168,
                99 => 169,
            ),
            '12.2' => array(
                1 => 173,
                2 => 174,
                3 => 175,
                4 => 176,
                99 => 177,
            ),
            '12.3' => array(
                1 => 181,
                2 => 182,
                3 => 183,
                4 => 184,
                99 => 185,
            ),
            '12.4' => array(
                1 => 189,
                2 => 190,
                3 => 191,
                4 => 192,
                99 => 193,
            ),
            '12.5' => array(
                1 => 197,
                2 => 198,
                3 => 199,
                4 => 200,
                99 => 201,
            ),
            13 => array(
                1 => 205,
                2 => 206,
                3 => 207,
                98 => 208,
                99 => 209,
            ),
            14 => array(
                1 => 213,
                2 => 214,
                99 => 215,
            ),
            17 => array(
                1 => 231,
                2 => 232,
                3 => 233,
                4 => 234,
                99 => 235,
            ),
            19 => array(
                1 => 248,
                2 => 249,
                3 => 250,
                99 => 251,
            ),
            22 => array(
                1 => 294,
                2 => 295,
                3 => 296,
                99 => 297,
            ),
            25 => array(
                1 => 315,
                2 => 316,
                99 => 317,
            ),
            28 => array(
                1 => 385,
                2 => 386,
                3 => 387,
                99 => 388,
            ),
            30 => array(
                1 => 398,
                2 => 399,
                3 => 400,
                4 => 401,
                99 => 402,
            ),
            33 => array(
                1 => 485,
                2 => 486,
                3 => 487,
                4 => 488,
                5 => 489,
                99 => 490,
            ),
            34 => array(
                1 => 494,
                2 => 495,
                99 => 496,
            ),
            43 => array(
                1 => 767,
                2 => 768,
                99 => 769,
            ),
            '44.1' => array(
                1 => 774,
                2 => 775,
                3 => 776,
                4 => 777,
                99 => 778,
            ),
            '44.2' => array(
                1 => 782,
                2 => 783,
                3 => 784,
                4 => 785,
                99 => 786,
            ),
            '44.3' => array(
                1 => 790,
                2 => 791,
                3 => 792,
                4 => 793,
                99 => 794,
            ),
            49 => array(
                1 => 829,
                2 => 830,
                99 => 831,
            ),
            '52.1' => array(
                0 => 851,
                1 => 855,
            ),
            '52.2' => array(
                0 => 852,
                1 => 855,
            ),
            '52.3' => array(
                0 => 853,
                1 => 855,
            ),
            '52.4' => array(
                0 => 854,
                1 => 855,
            ),
            55 => array(
                1 => 920,
                2 => 921,
                3 => 922,
                4 => 923,
                5 => 924,
                99 => 925,
            ),
            58 => array(
                1 => 1085,
                2 => 1086,
                3 => 1087,
                4 => 1088,
                5 => 1089,
                6 => 1090,
                7 => 1091,
                8 => 1092,
                99 => 1093,
            ),
            59 => array(
                1 => 1100,
                2 => 1101,
                3 => 1102,
                4 => 1103,
                99 => 1104,
            ),
            60 => array(
                1 => 1108,
                2 => 1109,
                3 => 1110,
                4 => 1111,
                99 => 1112,
            ),
            66 => array(
                1 => 1223,
                2 => 1224,
                99 => 1225,
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