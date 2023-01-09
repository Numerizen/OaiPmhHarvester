<?php declare(strict_types=1);
/**
 * @copyright Copyright (c) 2009-2011 Roy Rosenzweig Center for History and New Media
 * @copyright Daniel Berthereau, 2014-2023
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

    public function mapRecord(SimpleXMLElement $record): array
    {
        $resource = [
            '@type' => 'o:Item',
            'o:is_public' => $this->getOption('o:is_public'),
            'o:media' => [],
            'o:item_set' => $this->getOption('o:item_set'),
        ];

        // Manage specific profiles with structural map. Furthermore, files may
        // have metadata.
        $mapFiles = $this->mapFiles($record);
        $isEmpty = empty($mapFiles);
        $dmdSection = $this->mapRecordSingleMets($record, $isEmpty);
        if ($isEmpty) {
            $resource += $dmdSection;
        } else {
            $resource += $dmdSection[$mapFiles['itemId']];
        }

        $recordMetadata = $record->metadata;
        $recordMetadata->registerXpathNamespace('mets', self::NAMESPACE_METS);

        $files = $recordMetadata->xpath('mets:mets/mets:fileSec/mets:fileGrp/mets:file');
        foreach ($files as $fileXml) {
            $file = $fileXml->FLocat->attributes(self::NAMESPACE_XLINK);
            if (!isset($file['href'])) {
                continue;
            }

            // The dmd id can be set in two main places.
            $fileAttributes = $fileXml->attributes();
            if (isset($fileAttributes['DMDID'])) {
                $dmdId = (string) $fileAttributes['DMDID'];
            }
            // Indirect, if any.
            elseif (isset($fileAttributes['ID'])) {
                $fileId = (string) $fileAttributes['ID'];
                $fileDmdIds = $recordMetadata->xpath("mets:mets/mets:structMap[1]//mets:div[mets:fptr[@FILEID = '$fileId']][1]/@DMDID");
                $dmdId = $fileDmdIds ? (string) reset($fileDmdIds) : null;
                $dmdId = $dmdId !== $mapFiles['itemId'] ? $dmdId : null;
            }
            // No dmd.
            else {
                $dmdId = null;
            }

            $href = (string) $file['href'];
            $title = !isset($file['title']) || !strlen((string) $file['title']) ? null : [
                'type' => 'lieral',
                'property_id' => 1,
                '@value' => (string) $file['title'],
            ];
            $baseMedia = [
                'o:ingester' => 'url',
                'ingest_url' => $href,
                'o:source' => $href,
            ];
            $resource['o:media'][] = $baseMedia
                + ($title && !$dmdId ? ['dctems:title' => [$title]] : [])
                + ($dmdId ? $dmdSection[$dmdId] : []);
        }

        return [$resource];
    }

    /**
     * Convenience function that returns the xml structMap as an array of items
     * and the files associated with it.
     *
     * if the structmap doesn't exist in the xml schema, null will be returned.
     */
    protected function mapFiles(SimpleXMLElement $record): ?array
    {
        $structMap = $record
            ->metadata
            ->mets
            ->structMap
            ->div;

        if (!isset($structMap['DMDID'])) {
            return null;
        }

        $map = [
            'itemId' => (string) $structMap['DMDID'],
            'files' => [],
        ];

        foreach ($structMap->fptr ?? [] as $fileId) {
            $map['files'][] = (string) $fileId['FILEID'];
        }

        return $map;
    }

    protected function mapRecordSingleMets(SimpleXMLElement $record, bool $isEmpty): array
    {
        $meta = [];

        $mets = $record
            ->metadata
            ->mets
            ->children(self::NAMESPACE_METS);

        $dublinCores = [
            'dc' => OaiDc::NAMESPACE_DUBLIN_CORE,
            'dcterms' => OaiDcterms::NAMESPACE_DCTERMS,
        ];

        foreach ($mets->dmdSec as $k) {
            $extractedValues = [];
            foreach ($dublinCores as $prefix => $namespace) {
                $localNames = $this->getLocalNamesByIdForVocabulary($prefix);

                // TODO Currently, mdRef is not managed.
                if (empty($k->mdWrap)) {
                    continue;
                }

                $metadata = $k
                    ->mdWrap
                    ->xmlData
                    ->children($namespace);

                // Sometime, an intermediate wrapper is added between xmlData
                // and children, as <dc:dc>, so a quick check is done.
                if ($metadata->count() === 1) {
                    $firstElement = $metadata[0]->getName();
                    if (!in_array($firstElement, array_keys($localNames))) {
                        $metadata = $metadata->children($namespace);
                    }
                }

                if (!$metadata->count()) {
                    continue;
                }

                foreach ($localNames as $localName) {
                    if (isset($metadata->$localName)) {
                        $extractedValues["dcterms:$localName"] = $this->extractValues($metadata, "dcterms:$localName");
                    }
                }

                if ($isEmpty) {
                    $meta = $extractedValues;
                } else {
                    $dmdAttributes = $k->attributes();
                    $meta[(string) $dmdAttributes['ID']] = $extractedValues;
                }
            }
        }

        return $meta;
    }
}
