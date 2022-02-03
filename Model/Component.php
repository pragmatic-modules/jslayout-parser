<?php
declare(strict_types=1);

namespace Pragmatic\JsLayoutParser\Model;

use Pragmatic\JsLayoutParser\Api\ComponentInterface;
use Pragmatic\JsLayoutParser\Model;

class Component implements ComponentInterface
{
    /** @var string */
    private $componentName;

    /** @var array */
    private $data = [];

    /** @var ComponentInterface|null */
    private $parent;

    /**
     * @param string $componentName
     * @param array $data
     * @param ComponentInterface|null $parent
     */
    public function __construct(
        string $componentName,
        array $data,
        ?ComponentInterface $parent = null
    ) {
        $this->data = $data;
        $this->componentName = $componentName;
        $this->parent = $parent;
        $this->build();
    }

    protected function build()
    {
        foreach ($this->data['children'] ?? [] as $componentName => $data) {
            $this->data['children'][$componentName] = new Component(
                (string)$componentName,
                $data,
                $this
            );
        }
    }

    /**
     * @param string $key
     * @param $value
     * @return ComponentInterface
     */
    protected function setData(string $key, $value): ComponentInterface
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     * @param $default
     * @return mixed|null
     */
    protected function getData(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * @param ComponentInterface $component
     * @return ComponentInterface
     */
    protected function setParent(ComponentInterface $component): ComponentInterface
    {
        $this->parent = $component;

        return $this;
    }

    public function getComponentName(): string
    {
        return $this->componentName;
    }

    public function getParent(): ?ComponentInterface
    {
        return $this->parent;
    }

    public function remove(): void
    {
        if ($parent = $this->getParent()) {
            $parent->removeChild($this->getComponentName());
        }
    }

    public function hasChild(string $componentName): bool
    {
        return isset($this->data['children'][$componentName]);
    }

    public function getChild(string $componentName): ?ComponentInterface
    {
        return $this->data['children'][$componentName] ?? null;
    }

    public function addChild(ComponentInterface $component): ComponentInterface
    {
        $componentName = $component->getComponentName();

        if ($this->hasChild($componentName)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    '%1 component already have %2 as a child',
                    $componentName,
                    $this->getComponentName())
            );
        }

        $children = $this->data['children'] ?? [];
        $children[$componentName] = $component;
        $this->data['children'] = $children;

        $component->setParent($this);

        return $this;
    }

    public function removeChild(string $componentName): ComponentInterface
    {
        if (!$this->hasChild($componentName)) {
            throw new \Exception(
                sprintf('%s component does not exist in %s', $componentName, $this->getComponentName())
            );
        }

        unset($this->data['children'][$componentName]);

        return $this;
    }

    public function hasNestedChild(string $path, string $childSeparator = '.'): bool
    {
        $componentNames = explode($childSeparator, $path);
        $component = $this;

        foreach ($componentNames as $componentName) {
            if (!$component->hasChild($componentName)) {
                return false;
            }
            $component = $component->getChild($componentName);
        }

        return true;
    }

    public function getNestedChild(string $path, string $childSeparator = '.'): ?ComponentInterface
    {
        $componentNames = explode($childSeparator, $path);
        $component = $this;

        foreach ($componentNames as $componentName) {
            $component = $component->getChild($componentName);
            if ($component === null) {
                return null;
            }
        }

        return $component;
    }

    public function moveNestedChild(
        string $sourcePath,
        string $destinationPath,
        string $childSeparator = '.'
    ): void {
        $source = $this->getNestedChild($sourcePath);
        $destination = $this->getNestedChild($destinationPath);

        if (!$source || !$destination) {
            throw new \Exception(
                'Moving child failed. source or destination does not exist'
            );
        }

        $source->getParent()->removeChild($source->getComponentName());
        $destination->addChild($source);
    }

    public function removeNestedChild(string $path, string $childSeparator = '.'): void
    {
        $component = $this->getNestedChild($path);
        if (!$component) {
            throw new \Exception(
                'Removing nested child failed. Child does not exist.'
            );
        }

        $component->getParent()->removeChild($component->getComponentName());
    }

    public function hasChildren(): bool
    {
        return isset($this->data['children']) && count($this->data['children']) > 0;
    }

    public function getChildren(): array
    {
        return $this->data['children'] ?? [];
    }

    public function isChildOf(ComponentInterface $component): bool
    {
        return $this->getParent() === $component;
    }

    public function getComponent(): ?string
    {
        return $this->getData('component');
    }

    public function setComponent(string $component): ComponentInterface
    {
        return $this->setData('component', $component);
    }

    public function getConfig(): array
    {
        return $this->getData('config', []);
    }

    public function setConfig(array $config, bool $replace = false): ComponentInterface
    {
        return $this->setData(
            'config',
            $replace ? $config : array_merge($this->getConfig(), $config)
        );
    }

    public function getDataScope(): ?string
    {
        return $this->getData('dataScope');
    }

    public function setDataScope(string $dataScope): ComponentInterface
    {
        return $this->setData('dataScope', $dataScope);
    }

    public function getDisplayArea(): ?string
    {
        return $this->getData('displayArea');
    }

    public function setDisplayArea(string $displayArea): ComponentInterface
    {
        return $this->setData('displayArea', $displayArea);
    }

    public function getLabel()
    {
        return $this->getData('label');
    }

    public function setLabel($label): ComponentInterface
    {
        return $this->setData('label', $label);
    }

    public function getProvider(): ?string
    {
        return $this->getData('provider');
    }

    public function setProvider(string $provider): ComponentInterface
    {
        return $this->setData('provider', $provider);
    }

    public function getSortOrder(): ?string
    {
        return $this->getData('sortOrder');
    }

    public function setSortOrder(string $sortOrder): ComponentInterface
    {
        return $this->setData('sortOrder', $sortOrder);
    }

    public function getValidation(): ?array
    {
        return $this->getData('validation');
    }

    public function setValidation(?array $validation): ComponentInterface
    {
        return $this->setData('validation', $validation);
    }

    public function getFilterBy(): ?array
    {
        return $this->getData('filterBy');
    }

    public function setFilterBy(?array $filterBy = null): ComponentInterface
    {
        return $this->setData('filterBy', $filterBy);
    }

    public function isVisible(): bool
    {
        return (bool)$this->getData('visible', false);
    }

    public function setIsVisible(bool $visible): ComponentInterface
    {
        return $this->setData('visible', $visible);
    }

    public function isRequired(): bool
    {
        return (bool)$this->getData('required', false);
    }

    public function setIsRequired(bool $required): ComponentInterface
    {
        return $this->setData('required', $required);
    }

    public function asArray(): array
    {
        if (!isset($this->data['children']) || empty($this->data['children'])) {
            return $this->data;
        }

        $children = [];

        /** @var ComponentInterface $child */
        foreach ($this->data['children'] ?? [] as $componentName => $child) {
            $children[$componentName] = $child->asArray();
        }

        return array_merge($this->data, ['children' => $children]);
    }
}
