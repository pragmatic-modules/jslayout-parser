<?php
declare(strict_types=1);

namespace Pragmatic\JsLayoutParser\Test;

use PHPUnit\Framework\TestCase;
use Pragmatic\JsLayoutParser\Api\ComponentInterface;
use Pragmatic\JsLayoutParser\Model\Component;

class ComponentTest extends TestCase
{
    protected function createComponent(string $componentName, array $data, ?ComponentInterface $parent = null) : ComponentInterface
    {
        return new Component($componentName, $data, $parent);
    }

    protected function createOnepageCheckoutComponent(): ComponentInterface
    {
        return $this->createComponent(
            'checkout',
            $this->getOnepageCheckoutJsLayout()['components']['checkout']
        );
    }

    public function testParsingAndConvertingToArray()
    {
        $component = $this->createOnepageCheckoutComponent();
        $this->assertInstanceOf(Component::class, $component);
        $checkout = $component->asArray();
        $this->assertIsArray($checkout);
        $this->assertEquals($this->getOnepageCheckoutJsLayout()['components']['checkout'], $checkout);
    }

    public function testDataScope()
    {
        $component = $this->createOnepageCheckoutComponent();
        $postcode = $component->getNestedChild('steps.shipping-step.shippingAddress.shipping-address-fieldset.postcode');

        $this->assertEquals('shippingAddress.postcode', $postcode->getDataScope());

        $postcode->setDataScope('shippingAddress.test_postcode');
        $checkout = $component->asArray();

        $postcode = $checkout['children']
        ['steps']['children']
        ['shipping-step']['children']
        ['shippingAddress']['children']
        ['shipping-address-fieldset']['children']
        ['postcode'];

        $this->assertEquals('shippingAddress.test_postcode', $postcode['dataScope']);
    }

    public function testSortOrder()
    {
        $component = $this->createOnepageCheckoutComponent();
        $countryId = $component->getNestedChild('steps.shipping-step.shippingAddress.shipping-address-fieldset.country_id');

        $this->assertEquals('80', $countryId->getSortOrder());

        $countryId->setSortOrder('95');
        $checkout = $component->asArray();

        $countryId = $checkout['children']
        ['steps']['children']
        ['shipping-step']['children']
        ['shippingAddress']['children']
        ['shipping-address-fieldset']['children']
        ['country_id'];

        $this->assertEquals('95', $countryId['sortOrder']);
    }

    public function testVisible()
    {
        $component = $this->createOnepageCheckoutComponent();
        $regionId = $component->getNestedChild('steps.shipping-step.shippingAddress.shipping-address-fieldset.region_id');

        $this->assertEquals(true, $regionId->isVisible());

        $regionId->setIsVisible(false);
        $checkout = $component->asArray();

        $regionId = $checkout['children']
        ['steps']['children']
        ['shipping-step']['children']
        ['shippingAddress']['children']
        ['shipping-address-fieldset']['children']
        ['region_id'];

        $this->assertEquals(false, $regionId['visible']);
    }

    public function testLabel()
    {
        $component = $this->createOnepageCheckoutComponent();
        $telephone = $component->getNestedChild('steps.shipping-step.shippingAddress.shipping-address-fieldset.telephone');

        $this->assertEquals('Phone Number', $telephone->getLabel());

        $telephone->setLabel('Test Label');
        $checkout = $component->asArray();

        $telephone = $checkout['children']
        ['steps']['children']
        ['shipping-step']['children']
        ['shippingAddress']['children']
        ['shipping-address-fieldset']['children']
        ['telephone'];

        $this->assertEquals('Test Label', $telephone['label']);
    }

