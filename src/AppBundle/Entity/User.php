<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Answer
 *
 * @ORM\Table
 * @ORM\Entity
 */
class User
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    /**
     * @ORM\Column(name="sessionId", type="string", nullable=false, unique=true)
     */
    private $sessionId;
    /**
     * @ORM\Column(name="email", type="string", length=1024, nullable=true)
     */
    private $email;
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $gender;
    const GENDER_MALE = 'MALE';
    const GENDER_FEMALE = 'FEMALE';
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $age;
    const AGE_18_24 = '18_24';
    const AGE_25_39 = '25_39';
    const AGE_40_54 = '40-54';
    const AGE_55_64 = '55-64';
    const AGE_65P = '65+';
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $educationLevel;
    const EDUCATION_PRIMARY = 'PRIMARY';
    const EDUCATION_SECONDARY = 'SECONDARY';
    const EDUCATION_TERTIARY = 'TERTIARY';
    const EDUCATION_MASTER = 'MASTER';
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $income;
    const INCOME_500M = '500-';
    const INCOME_501_1000 = '501_1000';
    const INCOME_1001_1500 = '1001_1500';
    const INCOME_1501_2000 = '1501_2000';
    const INCOME_2001_3000 = '2001_3000';
    const INCOME_3001P = '3001+';
    const INCOME_UNKNOWN = 'UNKNOWN';
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $profession;
    const PROFESSION_CIVIL_SERVANT = 'CIVIL_SERVANT';
    const PROFESSION_PRIVATE_EMPLOYEE = 'PRIVATE_EMPLOYEE';
    const PROFESSION_FREELANCER_SCIENTIST = 'FREELANCER_SCIENTIST';
    const PROFESSION_FREELANCER_NON_SCIENTIST = 'FREELANCER_NON_SCIENTIST';
    const PROFESSION_ENTREPRENEUR = 'ENTREPRENEUR';
    const PROFESSION_FARMER = 'FARMER';
    const PROFESSION_STUDENT = 'STUDENT';
    const PROFESSION_HOUSEWIFE = 'HOUSEWIFE';
    const PROFESSION_RETIRED = 'RETIRED';
    const PROFESSION_UNEMPLOYED = 'UNEMPLOYED';
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $socialClass;
    const SOCIAL_CLASS_LOWER = 'LOWER';
    const SOCIAL_CLASS_MIDDLE = 'MIDDLE';
    const SOCIAL_CLASS_UPPER = 'UPPER';
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $region;
    const REGION_ATTICA = 'ATTICA';
    const REGION_THESSALONIKI = 'THESSALONIKI';
    const REGION_CENTRAL_GREECE = 'CENTRAL_GREECE';
    const REGION_NORTH_AEGEAN = 'NORTH_AEGEAN';
    const REGION_CRETE = 'CRETE';
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $urbanity;
    const URBANITY_URBAN = 'URBAN';
    const URBANITY_RURAL = 'RURAL';
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $politicalView;
    const POLITICAL_VIEW_LEFT = 'LEFT';
    const POLITICAL_VIEW_CENTER_LEFT = 'CENTER_LEFT';
    const POLITICAL_VIEW_CENTER = 'CENTER';
    const POLITICAL_VIEW_CENTER_RIGHT = 'CENTER_RIGHT';
    const POLITICAL_VIEW_RIGHT = 'RIGHT';
    const POLITICAL_VIEW_UNKNOWN = 'UNKNOWN';

    function getId() {
        return $this->id;
    }

    function getSessionId() {
        return $this->sessionId;
    }

    function getEmail() {
        return $this->email;
    }

    function getGender() {
        return $this->gender;
    }

    function getAge() {
        return $this->age;
    }

    function getEducationLevel() {
        return $this->educationLevel;
    }

    function getIncome() {
        return $this->income;
    }

    function getProfession() {
        return $this->profession;
    }

    function getSocialClass() {
        return $this->socialClass;
    }

    function getRegion() {
        return $this->region;
    }

    function getUrbanity() {
        return $this->urbanity;
    }

    function getPoliticalView() {
        return $this->politicalView;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setSessionId($sessionId) {
        $this->sessionId = $sessionId;
    }

    function setEmail($email) {
        $this->email = $email;
    }

    function setGender($gender) {
        $this->gender = $gender;
    }

    function setAge($age) {
        $this->age = $age;
    }

    function setEducationLevel($educationLevel) {
        $this->educationLevel = $educationLevel;
    }

    function setIncome($income) {
        $this->income = $income;
    }

    function setProfession($profession) {
        $this->profession = $profession;
    }

    function setSocialClass($socialClass) {
        $this->socialClass = $socialClass;
    }

    function setRegion($region) {
        $this->region = $region;
    }

    function setUrbanity($urbanity) {
        $this->urbanity = $urbanity;
    }

    function setPoliticalView($politicalView) {
        $this->politicalView = $politicalView;
    }

    static function getDimensionsExpanded() {
        $oClass = new \ReflectionClass(__CLASS__);
        $constants = $oClass->getConstants();
        $result = array();
        foreach($constants as $key => $curConstant) {
            if(strpos($key, 'GENDER') !== false) { $result['gender'][] = $curConstant; }
            if(strpos($key, 'AGE') !== false) { $result['age'][] = $curConstant; }
            if(strpos($key, 'EDUCATION') !== false) { $result['educationLevel'][] = $curConstant; }
            if(strpos($key, 'INCOME') !== false) { $result['income'][] = $curConstant; }
            if(strpos($key, 'PROFESSION') !== false) { $result['profession'][] = $curConstant; }
            if(strpos($key, 'SOCIAL_CLASS') !== false) { $result['socialClass'][] = $curConstant; }
            if(strpos($key, 'REGION') !== false) { $result['region'][] = $curConstant; }
            if(strpos($key, 'URBANITY') !== false) { $result['urbanity'][] = $curConstant; }
            if(strpos($key, 'POLITICAL_VIEW') !== false) { $result['politicalView'][] = $curConstant; }
        }
        return $result;
    }
}
