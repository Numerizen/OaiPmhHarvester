<?php declare(strict_types=1);
/**
 * @package OaipmhHarvester
 * @subpackage Models
 * @copyright Copyright (c) 2009-2011 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * Metadata format map for the required oai_dc Dublin Core format
 *
 * @package OaipmhHarvester
 * @subpackage Models
 */
class OaipmhHarvester_Harvest_OaiDc extends OaipmhHarvester_Harvest_Abstract
{
    /*  XML schema and OAI prefix for the format represented by this class.
     These constants are required for all maps. */
    const METADATA_SCHEMA = 'http://www.openarchives.org/OAI/2.0/oai_dc.xsd';
    const METADATA_PREFIX = 'oai_dc';

    const OAI_DC_NAMESPACE = 'http://www.openarchives.org/OAI/2.0/oai_dc/';
    const DUBLIN_CORE_NAMESPACE = 'http://purl.org/dc/elements/1.1/';

    /**
     * Collection to insert items into.
     * @var \Omeka\Api\Representation\ItemSetRepresentation
     */
    protected $_collection;

    /**
     * Actions to be carried out before the harvest of any items begins.
     */
    protected function _beforeHarvest(): void
    {
        $harvest = $this->_getHarvest();

        $collectionMetadata = [
            'metadata' => [
                'public' => $this->getOption('public'),
                'featured' => $this->getOption('featured'),
            ],
        ];
        $collectionMetadata['elementTexts']['Dublin Core']['Title'][] = ['text' => (string) $harvest->set_name, 'html' => false];
        $collectionMetadata['elementTexts']['Dublin Core']['Description'][] = ['text' => (string) $harvest->set_Description, 'html' => false];

        $this->_collection = $this->_insertCollection($collectionMetadata);
    }

    /**
     * Harvest one record.
     *
     * @param \SimpleXMLIterator $record XML metadata record
     * @return array Array of item-level, element texts and file metadata.
     */
    protected function _harvestRecord($record)
    {
        $itemMetadata = [
            'collection_id' => $this->_collection->id,
            'public' => $this->getOption('public'),
            'featured' => $this->getOption('featured'),
        ];

        $dcMetadata = $record
            ->metadata
            ->children(self::OAI_DC_NAMESPACE)
            ->children(self::DUBLIN_CORE_NAMESPACE);

        $elementTexts = [];
        $elements = [
            'contributor', 'coverage', 'creator',
            'date', 'description', 'format',
            'identifier', 'language', 'publisher',
            'relation', 'rights', 'source',
            'subject', 'title', 'type',
        ];
        foreach ($elements as $element) {
            if (isset($dcMetadata->$element)) {
                foreach ($dcMetadata->$element as $rawText) {
                    $text = trim($rawText);
                    $elementTexts['Dublin Core'][ucwords($element)][] = ['text' => (string) $text, 'html' => false];
                }
            }
        }

        return [
            'itemMetadata' => $itemMetadata,
            'elementTexts' => $elementTexts,
            'fileMetadata' => [],
        ];
    }
}
