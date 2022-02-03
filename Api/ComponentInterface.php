<?php
declare(strict_types=1);

namespace Pragmatic\JsLayoutParser\Api;

interface ComponentInterface
{
    /**
     * Get component name in layout.
     *
     * @return string
     */
    public function getComponentName(): string;

    /**
     * Get parent component.
     *
     * @return ComponentInterface|null
     */
    public function getParent(): ?ComponentInterface;

    /**
     * Remove component with all descendants from the component tree.
     */
    public function remove(): void;

    /**
     * Check if component has a child with a given name.
     *
     * @param string $componentName
     * @return bool
     */
    public function hasChild(string $componentName): bool;

    /**
     * Get component child.
     *
     * @param string $componentName
     * @return ComponentInterface|null
     */
    public function getChild(string $componentName): ?ComponentInterface;

    /**
     * Add new component as a child of the current component object.
     *
     * @param ComponentInterface $component
     * @return ComponentInterface
     * @throws \Exception
     */
    public function addChild(ComponentInterface $component): ComponentInterface;

    /**
     * Remove child from the component.
     *
     * @param string $componentName
     * @return ComponentInterface
     * @throws \Exception
     */
    public function removeChild(string $componentName): ComponentInterface;

    /**
     * Check if component has a nested child with a given path.
     *
     * By default, children are separated by a dot.
     * This behaviour can be adjusted by passing custom separator as a second argument.
     *
     * @param string $path
     * @param string $childSeparator
     * @return bool
     */
    public function hasNestedChild(string $path, string $childSeparator = '.'): bool;

    /**
     * Get component nested child.
     *
     * By default, children are separated by a dot.
     * This behaviour can be adjusted by passing custom separator as a second argument.
     *
     * @param string $path
     * @param string $childSeparator
     * @return ComponentInterface|null
     */
    public function getNestedChild(string $path, string $childSeparator = '.'): ?ComponentInterface;

    /**
     * Move nested child from source to destination.
     *
     * By default, children are separated by a dot.
     * This behaviour can be adjusted by passing custom separator as a third argument.
     *
     * @param string $sourcePath
     * @param string $destinationPath
     * @param string $childSeparator
     * @throws \Exception
     */
    public function moveNestedChild(string $sourcePath, string $destinationPath, string $childSeparator = '.'): void;

    /**
     * @param string $path
     * @param string $childSeparator
     * @throws \Exception
     */
    public function removeNestedChild(string $path, string $childSeparator = '.'): void;

    /**
     * @return bool
     */
    public function hasChildren(): bool;

    /**
     * @return ComponentInterface[]
     */
    public function getChildren(): array;

    /**
     * @param ComponentInterface $component
     * @return mixed
     */
    public function isChildOf(ComponentInterface $component);

    /**
     * @return string|null
     */
    public function getComponent(): ?string;

    /**
     * @param string $component
     * @return ComponentInterface
     */
    public function setComponent(string $component): ComponentInterface;

    /**
     * @return array
     */
    public function getConfig(): array;

    /**
     * @param array $config
     * @param bool $replace
     * @return ComponentInterface
     */
    public function setConfig(array $config, bool $replace = false): ComponentInterface;

    /**
     * @return string|null
     */
    public function getDataScope(): ?string;

    /**
     * @param string $dataScope
     * @return ComponentInterface
     */
    public function setDataScope(string $dataScope): ComponentInterface;

    /**
     * @return string|null
     */
    public function getDisplayArea(): ?string;

    /**
     * @param string $displayArea
     * @return ComponentInterface
     */
    public function setDisplayArea(string $displayArea): ComponentInterface;

    /**
     * @return mixed
     */
    public function getLabel();

    /**
     * @param $label
     * @return ComponentInterface
     */
    public function setLabel($label): ComponentInterface;

    /**
     * @return string|null
     */
    public function getProvider(): ?string;

    /**
     * @param string $provider
     * @return ComponentInterface
     */
    public function setProvider(string $provider): ComponentInterface;

    /**
     * @return string|null
     */
    public function getSortOrder(): ?string;

    /**
     * @param string $sortOrder
     * @return ComponentInterface
     */
    public function setSortOrder(string $sortOrder): ComponentInterface;

    /**
     * @return array
     */
    public function getValidation(): ?array;

    /**
     * @param array $validation
     * @return ComponentInterface
     */
    public function setValidation(?array $validation): ComponentInterface;

    /**
     * @return array|null
     */
    public function getFilterBy(): ?array;

    /**
     * @param array|null $filterBy
     * @return ComponentInterface
     */
    public function setFilterBy(?array $filterBy = null): ComponentInterface;

    /**
     * @return bool
     */
    public function isVisible(): bool;

    /**
     * @param bool $visible
     * @return ComponentInterface
     */
    public function setIsVisible(bool $visible): ComponentInterface;

    /**
     * @return bool
     */
    public function isRequired(): bool;

    /**
     * @param bool $required
     * @return ComponentInterface
     */
    public function setIsRequired(bool $required): ComponentInterface;

    /**
     * Recursively converts component object and all of its nested children
     * into a plain array that can be returned through jsLayout
     *
     * @return array
     */
    public function asArray(): array;
}
