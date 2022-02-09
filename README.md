# JS Layout Parser

Lightweight standalone PHP library that was created to make work with `$jsLayout` in Magento 2 less spaghetti, and more object-oriented.

## Installation

```
composer require pragmatic-modules/jslayout-parser
```

## Usage On Checkout

Add new layout processor by implementing `LayoutProcessorInterface`, and inject it into `layoutProcessors` array.

File: `etc/frontend/di.xml`

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
    <type name="Magento\Checkout\Block\Onepage">
        <arguments>
            <argument name="layoutProcessors" xsi:type="array">
                <item name="example_processor" xsi:type="object">Pragmatic\Example\Block\Checkout\ExampleProcessor
                </item>
            </argument>
        </arguments>
    </type>
</config>

```

Inject `Pragmatic\JsLayoutParser\Model\JsLayoutParser` into processor and parse `$jsLayout` for selected root component. 

You can find a list of available component methods below.

```php
<?php
declare(strict_types=1);

namespace Pragmatic\Example\Block\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Pragmatic\JsLayoutParser\Api\ComponentInterface;
use Pragmatic\JsLayoutParser\Model\JsLayoutParser;

class ExampleProcessor implements LayoutProcessorInterface
{
    /** @var JsLayoutParser */
    private $jsLayoutParser;

    public function __construct(JsLayoutParser $jsLayoutParser)
    {
        $this->jsLayoutParser = $jsLayoutParser;
    }

    public function process($jsLayout) : array
    {
        /** @var ComponentInterface $component */
        $component = $this->jsLayoutParser->parse($jsLayout, 'checkout');

        if ($shippingAddress = $component->getNestedChild('steps.shipping-step.shippingAddress')) {
            $shippingAddress->setComponent('Vendor_Module/js/view/shipping');
            $shippingAddress->setIsVisible(false);
        }
      
        $jsLayout['components']['checkout'] = $component->asArray();

        return $jsLayout;
    }
}
```


## JsLayoutParser Methods

### parse( array $jsLayout, string $rootComponent )

Parse `$jsLayout` into nested component objects tree.

`$rootComponent` is name of the component out of `$jsLayout['components']` that will be used as a base path for any operations.

Returns root `Component` object.

---

## Component Methods

* [asArray( )](#asarray-)
* [getComponentName( )](#getcomponentname-)
* [getParent( )](#getparent-)
* [remove( )](#remove-)
* [hasChild( string $componentName )](#haschild--string-componentname-)
* [getChild( string $componentName )](#getchild-string-componentname-)
* [addChild( ComponentInterface $component )](#addchild-componentinterface-component-)
* [removeChild ( string $componentName )](#removechild--string-componentname-)
* [hasNestedChild ( string $path, string $childSeparator = '.' )](#hasnestedchild--string-path-string-childseparator---)
* [getNestedChild ( string $componentName, string $childSeparator = '.' )](#getnestedchild--string-componentname-string-childseparator---)
* [moveNestedChild ( string $sourcePath, string $destinationPath, string $childSeparator = '.' )](#movenestedchild--string-sourcepath-string-destinationpath-string-childseparator---)
* [removeNestedChild ( string $path, string string $childSeparator = '.' )](#removenestedchild--string-path-string-string-childseparator---)
* [hasChildren ( )](#haschildren--)
* [getChildren ( )](#getchildren--)
* [isChildOf ( ComponentInterface $component )](#ischildof--componentinterface-component-)
* [getComponent ( )](#getcomponent--)
* [setComponent ( string $component )](#setcomponent--string-component-)
* [getConfig ( )](#getconfig--)
* [setConfig ( array $config, bool $replace = false )](#setconfig--array-config-bool-replace--false-)
* [getDataScope ( )](#getdatascope--)
* [setDataScope ( string $dataScope )](#setdatascope--string-datascope-)
* [getDisplayArea ( )](#getdisplayarea--)
* [setDisplayArea ( string $displayArea )](#setdisplayarea--string-displayarea-)
* [getLabel ( )](#getlabel--)
* [setLabel ( $label )](#setlabel--label-)
* [getProvider ( )](#getprovider--)
* [setProvider ( string $provider )](#setprovider--string-provider-)
* [getSortOrder ( )](#getsortorder--)
* [setSortOrder ( string $sortOrder )](#setsortorder--string-sortorder-)
* [getValidation ( )](#getvalidation--)
* [setValidation ( array $validation )](#setvalidation--array-validation-)
* [getFilterBy ( )](#getfilterby--)
* [setFilterBy ( ?array $filterBy )](#setfilterby--array-filterby-)
* [isVisible ( )](#isvisible--)
* [setIsVisible ( bool $visible )](#setisvisible--bool-visible-)
* [isRequired ( )](#isrequired--)
* [setIsRequired ( bool $required )](#setisrequired--bool-required-)

### asArray( )

Recursively converts component object and all of its nested children into a plain array that can be returned through `$jsLayout`. 

#### Example:

```php
$component = $this->jsLayoutParser->parse($jsLayout, 'checkout');

