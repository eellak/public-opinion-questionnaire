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
            foreach($curDimension as $curValue) {
                if($curValue == 'UNKNOWN') { continue; }
                $stats = $this->em->getManager()->createQuery('SELECT
                    (SELECT COUNT(uaa.id) FROM AppBundle\Entity\UserAnswer uaa JOIN uaa.user u WHERE uaa.answer = a AND u.'.$curField.' = :'.$curField.') as votes,
                    (SELECT COUNT(uaaa.id) FROM AppBundle\Entity\UserAnswer uaaa JOIN uaaa.answer aa JOIN uaaa.user uu WHERE aa.question = a.question AND uu.'.$curField.' = :'.$curField.') as sumVotes
                    FROM AppBundle\Entity\Answer a
                    WHERE a = :answer')
                    // WHERE a.question = :question
                    ->setParameter('answer', $answer)
                    ->setParameter($curField, $curValue)
                    ->getSingleResult();
                $percentage = ($stats['sumVotes'] != 0 ? round($stats['votes']/$stats['sumVotes']*100, 2) : 0);
                if($percentage <= 0) { continue; }
                $results[$curField][$curValue] = $stats;
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