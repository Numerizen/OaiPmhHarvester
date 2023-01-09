<?php declare(strict_types=1);

/**
 * @copyright Copyright (c) 2009-2011 Roy Rosenzweig Center for History and New Media
 * @copyright Daniel Berthereau, 2014-2023
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace OaiPmhHarvester\OaiPmh\HarvesterMap;

use SimpleXMLElement;

/**
 * Metadata format map for the common oai_dcterms Dublin Core format
 */
class OaiDcterms extends OaiDc
{
    // And many other non-unofficial prefixes and fake oai schema.
    const METADATA_PREFIX = 'oai_dcterms';
    const NAMESPACE_OAI_DCTERMS = 'http://www.openarchives.org/OAI/2.0/oai_dcterms/';
    const NAMESPACE_DCTERMS = 'http://purl.org/dc/terms/';

    protected function mapRecordSingle(SimpleXMLElement $record, array $resource): array
    {
        // Process all namespaces to manage various prefixes and unofficial oai
        // schemas.
        foreach ($record->metadata->getNamespaces(true) as $namespace) {
            $metadata = $record
                ->metadata
                ->children($namespace)
                ->children(self::NAMESPACE_DCTERMS);
            foreach ($this->getLocalNamesByIdForVocabulary('dcterms') as $localName) {
                if (isset($metadata->$localName)) {
                    $resource["dcterms:$localName"] = $this->extractValues($metadata, "dcterms:$localName");
                }
            }
        }
        return $resource;
    }
}
