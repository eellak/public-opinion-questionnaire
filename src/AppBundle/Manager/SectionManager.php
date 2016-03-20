<?php
namespace AppBundle\Manager;

use AppBundle\Entity\Section;
use AppBundle\Entity\User;

class SectionManager
{
    protected $em;

    public function __construct($em) {
        $this->em = $em;
    }

    public function getSectionStats(Section $section = null, User $user = null) {
        $results = array();
        foreach(User::getDimensionsExpanded() as $curField => $curDimension) {
            foreach($curDimension as $curValue) {
                if($curValue == 'UNKNOWN') { continue; }
                $tstats = $this->em->getManager()->createQuery('SELECT
                    (SELECT COUNT(uaa.id) FROM AppBundle\Entity\UserAnswer uaa JOIN uaa.user u WHERE uaa.answer = a AND u.'.$curField.' = :'.$curField.') as votes,
                    (SELECT COUNT(uaaa.id) FROM AppBundle\Entity\UserAnswer uaaa JOIN uaaa.answer aa JOIN uaaa.user uu WHERE aa.question = a.question AND uu.'.$curField.' = :'.$curField.') as sumVotes
                    FROM AppBundle\Entity\Answer a
                    JOIN a.question qs
                    JOIN a.userAnswers uas
                    WHERE uas.user = :user'.(isset($section) ? ' AND qs.section = :section' : ''))
                    ->useQueryCache(true)
                    ->useResultCache(true)
                    ->setParameter('user', $user);
                if(isset($section)) {
                    $tstats->setParameter('section', $section);
                }
                $tstats = $tstats
                    ->setParameter($curField, $curValue)
                    ->getResult();
                $stats = array('votes' => 0, 'sumVotes' => 0);
                foreach($tstats as $curTstat) {
                    $stats['votes'] = $stats['votes'] + $curTstat['votes'];
                    $stats['sumVotes'] = $stats['sumVotes'] + $curTstat['sumVotes'];
                }
                $percentage = ($stats['sumVotes'] != 0 ? round($stats['votes']/$stats['sumVotes']*100, 2) : 0);
                if($percentage <= 0) { continue; }
                $results[$curField][$curValue] = $stats;
                $results[$curField][$curValue]['percentage'] = $percentage;
            }
            uasort($results[$curField], function($a, $b) {
                if($a['percentage'] == $b['percentage']) { return 0; }
                return $b['percentage'] - $a['percentage'];
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