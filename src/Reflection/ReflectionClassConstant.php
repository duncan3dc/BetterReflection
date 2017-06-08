<?php

namespace Roave\BetterReflection\Reflection;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use Roave\BetterReflection\NodeCompiler\CompileNodeToValue;
use Roave\BetterReflection\NodeCompiler\CompilerContext;
use Roave\BetterReflection\Reflector\Reflector;

class ReflectionClassConstant implements \Reflector
{

    /**
     * @var bool value of const was cached?
     */
    private $valueCached = false;

    /**
     * @var mixed const value
     */
    private $value;

    /**
     * @var
     */
    private $reflector;
    /**
     * @var ReflectionClass Constant owner
     */
    private $owner;

    /**
     * @var ClassConst
     */
    private $node;

    private function __construct()
    {
    }

    /**
     * Create a reflection of a class's constant by Const Node
     *
     * @param Reflector $reflector
     * @param ClassConst $node
     * @param ReflectionClass $owner
     * @return ReflectionClassConstant
     */
    public static function createFromNode(
        Reflector $reflector,
        ClassConst $node,
        ReflectionClass $owner
    ) : self {
        $ref = new self();
        $ref->node = $node;
        $ref->owner = $owner;
        $ref->reflector = $reflector;
        return $ref;
    }

    /**
     * Get the name of the reflection (e.g. if this is a ReflectionClass this
     * will be the class name).
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->node->consts[0]->name;
    }

    /**
     * Returns constant value
     *
     * @return mixed
     */
    public function getValue()
    {
        if (false === $this->valueCached) {
            $this->value = (new CompileNodeToValue())->__invoke(
                $this->node->consts[0]->value,
                new CompilerContext($this->reflector, $this->getDeclaringClass())
            );
        }

        return $this->value;
    }

    /**
     * Constant is public
     *
     * @return bool
     */
    public function isPublic() : bool
    {
        return (bool)($this->getModifiers() & Class_::MODIFIER_PUBLIC);
    }

    /**
     * Cosnstant is private
     *
     * @return bool
     */
    public function isPrivate() : bool
    {
        return (bool)($this->getModifiers() & Class_::MODIFIER_PRIVATE);
    }

    /**
     * Constant is protected
     *
     * @return bool
     */
    public function isProtected() : bool
    {
        return (bool)($this->getModifiers() & Class_::MODIFIER_PROTECTED);
    }

    /**
     * Returns a bitfield of the access modifiers for this constant
     *
     * @return int
     */
    public function getModifiers() : int
    {
        return $this->node->flags === 0 ? Class_::MODIFIER_PUBLIC : $this->node->flags;
    }

    /**
     * Get the declaring class
     *
     * @return ReflectionClass
     */
    public function getDeclaringClass() : ReflectionClass
    {
        return $this->owner;
    }

    /**
     * Returns the doc comment for this constant
     *
     * @return string
     */
    public function getDocComment() : string
    {
        if (!$this->node->hasAttribute('comments')) {
            return '';
        }

        /* @var \PhpParser\Comment\Doc $comment */
        $comment = $this->node->getAttribute('comments')[0];
        return $comment->getReformattedText();
    }

    private function getVisibility()
    {
        if ($this->isPublic()) {
            return 'public';
        }
        if ($this->isPrivate()) {
            return 'private';
        }
        if ($this->isProtected()) {
            return 'protected';
        }
        return '';
    }

    /**
     * To string
     *
     * @link http://php.net/manual/en/reflector.tostring.php
     * @return string
     * @since 5.0
     */
    public function __toString()
    {
        return sprintf(
            'Constant [ %s %s ] { %s }' . PHP_EOL,
            $this->getVisibility(),
            $this->getName(),
            $this->getValue()
        );
    }

    /**
     * Exports
     *
     * @link http://php.net/manual/en/reflector.export.php
     * @return string
     * @since 5.0
     */
    public static function export()
    {
        throw new \Exception('Unable to export statically');
    }
}
