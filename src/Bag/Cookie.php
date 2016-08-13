<?php
/**
 *
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */

namespace FastD\Http\Bag;

use Serializable;

/**
 * Class Cookie
 *
 * @package FastD\Http\Bag
 */
class Cookie implements Serializable
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var string
     */
    protected $domain;

    /**
     * Default time() + $expire.
     *
     * @var int
     */
    protected $expire;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var bool
     */
    protected $secure;

    /**
     * @var bool
     */
    protected $httpOnly;

    /**
     * @param        $name
     * @param null $value
     * @param int $expire
     * @param string $path
     * @param null $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @param bool $force
     */
    public function __construct($name, $value = null, $expire = null, $path = null, $domain = null, $secure = null, $httpOnly = null, $force = true)
    {
        // from PHP source code
        if (preg_match("/[=,; \t\r\n\013\014]/", $name)) {
            throw new \InvalidArgumentException(sprintf('The cookie name "%s" contains invalid characters.', $name));
        }

        $this->name = $name;
        $this->value = $value;
        $this->domain = $domain;
        $this->expire = $expire;
        $this->path = $path;
        $this->secure = $secure;
        $this->httpOnly = $httpOnly;

        if ($force) {
            setcookie($this->getName(), $this->serialize(), empty($this->getExpire()) ? null : time() + $this->getExpire(), $this->getPath(), $this->getDomain(), $this->isSecure(), $this->isHttpOnly());
        } else {
            $this->unserialize($value);
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param null $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param null $domain
     * @return $this
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * @return int
     */
    public function getExpire()
    {
        return $this->expire;
    }

    /**
     * @param int $expire
     * @return $this
     */
    public function setExpire($expire)
    {
        $this->expire = $expire;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isSecure()
    {
        return $this->secure;
    }

    /**
     * @param boolean $secure
     * @return $this
     */
    public function setSecure($secure)
    {
        $this->secure = $secure;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isHttpOnly()
    {
        return $this->httpOnly;
    }

    /**
     * @param boolean $httpOnly
     * @return $this
     */
    public function setHttpOnly($httpOnly)
    {
        $this->httpOnly = $httpOnly;

        return $this;
    }

    /**
     * @return string
     */
    public function asString()
    {
        $str = urlencode($this->getName()) . '=';

        if ('' === (string) $this->getValue()) {
            $str .= 'deleted; expires=' . gmdate("D, d-M-Y H:i:s T", time() - 31536001);
        } else {
            $str .= urlencode($this->getValue());

            if ($this->getExpire() !== 0) {
                $str .= '; expires=' . gmdate("D, d-M-Y H:i:s T", $this->getExpire());
            }
        }

        if ($this->path) {
            $str .= '; path=' . $this->path;
        }

        if ($this->getDomain()) {
            $str .= '; domain=' . $this->getDomain();
        }

        if (true === $this->isSecure()) {
            $str .= '; secure';
        }

        if (true === $this->isHttpOnly()) {
            $str .= '; httponly';
        }

        return $str;
    }

    /**
     * Returns the cookie's value.
     *
     * @return string The cookie value
     */
    public function __toString()
    {
        return (string)$this->value;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     *
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize([
            'name' => $this->name,
            'value' => $this->value,
            'expire' => $this->expire,
            'path' => $this->path,
            'domain' => $this->domain,
            'secure' => $this->secure,
            'httpOnly' => $this->httpOnly,
        ]);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object
     *
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     *                           The string representation of the object.
     *                           </p>
     * @return void
     */
    public function unserialize($serialized)
    {
        if (null !== $serialized) {
            try {
                if (false !== ($data = @unserialize($serialized))) {
                    foreach ($data as $name => $value) {
                        $this->$name = $value;
                    }
                }
            } catch (\Exception $e) {
                $this->value = $serialized;
            }
        }
        unset($serialized, $data);
    }
}