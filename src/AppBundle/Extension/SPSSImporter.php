<?php
namespace AppBundle\Extension;

use AppBundle\Entity\User;
use AppBundle\Entity\Dataset;
use AppBundle\Entity\Question;
use AppBundle\Entity\Answer;
use AppBundle\Entity\UserAnswer;

class SPSSImporter
{
    protected $doctrine;

    // Data attributes
    protected $dimensionIds = array(
        'NUTS1' => 'region',
        'UrbanRural' => 'urbanity',
        'Gender' => 'gender',
        'age5cats' => 'age',
        'profession' => 'profession',
        'edu' => 'educationLevel',
        'income' => 'income',
        //'pastvote' => 'politicalView',
        'Class' => 'socialClass',
    );
    protected $questions;
    protected $dimensions;

    protected $allAnswers;

    public function __construct($doctrine) {
        $this->doctrine = $doctrine;
    }

    public function import(\SPSSReader $SPSS, $filename) {
        // Create the dataset
        $dataset = $this->doctrine->getRepository('AppBundle\Entity\Dataset')->findOneBy(array(
            'filename' => $filename,
        ));
        if(!isset($dataset)) {
            $dataset = new Dataset();
            $dataset->setFilename($filename);
            $this->doctrine->getManager()->persist($dataset);
            $this->doctrine->getManager()->flush($dataset);
        }

        // Import questions
        $this->importQuestions($SPSS, $dataset);

        // Import answers
        $this->importAnswers($SPSS, $dataset);
    }

    private function isDimension($questionId) {
        if(in_array($questionId, array_keys($this->dimensionIds))) {
            return true;
        } else {
            return false;
        }
    }

    private function importQuestions(\SPSSReader $SPSS, Dataset $dataset) {
        $toFlush = array();
        foreach($SPSS->variables as $var) {
            if($var->isExtended) { continue; }
            $index = isset($SPSS->extendedNames[$var->shortName]) ? $SPSS->extendedNames[$var->shortName] : $var->name;

            // -- Split question id --
            //$questionSplitted = explode(' ', mb_convert_encoding($var->label, 'UTF-8', 'ISO-8859-7'), 2);
            $questionSplitted = explode(' ', $var->label, 2);
            $questionSplitted[0] = rtrim($questionSplitted[0], '.');
            /*$tmpSplit = explode('.', $questionSplitted[0], 2);
            if($tmpSplit[0] == '3') { $tmpSplit[0] = '9'; } // C
            else if($tmpSplit[0] == '4') { $tmpSplit[0] = '8'; } // C
            else if($tmpSplit[0] == '5') { $tmpSplit[0] = '10'; } // C
            else if($tmpSplit[0] == '6') { $tmpSplit[0] = '12'; } // C
            else if($tmpSplit[0] == '7') { $tmpSplit[0] = '13'; } // C
            else if($tmpSplit[0] == '8') { $tmpSplit[0] = '19'; } // PROBLEM
            else if($tmpSplit[0] == '9') { $tmpSplit[0] = '23'; } // C
            else if($tmpSplit[0] == '10') { $tmpSplit[0] = '24'; } // C
            else if($tmpSplit[0] == '11') { $tmpSplit[0] = '25'; } // C
            else if($tmpSplit[0] == '12') { $tmpSplit[0] = '36'; } // C
            else if($tmpSplit[0] == '13') { $tmpSplit[0] = '37'; } // C
            else if($tmpSplit[0] == '14') { $tmpSplit[0] = '38'; } // C
            else if($tmpSplit[0] == '15') { $tmpSplit[0] = '53'; } // C
            else if($tmpSplit[0] == '16') { $tmpSplit[0] = '56'; } // C
            $questionSplitted[0] = implode('.', $tmpSplit);*/
            // -----------------------
            $allQuestions = $this->doctrine->getRepository('AppBundle\Entity\Question')->findAll();
            foreach($allQuestions as $curQuestion) { $this->allQuestions[$index] = $curQuestion; }
            $allAnswers = $this->doctrine->getRepository('AppBundle\Entity\Answer')->findAll();
            foreach($allAnswers as $curAnswer) { $this->allAnswers[$index.'_'.$curAnswer->getAnswerId()] = $curAnswer; }
            if($questionSplitted[0] == '') {
                $questionSplitted[0] = '';
            }

            $question = new Question();
            $question->setQuestionId($index);
            $question->setQuestion(isset($questionSplitted[1]) ? $questionSplitted[1] : 'Unknown');
            $question->setDataset($dataset);
            if(!$this->isDimension($index)) {
                $this->doctrine->getManager()->persist($question);
                $toFlush[] = $question;
                $this->questions[$index] = $question;
            } else {
                $this->dimensions[$index] = $question;
            }
            foreach($var->valueLabels as $lkey => $lval) {
                if(!isset($this->allAnswers[$index.'_'.$lkey])) {
                    $answer = new Answer();
                    $answer->setAnswerId($lkey);
                    $this->allAnswers[$index.'_'.$lkey] = $answer;
                } else {
                    throw new \Exception('Duplicate answer '.$index.'_'.$lkey);
                    //$answer = $this->allAnswers[$index.'_'.$lkey];
                }
                var_dump($index.'_'.$lkey);
                //$answer->setAnswer(mb_convert_encoding($lval, 'UTF-8', 'ISO-8859-7'));
                $answer->setAnswer($lval);
                $answer->setQuestion($question);
                $question->getAnswers()->add($answer);
                if(!$this->isDimension($index)) {
                    $this->doctrine->getManager()->persist($answer);
                    $toFlush[] = $answer;
                }
            }
        }
        if(count($toFlush) > 0) {
            $this->doctrine->getManager()->flush($toFlush);
        }
    }