if ($shippingAddress = $component->getNestedChild('steps.shipping-step.shippingAddress')) {
    $shippingAddress->setComponent('Vendor_Module/js/view/shipping');
    $shippingAddress->setIsVisible(false);
}

$jsLayout['components']['checkout'] = $component->asArray();
```

[scroll up️](#component-methods)

---

### getComponentName( )

Get component name in layout.

Returns string.

#### Example:

```php
$checkout = $this->jsLayoutParser->parse($jsLayout, 'checkout');
$componentName = $checkout->getComponentName(); // returns 'checkout'
```

#### jsLayout equivalent:

In associative `$jsLayout` array components are stored as nested key-value pairs, where value are arrays with nested
children. It is not possible to determine parent's key within child scope. The same array might be used in multiple
places within jsLayout, so recursive searching is not an option.

In other words, you must know the name before retrieving component:

```php
$componentName = 'checkout';
$checkout = $jsLayout['components'][$componentName];
```

and once you get into child scope, there is no way to dynamically tell what's the parent key:

```php
$steps = $jsLayout['components']['checkout']['steps'];
// $steps tells you nothing about parent
```

The closest you can get to the parser behaviour is by doing:

```php
$checkout = [
    'componentName' => 'checkout', 
    'data' => $jsLayout['components']['checkout']
];
$componentName = $checkout['componentName'] // 'checkout'
```

[scroll up️](#component-methods)

---

### getParent( )

Get parent component.

Returns `ComponentInterface` if parent exists.

Returns **NULL** if parent does not exist.

#### Example:

```php
$checkout = $this->jsLayoutParser->parse($jsLayout, 'checkout');

$parent = $checkout->getParent(); // returns null

if($steps = $checkout->getChild('steps')) {
    $parent = $steps->getParent(); // returns $checkout object
}
```

#### jsLayout equivalent:

```php
$checkout = $jsLayout['components']['checkout'];
$parent = null;

if(isset($checkout['steps'])) {
    $steps = $checkout['steps'];
    $parent = $checkout;
}
```

[scroll up️](#component-methods)

---

### remove( )

Remove component with all descendants from the component tree.

This method has no return value.

#### Example:

```php
$checkout = $this->jsLayoutParser->parse($jsLayout, 'checkout');

if($steps = $checkout->getChild('steps')) {
    $steps->remove();
}
```

#### jsLayout equivalent:

```php
if(isset($jsLayout['components']['checkout']['steps'])) {
    unset($jsLayout['components']['checkout']['steps']);
}
```

[scroll up️](#component-methods)

---

### hasChild ( string $componentName )

Check if component has a child with a given name.

Returns bool.

#### Example:

```php
$checkout = $this->jsLayoutParser->parse($jsLayout, 'checkout');

if($checkout->hasChild('steps')) {
    // do something
}

