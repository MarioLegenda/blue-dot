<?php

namespace BlueDot\Cache\Xml;

use BlueDot\Cache\Contracts\CacheInterface;
use BlueDot\Entity\Entity;
use BlueDot\Exception\CacheException;

class XmlCache implements CacheInterface
{
    /**
     * @var \DOMDocument
     */
    private $dom;
    /**
     * @var string $cacheFile
     */
    private $cacheFile;
    /**
     * CacheManipulator constructor.
     * @param \DomDocument $dom
     * @param string $cacheFile
     * @throws CacheException
     */
    public function __construct(\DOMDocument $dom, string $cacheFile)
    {
        $this->dom = $dom;
        $this->cacheFile = $cacheFile;
    }
    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name) : bool
    {
        $element = $this->dom->getElementById($name);

        return $element instanceof \DOMElement;
    }
    /**
     * @param string $name
     * @return null|string
     */
    public function get(string $name)
    {
        if (!$this->has($name)) {
            return null;
        }

        $element = $this->dom->getElementById($name);

        $unserialized = unserialize(trim($element->nodeValue));

        return $unserialized;
    }
    /**
     * @param string $name
     * @param string $value
     * @throws CacheException
     */
    public function put(string $name, string $value)
    {
        if ($this->has($name)) {
            throw new CacheException(
                sprintf(
                    'Invalid cache entry. Cache already contains an entry with name %s. This is probably a bug. Please, contact whitepostmail@gmail.com or post an issue on Github',
                    $name
                )
            );
        }

        $element = $this->dom->createElement('entry', $value);
        $element->setAttribute('id', $name);

        $this->dom->documentElement->appendChild($element);

        $this->dom->saveXML();

        $this->dom->save($this->cacheFile);
    }
    /**
     * @param string $name
     * @return bool
     */
    public function remove(string $name) : bool
    {
        if ($this->has($name)) {
            $element = $this->dom->getElementById($name);

            $this->dom->removeChild($element);

            $this->dom->saveXML();

            $this->dom->save($this->cacheFile);

            return true;
        }

        return false;
    }


}