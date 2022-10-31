<?php declare(strict_types=1);
/**
 * @copyright Copyright (c) 2009-2011 Roy Rosenzweig Center for History and New Media
 * @copyright Daniel Berthereau, 2014-2022
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace OaiPmhHarvester\OaiPmh\HarvesterMap;

use SimpleXMLElement;

/**
 * Metadata format map for Dublin Core via Mets.
 *
 * Mets may have multple profile with various schema. The Dublin Core is the
 * most common, but some other are used, in particular mods.
 * @todo Manage profile Mets for mods.
 */
class Mets extends AbstractHarvesterMap
{
    const METADATA_PREFIX = 'mets';
    const NAMESPACE_METS = 'http://www.loc.gov/METS/';
    const NAMESPACE_XLINK = 'http://www.w3.org/1999/xlink';

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

        $map = $this->_getMap($record);
        $dmdSection = $this->_dmdSecToArray($record);
        if (empty($map)) {
            $elementTexts = $dmdSection;
        } else {
            $elementTexts = $dmdSection[$map['itemId']];
        }

        $fileMetadata = [];
        $recordMetadata = $record->metadata;
        $recordMetadata->registerXpathNamespace('mets', self::METS_NAMESPACE);
        $files = $recordMetadata->xpath('mets:mets/mets:fileSec/mets:fileGrp/mets:file');
        foreach ($files as $fl) {
            $dmdId = $fl->attributes();
            $file = $fl->FLocat->attributes(self::XLINK_NAMESPACE);

            $fileMetadata['files'][] = [
                'Upload' => null,
                'Url' => (string) $file['href'],
                'source' => (string) $file['href'],
                //'name'   => (string) $file['title'],
                'metadata' => (isset($dmdId['DMDID']) ? $dmdSection[(string) $dmdId['DMDID']] : []),
            ];
        }

        return ['itemMetadata' => $itemMetadata,
            'elementTexts' => $elementTexts,
            'fileMetadata' => $fileMetadata, ];
    }

    /**
     * Convenience function that returns the xml structMap
     * as an array of items and the files associated with it.
     *
     * if the structmap doesn't exist in the xml schema null
     * will be returned.
     *
     * @param \SimpleXMLElement $record
     * @return array|null
     */
    private function _getMap($record)
    {
        $structMap = $record
            ->metadata
            ->mets
            ->structMap
            ->div;

        $map = null;
        if (isset($structMap['DMDID'])) {
            $map['itemId'] = (string) $structMap['DMDID'];

            $fileCount = count($structMap->fptr);

            $map['files'] = null;
            if ($fileCount != 0) {
                foreach ($structMap->fptr as $fileId) {
                    $map['files'][] = (string) $fileId['FILEID'];
                }
            }
        }

        return $map;
    }

    protected function mapRecordSingle(SimpleXMLElement $record, array $resource): array
    {
        $mets = $record
            ->metadata
            ->mets
            ->children(self::NAMESPACE_METS);

        foreach ([
            'dc' => OaiDc::NAMESPACE_DUBLIN_CORE,
            'dcterms' => OaiDcterms::NAMESPACE_DCTERMS,
        ] as $prefix => $namespace) foreach ($mets->dmdSec as $k) {
            $metadata = $k
                ->mdWrap
                ->xmlData
                ->children($namespace);
            foreach ($this->getLocalNamesByIdForVocabulary($prefix) as $localName) {
                if (isset($metadata->$localName)) {
                    $resource["dcterms:$localName"] = $this->extractValues($metadata, "dcterms:$localName");
                }
            }
        }

        return $resource;
    }
}