if($checkout->hasChild('non-existing-child')) {
    // this won't execute
}
```

#### jsLayout equivalent:

```php
if(isset($jsLayout['component']['checkout']['children']['steps'])) {
    // do something
}

if(isset($jsLayout['component']['checkout']['children']['non-existing-child'])) {
    // this won't execute
}
```

[scroll up️](#component-methods)

---

### getChild( string $componentName )

Get component child.

Returns `ComponentInterface` if child exists.

Returns **NULL** if child does not exist.

#### Example:

```php
$checkout = $this->jsLayoutParser->parse($jsLayout, 'checkout');

if($child = $checkout->getChild('steps')) {
    // do something with child
}
```

#### jsLayout equivalent:

```php
if(isset($jsLayout['components']['checkout']['children']['steps'])) {
    $child = $jsLayout['components']['checkout']['children']['steps'];
    // do something with child
}
```

[scroll up️](#component-methods)

---

### addChild( ComponentInterface $component )

Add new component as a child of the current component object.

This method throws `Exception` if component with the same name is already a child of current component.

Returns self on success.

#### Example:

```php
$checkout = $this->jsLayoutParser->parse($jsLayout, 'checkout');

/** @var \Pragmatic\JsLayoutParser\Model\ComponentFactory */
$child = $this->componentFactory->create([
    'componentName' => 'example',
    'data' => [
        'component' => 'Magento_Ui/js/form/element/abstract',
        'label' => 'Example component',
        'provider' => 'checkoutProvider'
    ]   
]);

if(!$checkout->hasChild('example')) {
    $checkout->addChild($child);
}
```

#### jsLayout equivalent:

```php
if(!isset($jsLayout['components']['checkout']['children']['example'])) {
    $jsLayout['components']['checkout']['children']['example'] = [
        'component' => 'Magento_Ui/js/form/element/abstract',
        'label' => 'Example component',
        'provider' => 'checkoutProvider'
    ];
}
```

[scroll up️](#component-methods)

---

### removeChild ( string $componentName )

Remove child from the component.

This method throws `Exception` if child does not exist within component.

This method has no return value.

#### Example:

```php
$checkout = $this->jsLayoutParser->parse($jsLayout, 'checkout');

if($checkout->hasChild('steps')) {
    $checkout->removeChild('steps');
}
```

#### jsLayout equivalent:

```php
if(isset($jsLayout['components']['checkout']['children']['steps'])) {
    unset($jsLayout['components']['checkout']['children']['steps']);
}
```

[scroll up️](#component-methods)

---

### hasNestedChild ( string $path, string $childSeparator = '.' )

Check if component has a nested child with a given path.

By default, children are separated by a dot. This behaviour can be adjusted by passing custom separator as a second
argument.

Returns bool.

#### Example:

```php
$checkout = $this->jsLayoutParser->parse($jsLayout, 'checkout');