    public function testFilterBy()
    {
        $component = $this->createOnepageCheckoutComponent();
        $regionId = $component->getNestedChild('steps.shipping-step.shippingAddress.shipping-address-fieldset.region_id');

        $this->assertEquals([
            'target' => '${ $.provider }:${ $.parentScope }.country_id',
            'field' => 'country_id',
        ], $regionId->getFilterBy());

        $regionId->setFilterBy(null);
        $checkout = $component->asArray();

        $regionId = $checkout['children']
        ['steps']['children']
        ['shipping-step']['children']
        ['shippingAddress']['children']
        ['shipping-address-fieldset']['children']
        ['region_id'];

        $this->assertNull($regionId['filterBy']);
    }

    public function testComponent()
    {
        $component = $this->createOnepageCheckoutComponent();
        $company = $component->getNestedChild('steps.shipping-step.shippingAddress.shipping-address-fieldset.company');

        $this->assertEquals('Magento_Ui/js/form/element/abstract', $company->getComponent());

        $company->setComponent('Vendor/Module/js/test-component');
        $checkout = $component->asArray();

        $company = $checkout['children']
        ['steps']['children']
        ['shipping-step']['children']
        ['shippingAddress']['children']
        ['shipping-address-fieldset']['children']
        ['company'];

        $this->assertEquals('Vendor/Module/js/test-component', $company['component']);
    }

    public function testConfig()
    {
        $component = $this->createOnepageCheckoutComponent();
        $regionId = $component->getNestedChild('steps.shipping-step.shippingAddress.shipping-address-fieldset.region_id');

        $this->assertIsArray($regionId->getConfig());
        $this->assertEquals([
            'customScope' => 'shippingAddress',
            'template' => 'ui/form/field',
            'elementTmpl' => 'ui/form/element/select',
            'customEntry' => 'shippingAddress.region',
        ], $regionId->getConfig());

        $regionId->setConfig([
                'customScope' => 'shippingAddress_test',
                'template' => 'ui/form/field_test',
                'elementTmpl' => 'ui/form/element/select_test',
                'customEntry' => 'shippingAddress.region_test'
        ]);
        $checkout = $component->asArray();

        $regionId = $checkout['children']
        ['steps']['children']
        ['shipping-step']['children']
        ['shippingAddress']['children']
        ['shipping-address-fieldset']['children']
        ['region_id'];

        $this->assertEquals([
            'customScope' => 'shippingAddress_test',
            'template' => 'ui/form/field_test',
            'elementTmpl' => 'ui/form/element/select_test',
            'customEntry' => 'shippingAddress.region_test'
        ], $regionId['config']);
    }

    public function testDisplayArea()
    {
        $component = $this->createOnepageCheckoutComponent();
        $shippingFieldset = $component->getNestedChild('steps.shipping-step.shippingAddress.shipping-address-fieldset');

        $this->assertEquals('additional-fieldsets', $shippingFieldset->getDisplayArea());

        $shippingFieldset->setDisplayArea('test-display-area');
        $checkout = $component->asArray();

        $shippingFieldset = $checkout['children']
        ['steps']['children']
        ['shipping-step']['children']
        ['shippingAddress']['children']
        ['shipping-address-fieldset'];

        $this->assertEquals('test-display-area', $shippingFieldset['displayArea']);
    }

    public function testValidation()
    {
        $component = $this->createOnepageCheckoutComponent();
        $regionId = $component->getNestedChild('steps.shipping-step.shippingAddress.shipping-address-fieldset.region_id');

        $this->assertIsArray($regionId->getValidation());
        $this->assertEquals([
            'required-entry' => true,
        ], $regionId->getValidation());

        $regionId->setValidation(null);
        $checkout = $component->asArray();

        $regionId = $checkout['children']
        ['steps']['children']
        ['shipping-step']['children']
        ['shippingAddress']['children']
        ['shipping-address-fieldset']['children']
        ['region_id'];

        $this->assertNull($regionId['validation']);
    }

