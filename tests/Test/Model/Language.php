<?php

namespace Test\Model;

class Language
{
    /**
     * @var int $id;
     */
    private $id;
    /**
     * @var int $developer
     */
    private $developer;
    /**
     * @var int $workingLanguage
     */
    private $workingLanguage;
    /**
     * @var string $language
     */
    private $language;
    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
    /**
     * @return mixed
     */
    public function getDeveloper()
    {
        return $this->developer;
    }
    /**
     * @param mixed $developer
     */
    public function setDeveloper($developer)
    {
        $this->developer = $developer;
    }
    /**
     * @return mixed
     */
    public function getWorkingLanguage()
    {
        return $this->workingLanguage;
    }
    /**
     * @param mixed $workingLanguage
     */
    public function setWorkingLanguage($workingLanguage)
    {
        $this->workingLanguage = $workingLanguage;
    }
    /**
     * @return mixed
     */
    public function getLanguage()
    {
        return $this->language;
    }
    /**
     * @param mixed $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }
}