if($checkout->hasNestedChild('steps.shipping-step.shippingAddress')) {
    // do something
}
```

#### jsLayout equivalent:

```php
if(isset($jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress'])
) {
    // do something
}
```

[scroll up️](#component-methods)

---

### getNestedChild ( string $componentName, string $childSeparator = '.' )

Get component nested child.

By default, children are separated by a dot. This behaviour can be adjusted by passing custom separator as a second
argument.

Returns `ComponentInterface` if nested child exists.

Returns **NULL** if nested child does not exist.

#### Example:

```php
$checkout = $this->jsLayoutParser->parse($jsLayout, 'checkout');

if($shippingAddress = $checkout->getNestedChild('steps.shipping-step.shippingAddress')) {
    // do something with $shippingAddress
}
```

#### jsLayout equivalent:

```php
if(isset($jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']
)) {
    $shippingAddress = $jsLayout['components']['checkout']['children']
        ['steps']['children']
        ['shipping-step']['children']
        ['shippingAddress'];
    // do something with $shippingAddress
}
```

[scroll up️](#component-methods)

---

### moveNestedChild ( string $sourcePath, string $destinationPath, string $childSeparator = '.' )

Move nested child from source to destination.

By default, children are separated by a dot. This behaviour can be adjusted by passing custom separator as a third
argument.

This method throws `Exception` if source or destination does not exist.

This method has no return value.

#### Example:

```php
$checkout = $this->jsLayoutParser->parse($jsLayout, 'checkout');

if($checkout->hasNestedChild('steps.shipping-step.shippingAddress') && 
   $checkout->hasChild('steps')
) {
    $checkout->moveNestedChild('steps.shipping-step.shippingAddress', 'steps');
}

$checkout->hasNestedChild('steps.shipping-step.shippingAddress'); // false
$checkout->hasNestedChild('steps.shippingAddress'); // true
```

#### jsLayout equivalent:

```php
if(isset($jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']
) && isset($jsLayout['components']['checkout']['children']['steps'])
) {
    $steps = &$jsLayout['components']['checkout']['children']
    ['steps']['children'];
    $shippingAddress = $steps['shipping-step']['children']['shippingAddress'];
    unset($steps['shipping-step']['children']['shippingAddress']);
    $steps['shippingAddress'] = $shippingAddress;
}

isset($jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']
); // false

isset($jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shippingAddress']
); // true
```

[scroll up️](#component-methods)

---

### removeNestedChild ( string $path, string string $childSeparator = '.' )

Remove nested child by path.

By default, children are separated by a dot. This behaviour can be adjusted by passing custom separator as a third
argument.

This method throws `Exception` if source or destination does not exist.

This method has no return value.

#### Example:

```php
$checkout = $this->jsLayoutParser->parse($jsLayout, 'checkout');

if($checkout->hasNestedChild('steps.shipping-step.shippingAddress')) {
    $checkout->removeNestedChild('steps.shipping-step.shippingAddress');
}
$checkout->hasNestedChild('steps.shipping-step.shippingAddress'); // false
```

#### jsLayout equivalent:

```php
if(isset($jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']
)) {
    unset($steps['shipping-step']['children']['shippingAddress']);
}

isset($jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']
); // false
```

[scroll up️](#component-methods)

---

### hasChildren ( )

Check if component has children.

Returns true if at least one child exists, returns false otherwise.

#### Example:

```php
$checkout = $this->jsLayoutParser->parse($jsLayout, 'checkout');

$checkout->hasChildren() // returns true
```

#### jsLayout equivalent:

```php
(isset($jsLayout['components']['checkout']['children']) && 
count($jsLayout['components']['checkout']['children']) > 0); // returns true
```

[scroll up️](#component-methods)

---

### getChildren ( )

Get component children.

Returns array of components.

Returns empty array if component has no children.

#### Example:

```php
$checkout = $this->jsLayoutParser->parse($jsLayout, 'checkout');

$checkout->getChildren(); // returns array with 'steps' component
```

#### jsLayout equivalent:

```php
$jsLayout['components']['checkout']['children'] ?? [] // returns array with 'steps' component
```

[scroll up️](#component-methods)

---

### isChildOf ( ComponentInterface $component )

Check if component is child of given component.

#### Example:

```php
$checkout = $this->jsLayoutParser->parse($jsLayout, 'checkout');

$steps = $checkout->getChild('steps');
$steps->isChildOf($checkout); // returns true
```

#### jsLayout equivalent:

In associative `$jsLayout` array components are stored as nested key-value pairs, where value are arrays with nested
children. It is not possible to determine parent within child scope.

[scroll up️](#component-methods)

---

### getComponent ( )

Get UI Component of given component object.

#### Example:

```php
$checkout = $this->jsLayoutParser->parse($jsLayout, 'checkout');

if($shippingAddress = $checkout->getNestedChild('steps.shipping-step.shippingAddress')) {
    $component = $shippingAddress->getComponent(); // returns 'Magento_Checkout/js/view/shipping'
}
```

#### jsLayout equivalent:

```php
if(isset($jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']
)) {
    $shippingAddress = $jsLayout['components']['checkout']['children']
        ['steps']['children']
        ['shipping-step']['children']
        ['shippingAddress'];
    $component = $shippingAddress['component'] // 'Magento_Checkout/js/view/shipping'
}
```

[scroll up️](#component-methods)

---

### setComponent ( string $component )

Set UI Component of given component object.

#### Example:

```php
$checkout = $this->jsLayoutParser->parse($jsLayout, 'checkout');

if($shippingAddress = $checkout->getNestedChild('steps.shipping-step.shippingAddress')) {
    $shippingAddress->setComponent('Vendor_Module/js/view/shipping')
}
```

#### jsLayout equivalent:

```php
if(isset($jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']
)) {
    $shippingAddress = &$jsLayout['components']['checkout']['children']
        ['steps']['children']
        ['shipping-step']['children']
        ['shippingAddress'];
    $shippingAddress['component'] = 'Vendor_Module/js/view/shipping';
}
```

[scroll up️](#component-methods)

---

### getConfig ( )

Get component configuration.

Returns array.

#### Example:

```php
$checkout = $this->jsLayoutParser->parse($jsLayout, 'checkout');

if($regionId = $checkout->getNestedChild('steps.shipping-step.shippingAddress.shipping-address-fieldset.region_id')) {
    $config = $regionId->getConfig();
    /** $config is an array:
        [
            'customScope' => 'shippingAddress',
            'template' => 'ui/form/field',
            'elementTmpl' => 'ui/form/element/select',
            'customEntry' => 'shippingAddress.region',
        ]
    */
}
```

#### jsLayout equivalent:

```php
if(isset($jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']['children']
    ['shipping-address-fieldset']['children']
    ['region_id']
)) {
    $regionId = &$jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']['children']
    ['shipping-address-fieldset']['children']
    ['region_id'];

    $config = $regionId['config'];
}
```

[scroll up️](#component-methods)

---

### setConfig ( array $config, bool $replace = false )

Set component configuration.

Configurations are merged together by default. Previous values remain intact except the ones that have the same keys as `$config` array. 

`$config` values have higher priority and can be used to replace parts of existing configuration.

Entire component configuration can be overwritten by `$config` by using `$replace = true`. 

Returns self.

#### Example:

```php
$checkout = $this->jsLayoutParser->parse($jsLayout, 'checkout');

if($regionId = $checkout->getNestedChild('steps.shipping-step.shippingAddress.shipping-address-fieldset.region_id')) {
    $config = $shippingAddress->setConfig([
        'template' => 'Vendor_Module/form/field'
    ]);
}
```

#### jsLayout equivalent:

```php
if(isset($jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']['children']
    ['shipping-address-fieldset']['children']
    ['region_id']
)) {
    $regionId = &$jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']['children']
    ['shipping-address-fieldset']['children']
    ['region_id'];

    $regionId['config']['template'] = 'Vendor_Module/form/field';
}
```

[scroll up️](#component-methods)

---

### getDataScope ( )

Get component data scope.

Returns string or null.

#### Example:

```php
$checkout = $this->jsLayoutParser->parse($jsLayout, 'checkout');

if($regionId = $checkout->getNestedChild('steps.shipping-step.shippingAddress.shipping-address-fieldset.region_id')) {
    $dataScope = $regionId->getDataScope(); // returns 'shippingAddress.region_id'
}
```

#### jsLayout equivalent:

```php
if(isset($jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']['children']
    ['shipping-address-fieldset']['children']
    ['region_id']
)) {
    $dataScope = $jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']['children']
    ['shipping-address-fieldset']['children']
    ['region_id']['dataScope'] ?? null; // 'shippingAddress.region_id'
}
```

[scroll up️](#component-methods)

---

### setDataScope ( string $dataScope )

Set component data scope.

Returns self.

#### Example:

```php
$checkout = $this->jsLayoutParser->parse($jsLayout, 'checkout');

if($regionId = $checkout->getNestedChild('steps.shipping-step.shippingAddress.shipping-address-fieldset.region_id')) {
    $regionId->setDataScope('shippingAddress.some_region');
}
```

#### jsLayout equivalent:

```php
if(isset($jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']['children']
    ['shipping-address-fieldset']['children']
    ['region_id']
)) {
    $jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']['children']
    ['shipping-address-fieldset']['children']
    ['region_id']['dataScope'] = 'shippingAddress.some_region';
}
```

[scroll up️](#component-methods)

---

### getDisplayArea ( )

Get component display area.

Returns string or null.

#### Example:

```php
$checkout = $this->jsLayoutParser->parse($jsLayout, 'checkout');

if($shippingFieldset = $checkout->getNestedChild('steps.shipping-step.shippingAddress.shipping-address-fieldset')) {
    $displayArea = $shippingFieldset->getDisplayArea(); // returns 'additional-fieldsets'
}
```

#### jsLayout equivalent:

```php
if(isset($jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']['children']
    ['shipping-address-fieldset']
)) {
    $displayArea = $jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']['children']
    ['shipping-address-fieldset']['displayArea'] ?? null; // 'additional-fieldsets'
}
```

[scroll up️](#component-methods)

---

### setDisplayArea ( string $displayArea )

Set component display area.

Returns self.

#### Example:

```php
$checkout = $this->jsLayoutParser->parse($jsLayout, 'checkout');

if($shippingFieldset = $checkout->getNestedChild('steps.shipping-step.shippingAddress.shipping-address-fieldset')) {
    $shippingFieldset->setDisplayArea('summary');
}
```

#### jsLayout equivalent:

```php
if(isset($jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']['children']
    ['shipping-address-fieldset']
)) {
    $jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']['children']
    ['shipping-address-fieldset']['displayArea'] = 'summary';
}
```

[scroll up️](#component-methods)

---

### getLabel ( )

Get component label.

Returns string, `Magento\Framework\Phrase`, or null.

#### Example:

```php
$checkout = $this->jsLayoutParser->parse($jsLayout, 'checkout');

if($postcode = $checkout->getNestedChild('steps.shipping-step.shippingAddress.shipping-address-fieldset.postcode')) {
    $label = $postcode->getLabel(); // returns 'Zip/Postal Code'
}
```

#### jsLayout equivalent:

```php
if(isset($jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']['children']
    ['shipping-address-fieldset']['children']
    ['postcode']
)) {
    $label = $jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']['children']
    ['shipping-address-fieldset']['children']
    ['postcode']['label'] ?? null;  // 'Zip/Postal Code'
}
```

[scroll up️](#component-methods)

---

### setLabel ( $label )

Set component label.

`$label` should be either string, `Magento\Framework\Phrase`, or null.

Returns self.

#### Example:

```php
$checkout = $this->jsLayoutParser->parse($jsLayout, 'checkout');

if($postcode = $checkout->getNestedChild('steps.shipping-step.shippingAddress.shipping-address-fieldset.postcode')) {
    $label = $postcode->setLabel(__('ZIP'));
}
```

#### jsLayout equivalent:

```php
if(isset($jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']['children']
    ['shipping-address-fieldset']['children']
    ['postcode']
)) {
    $jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']['children']
    ['shipping-address-fieldset']['children']
    ['postcode']['label'] = __('ZIP');
}
```

[scroll up️](#component-methods)

---

### getProvider ( )

Get component provider.

Returns string or null.

#### Example:

```php
$checkout = $this->jsLayoutParser->parse($jsLayout, 'checkout');

if($postcode = $checkout->getNestedChild('steps.shipping-step.shippingAddress.shipping-address-fieldset.postcode')) {
    $provider = $postcode->getProvider(); // returns 'checkoutProvider'
}
```

#### jsLayout equivalent:

```php
if(isset($jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']['children']
    ['shipping-address-fieldset']['children']
    ['postcode']
)) {
    $provider = $jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']['children']
    ['shipping-address-fieldset']['children']
    ['postcode']['provider'] ?? null; // 'checkoutProvider'
}
```

[scroll up️](#component-methods)

---

### setProvider ( string $provider )

Set component provider.

Returns self.

#### Example:

```php
$checkout = $this->jsLayoutParser->parse($jsLayout, 'checkout');

if($postcode = $checkout->getNestedChild('steps.shipping-step.shippingAddress.shipping-address-fieldset.postcode')) {
    $postcode->setProvider('vendorProvider');
}
```

#### jsLayout equivalent:

```php
if(isset($jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']['children']
    ['shipping-address-fieldset']['children']
    ['postcode']
)) {
    $jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']['children']
    ['shipping-address-fieldset']['children']
    ['postcode']['provider'] = 'vendorProvider';
}
```

[scroll up️](#component-methods)

---

### getSortOrder ( )

Get component sort order.

Returns string, or null.

#### Example:

```php
$checkout = $this->jsLayoutParser->parse($jsLayout, 'checkout');

if($postcode = $checkout->getNestedChild('steps.shipping-step.shippingAddress.shipping-address-fieldset.postcode')) {
    $sortOrder = $postcode->getSortOrder(); // returns '110'
}
```

#### jsLayout equivalent:

```php
if(isset($jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']['children']
    ['shipping-address-fieldset']['children']
    ['postcode']
)) {
    $sortOrder = $jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']['children']
    ['shipping-address-fieldset']['children']
    ['postcode']['sortOrder'] ?? null; // '110'
}
```

[scroll up️](#component-methods)

---

### setSortOrder ( string $sortOrder )

Set component sort order.

Returns self.

#### Example:

```php
$checkout = $this->jsLayoutParser->parse($jsLayout, 'checkout');

if($postcode = $checkout->getNestedChild('steps.shipping-step.shippingAddress.shipping-address-fieldset.postcode')) {
    $postcode->setSortOrder('150');
}
```

#### jsLayout equivalent:

```php
if(isset($jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']['children']
    ['shipping-address-fieldset']['children']
    ['postcode']
)) {
    $jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']['children']
    ['shipping-address-fieldset']['children']
    ['postcode']['sortOrder'] = '150';
}
```

[scroll up️](#component-methods)

---

### getValidation ( )

Get component validation rules.

Returns array or null.

#### Example:

```php
$checkout = $this->jsLayoutParser->parse($jsLayout, 'checkout');

if($postcode = $checkout->getNestedChild('steps.shipping-step.shippingAddress.shipping-address-fieldset.postcode')) {
    $validation = $postcode->getValidation(); // returns ['required-entry' => true]
}
```

#### jsLayout equivalent:

```php
if(isset($jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']['children']
    ['shipping-address-fieldset']['children']
    ['postcode']
)) {
    $validation = $jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']['children']
    ['shipping-address-fieldset']['children']
    ['postcode']['validation'] ?? null;  // ['required-entry' => true]
}
```

[scroll up️](#component-methods)

---

### setValidation ( array $validation )

Set component validation rules.

Returns self.

#### Example:

```php
$checkout = $this->jsLayoutParser->parse($jsLayout, 'checkout');

if($postcode = $checkout->getNestedChild('steps.shipping-step.shippingAddress.shipping-address-fieldset.postcode')) {
    $postcode->setValidation([
        'required-entry' => true,
        'max_text_length' => 6
    ]);
}
```

#### jsLayout equivalent:

```php
if(isset($jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']['children']
    ['shipping-address-fieldset']['children']
    ['postcode']
)) {
    $validation = $jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']['children']
    ['shipping-address-fieldset']['children']
    ['postcode']['validation'] = [
        'required-entry' => true,
        'max_text_length' => 6
    ];
}
```

[scroll up️](#component-methods)

---

### getFilterBy ( )

Get component filter by configuration.

Returns array or null.

#### Example:

```php
$checkout = $this->jsLayoutParser->parse($jsLayout, 'checkout');

if($regionId = $checkout->getNestedChild('steps.shipping-step.shippingAddress.shipping-address-fieldset.region_id')) {
    $filterBy = $regionId->getFilterBy(); 
// returns 
//    [
//        'target' => '${ $.provider }:${ $.parentScope }.country_id',
//        'field' => 'country_id',
//    ]
}
```

#### jsLayout equivalent:

```php
if(isset($jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']['children']
    ['shipping-address-fieldset']['children']
    ['region_id']
)) {
    $filterBy = $jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']['children']
    ['shipping-address-fieldset']['children']
    ['region_id']['filterBy'] ?? null;
}
```

[scroll up️](#component-methods)

---

### setFilterBy ( ?array $filterBy )

Set component filter by configuration.

Returns self.

#### Example:

```php
$checkout = $this->jsLayoutParser->parse($jsLayout, 'checkout');

if($regionId = $checkout->getNestedChild('steps.shipping-step.shippingAddress.shipping-address-fieldset.region_id')) {
    $regionId->setFilterBy(null); 
}
```

#### jsLayout equivalent:

```php
if(isset($jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']['children']
    ['shipping-address-fieldset']['children']
    ['region_id']
)) {
    $jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']['children']
    ['shipping-address-fieldset']['children']
    ['region_id']['filterBy'] = null;
}
```

[scroll up️](#component-methods)

---

### isVisible ( )

Get component visibility.

Returns bool.

#### Example:

```php
$checkout = $this->jsLayoutParser->parse($jsLayout, 'checkout');

if($postcode = $checkout->getNestedChild('steps.shipping-step.shippingAddress.shipping-address-fieldset.postcode')) {
    $isVisible = $postcode->isVisible(); // returns true
}
```

#### jsLayout equivalent:

```php
if(isset($jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']['children']
    ['shipping-address-fieldset']['children']
    ['postcode']
)) {
    $isVisible = $jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']['children']
    ['shipping-address-fieldset']['children']
    ['postcode']['visible'] ?? false; // true
}
```

[scroll up️](#component-methods)

---

### setIsVisible ( bool $visible )

Set component visibility.

Returns self.

#### Example:

```php
$checkout = $this->jsLayoutParser->parse($jsLayout, 'checkout');

if($postcode = $checkout->getNestedChild('steps.shipping-step.shippingAddress.shipping-address-fieldset.postcode')) {
    $postcode->setIsVisible(false);
}
```

#### jsLayout equivalent:

```php
if(isset($jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']['children']
    ['shipping-address-fieldset']['children']
    ['postcode']
)) {
    $jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']['children']
    ['shipping-address-fieldset']['children']
    ['postcode']['visible'] = false;
}
```

[scroll up️](#component-methods)

---

### isRequired ( )

Get component `required` flag.

Returns bool.

#### Example:

```php
$checkout = $this->jsLayoutParser->parse($jsLayout, 'checkout');

if($street = $checkout->getNestedChild('steps.shipping-step.shippingAddress.shipping-address-fieldset.street')) {
    $isRequired = $street->isRequired(); // returns true
}
```

#### jsLayout equivalent:

```php
if(isset($jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']['children']
    ['shipping-address-fieldset']['children']
    ['street']
)) {
    $isRequired = $jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']['children']
    ['shipping-address-fieldset']['children']
    ['street']['required'] ?? false; // true
}
```

[scroll up️](#component-methods)

---

### setIsRequired ( bool $required )

Set component `required` flag.

Returns self.

#### Example:

```php
$checkout = $this->jsLayoutParser->parse($jsLayout, 'checkout');

if($street = $checkout->getNestedChild('steps.shipping-step.shippingAddress.shipping-address-fieldset.street')) {
    $street->setIsRequired(false);
}
```

#### jsLayout equivalent:

```php
if(isset($jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']['children']
    ['shipping-address-fieldset']['children']
    ['street']
)) {
    $jsLayout['components']['checkout']['children']
    ['steps']['children']
    ['shipping-step']['children']
    ['shippingAddress']['children']
    ['shipping-address-fieldset']['children']
    ['street']['required'] = false;
}
```

[scroll up️](#component-methods)

---