    private function importAnswers(\SPSSReader $SPSS, Dataset $dataset) {
        // Loop through the answers
        $SPSS->loadData();
        for($case=0; $case<$SPSS->header->numberOfCases; $case++) {
            $user = new User();
            $user->setSessionId(microtime());
            $user->setAutoGenerated(true);
            $toFlush = array($user);
            foreach($SPSS->variables as $var) {
                if ($var->isExtended) { continue; }
                $index = isset($SPSS->extendedNames[$var->shortName]) ? $SPSS->extendedNames[$var->shortName] : $var->name;

                if(isset($this->dimensions[$index])) { // This is a dimension attribute
                    // Check if the dimension is a valid profile dimension
                    if($this->isValidProfileDimension($index)) {
                        // Set the profile dimension
                        $dimension = $this->dimensionIds[$index];
                        $setter = 'set'.ucfirst($dimension);
                        $user->$setter($this->mapProfileDimensionValue($dimension, $var->data[$case]==='NaN'?'':$var->data[$case]));
                    }
                } else if($this->questions[$index]) {
                    // Create a UserAnswer
                    $userAnswer = new UserAnswer();
                    $userAnswer->setDataset($dataset);
                    $userAnswer->setUser($user);
                    if($var->data[$case]==='NaN' || $var->data[$case]=='') { continue; }
                    if(!isset($this->allAnswers[$index.'_'.$var->data[$case]])) {
                        continue;
                        //throw new \Exception('Could not find user answer for '.$index.' ('.$var->data[$case].') given');
                    }
                    $answer = $this->allAnswers[$index.'_'.$var->data[$case]];
                    $userAnswer->setAnswer($answer);
                    $this->doctrine->getManager()->persist($userAnswer);
                    $toFlush[] = $userAnswer;
                } else {
                    throw new \Exception('Answer to a non-existing question! '.$index);
                }
            }
            $this->doctrine->getManager()->persist($user);
            $this->doctrine->getManager()->flush($toFlush);
            foreach($toFlush as $curEntity) {
                $this->doctrine->getManager()->detach($curEntity); // To save memory
            }
        }
    }

    private function isValidProfileDimension($dimensionId) {
        $dimension = $this->dimensionIds[$dimensionId];
        $profileDimensions = User::getDimensionsExpanded();
        if(in_array($dimension, array_keys($profileDimensions))) {
            return true;
        } else {
            return false;
        }
    }

    private function mapProfileDimensionValue($dimension, $origValue) {
        $map = array(
            'gender' => array(
                1 => User::GENDER_MALE,
                2 => User::GENDER_FEMALE,
            ),
            'age' => array(
                1 => User::AGE_18_24,
                2 => User::AGE_25_39,
                3 => User::AGE_40_54,
                4 => User::AGE_55_64,
                5 => User::AGE_65P,
            ),
            'educationLevel' => array(
                1 => User::EDUCATION_PRIMARY,
                2 => User::EDUCATION_PRIMARY,
                3 => User::EDUCATION_SECONDARY,
                4 => User::EDUCATION_SECONDARY,
                5 => User::EDUCATION_SECONDARY,
                6 => User::EDUCATION_TERTIARY,
                7 => User::EDUCATION_MASTER,
            ),
            'income' => array(
                1 => User::INCOME_500M,
                2 => User::INCOME_501_1000,
                3 => User::INCOME_1001_1500,
                4 => User::INCOME_1501_2000,
                5 => User::INCOME_2001_3000,
                6 => User::INCOME_3001P,
                7 => User::INCOME_3001P,
                88 => User::INCOME_UNKNOWN,
            ),
            'profession' => array(
                1 => User::PROFESSION_CIVIL_SERVANT,
                2 => User::PROFESSION_PRIVATE_EMPLOYEE,
                3 => User::PROFESSION_RETIRED,
                4 => User::PROFESSION_RETIRED,
                5 => User::PROFESSION_FREELANCER_NON_SCIENTIST,
                6 => User::PROFESSION_FREELANCER_SCIENTIST,
                7 => User::PROFESSION_HOUSEWIFE,
                8 => User::PROFESSION_UNEMPLOYED,
                9 => User::PROFESSION_STUDENT,
                10 => User::PROFESSION_FARMER,
                11 => User::PROFESSION_UNKNOWN,
                12 => User::PROFESSION_ENTREPRENEUR,
            ),
            'socialClass' => array(
                1 => User::SOCIAL_CLASS_LOWER,
                2 => User::SOCIAL_CLASS_MIDDLE,
                3 => User::SOCIAL_CLASS_UPPER,
                88 => User::SOCIAL_CLASS_UNKNOWN,
            ),
            'region' => array(
                0 => User::REGION_ATTICA,
                1 => User::REGION_THESSALONIKI,
                2 => User::REGION_NORTH_GREECE,
                3 => User::REGION_CENTRAL_GREECE,
                4 => User::REGION_CRETE,
            ),
            'urbanity' => array(
                1 => User::URBANITY_URBAN,
                2 => User::URBANITY_RURAL,
            ),
            /*'politicalView' => array(
                1 => User::POLITICAL_VIEW_LEFT,
                3 => User::POLITICAL_VIEW_CENTER_LEFT,
                4 => User::POLITICAL_VIEW_CENTER,
                5 => User::POLITICAL_VIEW_CENTER_RIGHT,
                6 => User::POLITICAL_VIEW_RIGHT,
                88 => User::POLITICAL_VIEW_UNKNOWN,
            ),*/
        );
        return $map[$dimension][$origValue];
    }
}