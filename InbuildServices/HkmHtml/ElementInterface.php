<?php
namespace Hkm_services\HkmHtml;

interface ElementInterface
{
    /**
	 * Element
	 *
	 * set Element 
	 *
     * @var string $elementParty only allowed header, body, footer
     * @var string $elementType only allowed meta, link, script
     * @avr array $attributes
	 *
	 * @return ElementInterface
	 */
    public function setElement(string $elementParty, string $elementType, array $attributes):ElementInterface;

    /**
	 * Element
	 *
	 * get Element 
	 *
     * @var string $type only allowed metas, links, scripts
	 *
	 * @return string
	 */
    public function getElements(string $type):string;
}
