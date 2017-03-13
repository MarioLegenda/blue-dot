<?php

namespace Test\Model;

class Word
{
    /**
     * @var int $id
     */
    private $id;
    /**
     * @var string $word
     */
    private $word;
    /**
     * @var string $category
     */
    private $category;
    /**
     * @var string $type
     */
    private $type;
    /**
     * @var int $language
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
    public function getWord()
    {
        return $this->word;
    }
    /**
     * @param mixed $word
     */
    public function setWord($word)
    {
        $this->word = $word;
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
    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }
    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }
    /**
     * @param string $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }
}