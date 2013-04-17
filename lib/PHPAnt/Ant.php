<?php
namespace PHPAnt;

/**
 * Implementation of a wrapper around ant
 *
 * @author lex
 */
class Ant {

    /**
     * Default name, recognized by ant
     *
     */
    const DEFAULT_NAME = 'build.xml';

    /**
     * @var \DOMDocument
     */
    private $dom = null;

    /**
     * Main node of the xml
     *
     * @var \DOMElement
     */
    private $project = null;

    /**
     * Node, that all new nodes will be added to
     *
     * @var \DOMElement
     */
    private $currentNode = null;

    /**
     * Simple storage to share values between closures
     *
     * @var array
     */
    private $storage = array();

    /**
     * Constructor
     *
     * @param string $projectName
     * @param string $defaultTarget
     */
    public function __construct($projectName = null, $defaultTarget = null)
    {
        $this->dom = new \DOMDocument();
        $this->dom->formatOutput = true;

        $this->project = $this->dom->createElement('project');
        if (!empty($projectName)) {
            $this->project->setAttribute('name', $projectName);
        }
        if (!empty($defaultTarget)) {
            $this->project->setAttribute('default', $defaultTarget);
        }

        $this->currentNode = $this->dom->appendChild($this->project);
    }

    /**
     * Produces DOM structure for any node in ant file except 'target'
     *
     * @param $name
     * @param $arguments
     */
    public function __call($name, $arguments)
    {
        $node = $this->dom->createElement($name);

        $this->pushNode($node);
        if (isset($arguments[0])) {
            if (is_array($arguments[0])) {
                foreach ($arguments[0] as $name => $value) {
                    $node->setAttribute($name, $value);
                }
            } else if (is_callable($arguments[0])) {
                $arguments[0]($this);
            }
        }
        $this->popNode();
    }

    /**
     * Produces DOM structure for 'property' node in ant project file
     *
     * @param array $attributes
     */
    public function property(array $attributes)
    {
        $property = $this->dom->createElement('property');
        foreach ($attributes as $name => $value) {
            $property->setAttribute($name, $value);
        }

        $this->project->appendChild($property);
    }

    /**
     * Set default target
     *
     */
    public function setDefaultTarget($target)
    {
        $this->project->setAttribute('default', $target);
    }

    /**
     * Set list of attributes for current node
     *
     * @param array $attrs
     */
    public function setNodeAttrs(array $attrs)
    {
        foreach ($attrs as $name => $value) {
            $this->currentNode->setAttribute($name, $value);
        }
    }

    /**
     * Store object in local ant storage
     *
     * @param string $key
     * @param mixed $value
     */
    public function storeValue($key, $value)
    {
        $this->storage[$key] = $value;
    }

    /**
     * Get value from storage
     *
     * @param string $key
     * @return mixed
     */
    public function getValue($key)
    {
        if (!isset($this->storage[$key])) {
            return null;
        }

        return $this->storage[$key];
    }

    /**
     * Save xml to disk
     *
     * @param string $filename
     */
    public function save($filename = self::DEFAULT_NAME)
    {
        $this->dom->save($filename);
    }

    /**
     * Execute ant build file
     *
     * @param string $target - target to run, required if no default
     *                         target is specified
     * @param array $options - extra options to add to run in format
     *                         array(<key-with-minus> => <value>, ...)
     */
    public function run($target = null, $options = array())
    {
        $filename = tempnam(sys_get_temp_dir(), 'phpant-build-');
        $this->save($filename);
        $cmd = 'ant -f ' . $filename;
        foreach ($options as $key => $value) {
            $cmd .= ' ' . $key . ' ' . $value;
        }
        if ($target) {
            $cmd .= ' ' . $target;
        }
        system($cmd);

        unlink($filename) || unlink($filename);
    }

    /**
     * Add node to the current one and make it current
     *
     * @param DOMElement $node
     */
    private function pushNode(\DOMElement $node)
    {
        $this->currentNode = $this->currentNode->appendChild($node);
    }

    /**
     * Set current node to be parent of current one
     */
    private function popNode()
    {
        $this->currentNode = $this->currentNode->parentNode;
    }

}
