<?php

namespace Fousky\TreeBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;

abstract class AbstractTreeAdmin extends AbstractAdmin
{
    /** @var string */
    protected $treeTextField;

    /**
     * @param string $code
     * @param string $class
     * @param string $baseControllerName
     * @param string $treeTextField
     */
    public function __construct($code, $class, $baseControllerName, $treeTextField)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->listModes['tree'] = [
            'class' => 'fa fa-tree fa-fw',
        ];

        if (empty($treeTextField)) {
            throw new \UnexpectedValueException('It\'s required to specify \'treeTextField\' for tree view');
        }

        $this->treeTextField = $treeTextField;
    }

    /**
     * @return string
     */
    public function getTreeTextField(): string
    {
        return $this->treeTextField;
    }
}
