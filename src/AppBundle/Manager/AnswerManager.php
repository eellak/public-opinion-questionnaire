<?php
namespace AppBundle\Manager;

use AppBundle\Entity\Answer;
use AppBundle\Entity\User;

class AnswerManager
{
    protected $em;

    public function __construct($em) {
        $this->em = $em;
    }

    public function getAnswerStats(Answer $answer) {
        $results = array();
        foreach(User::getDimensionsExpanded() as $curField => $curDimension) {
            $stats = $this->em->getManager()->createQuery('SELECT
                ans.dimensionValue, ans.percentage
                FROM AppBundle\Entity\AnswerStat ans
                INDEX BY ans.dimensionValue
                WHERE ans.answer = :answer AND ans.dimension = :curField')
                // WHERE a.question = :question
                ->setParameter('answer', $answer)
                ->setParameter('curField', $curField)
                ->useQueryCache(true)
                ->useResultCache(true)
                ->getResult();
            foreach($curDimension as $curValue) {
                if($curValue == 'UNKNOWN') { continue; }
                if(!isset($stats[$curValue])) { continue; }
                $percentage = $stats[$curValue]['percentage'];
                if($percentage <= 0) { continue; }
                $results[$curField][$curValue] = $stats[$curValue];
                $results[$curField][$curValue]['percentage'] = $percentage;
            }
        }
        return $results;
    }

    public function getAnswerStatsFlattenedSorted(Answer $answer) {
        $results = $this->flatten($this->getAnswerStats($answer));
        asort($results);
        return $results;
    }

    private function flatten($array, $prefix = '') {
        $result = array();
        foreach($array as $key=>$value) {
            if(is_array($value)) {
                $result = $result + $this->flatten($value, $prefix . $key . '.');
            }
            else {
                if($key == 'percentage') {
                    $result[$prefix . $key] = $value;
                }
            }
        }
        return $result;
    }
}