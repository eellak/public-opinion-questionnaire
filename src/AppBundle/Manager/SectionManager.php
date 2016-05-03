<?php
namespace AppBundle\Manager;

use AppBundle\Entity\Section;
use AppBundle\Entity\User;
use \Doctrine\ORM\Query;

class SectionManager
{
    protected $em;

    public function __construct($em) {
        $this->em = $em;
    }

    public function getSectionStats(Section $section = null, User $user = null) {
        // Section -> question -> selectedAnswer -> dimensions percentage
        // /
        // Section -> question -> selectedAnswer -> total percentage
        $results = array();
        // Get user answer ids
        $answerIds = $this->em->getManager()->createQuery('SELECT a.id FROM AppBundle\Entity\UserAnswer ua JOIN ua.answer a WHERE ua.user = :user GROUP BY a.id')
            ->setParameter('user', $user)
            ->useQueryCache(true)
            ->getResult(Query::HYDRATE_ARRAY);
        // Get total percentages
        $rtotalCounts = $this->em->getManager()->createQuery('SELECT a.id, COUNT(ua.id) FROM AppBundle\Entity\UserAnswer ua JOIN ua.answer a WHERE a.id IN (:answerIds) GROUP BY a.id')
            ->setParameter('answerIds', $answerIds)
            ->useQueryCache(true)
            ->getResult(Query::HYDRATE_ARRAY);
        $totalCounts = array();
        foreach($rtotalCounts as $t) {
            $totalCounts[$t['id']] = $t;
        }
        unset($rtotalCounts);
        // Get dimension percentages
        foreach(array_keys(User::getDimensionsExpanded()) as $curField) {
            $dimensionCounts = $this->em->getManager()->createQuery('SELECT a.id, u.'.$curField.', COUNT(ua.id) FROM AppBundle\Entity\UserAnswer ua JOIN ua.answer a JOIN ua.user u WHERE a.id IN (:answerIds) GROUP BY a.id, u.'.$curField)
                ->setParameter('answerIds', $answerIds)
                ->useQueryCache(true)
                ->getResult(Query::HYDRATE_ARRAY);
            foreach($dimensionCounts as $curCount) {
                if($curCount[$curField] == 'UNKNOWN' || $curCount[$curField] == null) { continue; }
                $percentage = round($curCount[1]/$totalCounts[$curCount['id']][1]*100, 1);
                if($percentage <= 0) { continue; }
                $results[$curField][$curCount[$curField]] = array();
                $results[$curField][$curCount[$curField]]['percentage'] = $percentage;
            }
            uasort($results[$curField], function($a, $b) {
                if($a['percentage'] == $b['percentage']) { return 0; }
                return $a['percentage'] < $b['percentage'] ? 1 : -1;
            });
        }
        return $results;
    }

    public function getSectionStatsFlattenedSorted(Section $section = null, User $user = null) {
        $results = $this->flatten($this->getSectionStats($section, $user));
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