    public function testProvider()
    {
        $component = $this->createOnepageCheckoutComponent();
        $shippingAddress = $component->getNestedChild('steps.shipping-step.shippingAddress');

        $this->assertEquals('checkoutProvider', $shippingAddress->getProvider());

        $shippingAddress->setProvider('testProvider');

        $checkout = $component->asArray();

        $shippingAddress = $checkout['children']
        ['steps']['children']
        ['shipping-step']['children']
        ['shippingAddress'];


        $this->assertEquals('testProvider', $shippingAddress['provider']);
    }

    public function testChildAndNestedChildOperations()
    {
        $component = $this->createOnepageCheckoutComponent();
        $jsLayout = $this->getOnepageCheckoutJsLayout();
        $shippingFieldset = $component->getNestedChild('steps.shipping-step.shippingAddress.shipping-address-fieldset');

        $this->assertEquals(true, $shippingFieldset->hasChild('company'));

        $companyArray = $shippingFieldset->getChild('company')->asArray();
        $shippingFieldset->removeChild('company');

        $this->assertEquals(false, $shippingFieldset->hasChild('company'));

        $shippingFieldset->addChild(
            $this->createComponent('company', $companyArray)
        );

        $this->assertEquals(true, $shippingFieldset->hasChild('company'));

        $this->assertEquals(true, $component->hasNestedChild('steps.shipping-step.shippingAddress'));
        $this->assertEquals(true, $component->hasChild('steps'));

        $shippingAddress = $component->getNestedChild('steps.shipping-step.shippingAddress');

        $this->assertCount(
            count($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']),
            $shippingAddress->getChildren()
        );

        $component->moveNestedChild('steps.shipping-step.shippingAddress', 'steps');
        $steps = $component->getChild('steps');

        $this->assertEquals(true, $shippingAddress->isChildOf($steps));
        $this->assertEquals(false, $component->hasNestedChild('steps.shipping-step.shippingAddress'));
        $this->assertEquals(true, $component->hasNestedChild('steps.shippingAddress'));
        $this->assertEquals(true, $component->hasChild('steps'));
        $this->assertEquals(true, $steps->hasChild('shippingAddress'));
        $this->assertEquals(true, $steps->hasChild($shippingAddress->getComponentName()));
        $this->assertEquals($steps, $shippingAddress->getParent());

        $component->removeNestedChild('steps.shippingAddress');
        $this->assertEquals(false, $component->hasNestedChild('steps.shippingAddress'));

        $component->removeChild('steps');
        $this->assertEquals(false, $component->hasChild('steps'));
    }

    protected function getOnepageCheckoutJsLayout(): array
    {
        return [
            'components' => [
                'checkout' => [
                    'children' => [
                        'steps' => [
                            'children' => [
                                'shipping-step' => [
                                    'component' => 'uiComponent',
                                    'sortOrder' => '1',
                                    'children' => [
                                        'step-config' => [
                                            'component' => 'uiComponent',
                                            'children' => [
                                                'shipping-rates-validation' => [
                                                    'children' => [
                                                        'flatrate-rates-validation' => [
                                                            'component' => 'Magento_OfflineShipping/js/view/shipping-rates-validation/flatrate',
                                                        ],
                                                        'tablerate-rates-validation' => [
                                                            'component' => 'Magento_OfflineShipping/js/view/shipping-rates-validation/tablerate',
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                        'shippingAddress' => [
                                            'config' => [
                                                'deps' => [
                                                    0 => 'checkout.steps.shipping-step.step-config',
                                                    1 => 'checkoutProvider',
                                                ],
                                                'popUpForm' => [
                                                    'element' => '#opc-new-shipping-address',
                                                    'options' => [
                                                        'type' => 'popup',
                                                        'responsive' => true,
                                                        'innerScroll' => true,
                                                        'title' => 'Shipping Address',
                                                        'trigger' => 'opc-new-shipping-address',
                                                        'buttons' => [
                                                            'save' => [
                                                                'text' => 'Ship Here',
                                                                'class' => 'action primary action-save-address',
                                                            ],
                                                            'cancel' => [
                                                                'text' => 'Cancel',
                                                                'class' => 'action secondary action-hide-popup',
                                                            ],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                            'component' => 'Amazon_Payment/js/view/shipping',
                                            'provider' => 'checkoutProvider',
                                            'sortOrder' => '10',
                                            'children' => [
                                                'shipping-address-fieldset' => [
                                                    'component' => 'uiComponent',
                                                    'config' => [
                                                        'deps' => [
                                                            0 => 'checkoutProvider',
                                                        ],
                                                    ],
                                                    'displayArea' => 'additional-fieldsets',
                                                    'children' => [
                                                        'region' => [
                                                            'visible' => false,
                                                        ],
                                                        'region_id' => [
                                                            'component' => 'Magento_Ui/js/form/element/region',
                                                            'config' => [
                                                                'customScope' => 'shippingAddress',
                                                                'template' => 'ui/form/field',
                                                                'elementTmpl' => 'ui/form/element/select',
                                                                'customEntry' => 'shippingAddress.region',
                                                            ],
                                                            'dataScope' => 'shippingAddress.region_id',
                                                            'label' => 'State/Province',
                                                            'provider' => 'checkoutProvider',
                                                            'sortOrder' => '90',
                                                            'validation' => [
                                                                'required-entry' => true,
                                                            ],
                                                            'filterBy' => [
                                                                'target' => '${ $.provider }:${ $.parentScope }.country_id',
                                                                'field' => 'country_id',
                                                            ],
                                                            'customEntry' => null,
                                                            'visible' => true,
                                                            'deps' => [
                                                                0 => 'checkoutProvider',
                                                            ],
                                                            'imports' => [
                                                                'initialOptions' => 'index = checkoutProvider:dictionaries.region_id',
                                                                'setOptions' => 'index = checkoutProvider:dictionaries.region_id',
                                                            ],
                                                        ],
                                                        'postcode' => [
                                                            'component' => 'Magento_Ui/js/form/element/post-code',
                                                            'config' => [
                                                                'customScope' => 'shippingAddress',
                                                                'template' => 'ui/form/field',
                                                                'elementTmpl' => 'ui/form/element/input',
                                                            ],
                                                            'dataScope' => 'shippingAddress.postcode',
                                                            'label' => 'Zip/Postal Code',
                                                            'provider' => 'checkoutProvider',
                                                            'sortOrder' => '110',
                                                            'validation' => [
                                                                'required-entry' => true,
                                                            ],
                                                            'options' => [
                                                            ],
                                                            'filterBy' => null,
                                                            'customEntry' => null,
                                                            'visible' => true,
                                                        ],
                                                        'company' => [
                                                            'component' => 'Magento_Ui/js/form/element/abstract',
                                                            'config' => [
                                                                'customScope' => 'shippingAddress',
                                                                'template' => 'ui/form/field',
                                                                'elementTmpl' => 'ui/form/element/input',
                                                            ],
                                                            'dataScope' => 'shippingAddress.company',
                                                            'label' => 'Company',
                                                            'provider' => 'checkoutProvider',
                                                            'sortOrder' => '60',
                                                            'validation' => [
                                                                'max_text_length' => 255,
                                                                'min_text_length' => '0',
                                                            ],
                                                            'options' => [
                                                            ],
                                                            'filterBy' => null,
                                                            'customEntry' => null,
                                                            'visible' => true,
                                                        ],
                                                        'fax' => [
                                                            'validation' => [
                                                                'min_text_length' => '0',
                                                            ],
                                                        ],
                                                        'telephone' => [
                                                            'component' => 'Magento_Ui/js/form/element/abstract',
                                                            'config' => [
                                                                'customScope' => 'shippingAddress',
                                                                'template' => 'ui/form/field',
                                                                'elementTmpl' => 'ui/form/element/input',
                                                                'tooltip' => [
                                                                    'description' => 'For delivery questions.',
                                                                ],
                                                            ],
                                                            'dataScope' => 'shippingAddress.telephone',
                                                            'label' => 'Phone Number',
                                                            'provider' => 'checkoutProvider',
                                                            'sortOrder' => '120',
                                                            'validation' => [
                                                                'required-entry' => true,
                                                                'max_text_length' => 255,
                                                                'min_text_length' => 1,
                                                            ],
                                                            'options' => [
                                                            ],
                                                            'filterBy' => null,
                                                            'customEntry' => null,
                                                            'visible' => true,
                                                        ],
                                                        'inline-form-manipulator' => [
                                                            'component' => 'Amazon_Payment/js/view/shipping-address/inline-form',
                                                        ],
                                                        'firstname' => [
                                                            'component' => 'Magento_Ui/js/form/element/abstract',
                                                            'config' => [
                                                                'customScope' => 'shippingAddress',
                                                                'template' => 'ui/form/field',
                                                                'elementTmpl' => 'ui/form/element/input',
                                                            ],
                                                            'dataScope' => 'shippingAddress.firstname',
                                                            'label' => 'First Name',
                                                            'provider' => 'checkoutProvider',
                                                            'sortOrder' => '20',
                                                            'validation' => [
                                                                'required-entry' => true,
                                                                'max_text_length' => 255,
                                                                'min_text_length' => 1,
                                                            ],
                                                            'options' => [
                                                            ],
                                                            'filterBy' => null,
                                                            'customEntry' => null,
                                                            'visible' => true,
                                                            'value' => 'Veronica',
                                                        ],
                                                        'lastname' => [
                                                            'component' => 'Magento_Ui/js/form/element/abstract',
                                                            'config' => [
                                                                'customScope' => 'shippingAddress',
                                                                'template' => 'ui/form/field',
                                                                'elementTmpl' => 'ui/form/element/input',
                                                            ],
                                                            'dataScope' => 'shippingAddress.lastname',
                                                            'label' => 'Last Name',
                                                            'provider' => 'checkoutProvider',
                                                            'sortOrder' => '40',
                                                            'validation' => [
                                                                'required-entry' => true,
                                                                'max_text_length' => 255,
                                                                'min_text_length' => 1,
                                                            ],
                                                            'options' => [
                                                            ],
                                                            'filterBy' => null,
                                                            'customEntry' => null,
                                                            'visible' => true,
                                                            'value' => 'Costello',
                                                        ],
                                                        'street' => [
                                                            'component' => 'Magento_Ui/js/form/components/group',
                                                            'label' => 'Street Address',
                                                            'required' => true,
                                                            'dataScope' => 'shippingAddress.street',
                                                            'provider' => 'checkoutProvider',
                                                            'sortOrder' => '70',
                                                            'type' => 'group',
                                                            'config' => [
                                                                'template' => 'ui/group/group',
                                                                'additionalClasses' => 'street',
                                                            ],
                                                            'children' => [
                                                                0 => [
                                                                    'label' => 'Street Address: Line 1',
                                                                    'component' => 'Magento_Ui/js/form/element/abstract',
                                                                    'config' => [
                                                                        'customScope' => 'shippingAddress',
                                                                        'template' => 'ui/form/field',
                                                                        'elementTmpl' => 'ui/form/element/input',
                                                                    ],
                                                                    'dataScope' => 0,
                                                                    'provider' => 'checkoutProvider',
                                                                    'validation' => [
                                                                        'required-entry' => true,
                                                                        'max_text_length' => 255,
                                                                        'min_text_length' => 1,
                                                                    ],
                                                                    'additionalClasses' => 'field',
                                                                ],
                                                                1 => [
                                                                    'label' => 'Street Address: Line 2',
                                                                    'component' => 'Magento_Ui/js/form/element/abstract',
                                                                    'config' => [
                                                                        'customScope' => 'shippingAddress',
                                                                        'template' => 'ui/form/field',
                                                                        'elementTmpl' => 'ui/form/element/input',
                                                                    ],
                                                                    'dataScope' => 1,
                                                                    'provider' => 'checkoutProvider',
                                                                    'validation' => [
                                                                        'max_text_length' => 255,
                                                                        'min_text_length' => 1,
                                                                    ],
                                                                    'additionalClasses' => 'additional',
                                                                ],
                                                                2 => [
                                                                    'label' => 'Street Address: Line 3',
                                                                    'component' => 'Magento_Ui/js/form/element/abstract',
                                                                    'config' => [
                                                                        'customScope' => 'shippingAddress',
                                                                        'template' => 'ui/form/field',
                                                                        'elementTmpl' => 'ui/form/element/input',
                                                                    ],
                                                                    'dataScope' => 2,
                                                                    'provider' => 'checkoutProvider',
                                                                    'validation' => [
                                                                        'max_text_length' => 255,
                                                                        'min_text_length' => 1,
                                                                    ],
                                                                    'additionalClasses' => 'additional',
                                                                ],
                                                            ],
                                                        ],
                                                        'country_id' => [
                                                            'component' => 'Magento_Ui/js/form/element/select',
                                                            'config' => [
                                                                'customScope' => 'shippingAddress',
                                                                'template' => 'ui/form/field',
                                                                'elementTmpl' => 'ui/form/element/select',
                                                            ],
                                                            'dataScope' => 'shippingAddress.country_id',
                                                            'label' => 'Country',
                                                            'provider' => 'checkoutProvider',
                                                            'sortOrder' => '80',
                                                            'validation' => [
                                                                'required-entry' => true,
                                                            ],
                                                            'filterBy' => null,
                                                            'customEntry' => null,
                                                            'visible' => true,
                                                            'deps' => [
                                                                0 => 'checkoutProvider',
                                                            ],
                                                            'imports' => [
                                                                'initialOptions' => 'index = checkoutProvider:dictionaries.country_id',
                                                                'setOptions' => 'index = checkoutProvider:dictionaries.country_id',
                                                            ],
                                                            'value' => 'US',
                                                        ],
                                                        'city' => [
                                                            'component' => 'Magento_Ui/js/form/element/abstract',
                                                            'config' => [
                                                                'customScope' => 'shippingAddress',
                                                                'template' => 'ui/form/field',
                                                                'elementTmpl' => 'ui/form/element/input',
                                                            ],
                                                            'dataScope' => 'shippingAddress.city',
                                                            'label' => 'City',
                                                            'provider' => 'checkoutProvider',
                                                            'sortOrder' => '100',
                                                            'validation' => [
                                                                'required-entry' => true,
                                                                'max_text_length' => 255,
                                                                'min_text_length' => 1,
                                                            ],
                                                            'options' => [
                                                            ],
                                                            'filterBy' => null,
                                                            'customEntry' => null,
                                                            'visible' => true,
                                                        ],
                                                    ],
                                                ],
                                                'price' => [
                                                    'component' => 'Magento_Tax/js/view/checkout/shipping_method/price',
                                                    'displayArea' => 'price',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'component' => 'uiComponent',
                            'displayArea' => 'steps',
                        ],
                    ],
                    'component' => 'uiComponent',
                    'config' => [
                        'template' => 'Magento_Checkout/onepage',
                    ],
                ],
            ],
            'types' => [
                'form.input' => [
                    'component' => 'Magento_Ui/js/form/element/abstract',
                    'config' => [
                        'provider' => 'checkoutProvider',
                        'deps' => [
                            0 => 'checkoutProvider',
                        ],
                        'template' => 'ui/form/field',
                        'elementTmpl' => 'ui/form/element/input',
                    ],
                ],
            ],
        ];
    }
}
