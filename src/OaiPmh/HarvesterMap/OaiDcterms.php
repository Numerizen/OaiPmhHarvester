<?php declare(strict_types=1);

/**
 * @copyright Copyright (c) 2009-2011 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace OaiPmhHarvester\OaiPmh\HarvesterMap;

/**
 * Metadata format map for the common oai_dcterms Dublin Core format
 */
class OaiDcterms extends OaiDc
{
    const METADATA_SCHEMA = 'http://www.openarchives.org/OAI/2.0/oai_dcterms.xsd';
    const METADATA_PREFIX = 'oai_dcterms';

    const OAI_DCTERMS_NAMESPACE = 'http://www.openarchives.org/OAI/2.0/oai_dcterms/';
    const DCTERMS_NAMESPACE = 'http://purl.org/dc/terms/';
}
