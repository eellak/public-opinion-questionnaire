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
        $generalPopulationStats = $this->em->getManager()->createQuery('SELECT
            a.id,
            (SELECT COUNT(uaa.id) FROM AppBundle\Entity\UserAnswer uaa JOIN uaa.user u WHERE uaa.answer = a) as votes,
            (SELECT COUNT(uaaa.id) FROM AppBundle\Entity\UserAnswer uaaa JOIN uaaa.answer aa JOIN uaaa.user uu WHERE aa.question = a.question) as sumVotes
            FROM AppBundle\Entity\Answer a
            INDEX BY a.id
            JOIN a.question qs
            JOIN a.userAnswers uas
            WHERE uas.user = :user'.(isset($section) ? ' AND qs.section = :section' : ''))
            ->useQueryCache(true)
            ->useResultCache(true)
            ->setParameter('user', $user);
        if(isset($section)) {
            $generalPopulationStats->setParameter('section', $section);
        }
        $generalPopulationStats = $generalPopulationStats->getResult();
        foreach($generalPopulationStats as &$curStat) {
            $curStat['percentage'] = $curStat['votes']/$curStat['sumVotes'];
        }
        foreach(User::getDimensionsExpanded() as $curField => $curDimension) {
            foreach($curDimension as $curValue) {
                if($curValue == 'UNKNOWN') { continue; }
                $tstats = $this->em->getManager()->createQuery('SELECT
                    a.id,
                    (SELECT COUNT(uaab.id) FROM AppBundle\Entity\UserAnswer uaab WHERE uaab.answer = a) as votes,
                    (SELECT COUNT(uaa.id) FROM AppBundle\Entity\UserAnswer uaa JOIN uaa.user u WHERE uaa.answer = a AND u.'.$curField.' = :'.$curField.') as dimVotes,
                    (SELECT COUNT(uaaa.id) FROM AppBundle\Entity\UserAnswer uaaa JOIN uaaa.answer aa JOIN uaaa.user uu WHERE aa.question = a.question AND uu.'.$curField.' = :'.$curField.') as sumVotes
                    FROM AppBundle\Entity\Answer a
                    INDEX BY a.id
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
                foreach($tstats as &$curStat) {
                    if($curStat['sumVotes'] > 0) {
                        $curStat['percentage'] = $curStat['dimVotes']/$curStat['votes']; // Percentage of e.g. men who answered this compared to everyone who answered this
                        $tstatPercentage = $curStat['dimVotes']/$curStat['sumVotes']; // Percentage of e.g. men who answered this compared to all men (on any answer)
                        if($tstatPercentage > $generalPopulationStats[$curStat['id']]['percentage']) {
                            $curStat['weight'] = $tstatPercentage*100 - $generalPopulationStats[$curStat['id']]['percentage']*100;
                        } else {
                            $curStat['weight'] = 0;
                        }
                    } else {
                        $curStat['percentage'] = 0;
                        $curStat['weight'] = 0;
                    }
                }
                // Find the weighted mean
                $sum = 0;
                $weightSum = 0;
                foreach($tstats as &$curStat) {
                    $sum = $sum + $curStat['weight']*$curStat['percentage'];
                    $weightSum = $weightSum + $curStat['weight'];
                }

                //$percentage = $sum/$weightSum;
                $percentage = $sum/$weightSum;
                $percentage = round($percentage*100, 1);
                if($percentage <= 0) { continue; }
                $results[$curField][$curValue] = array();
                $results[$curField][$curValue]['percentage'] = $percentage;
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
