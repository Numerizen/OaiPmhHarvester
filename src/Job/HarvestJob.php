<?php
namespace OaiPmhHarvester\Job;

use Omeka\Job\AbstractJob;
use Zend\Http\Request;

use Zend\Debug\Debug;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;

class HarvestJob extends AbstractJob
{
    /*Xml schema and OAI prefix for the format represented by this class
     * These constants are required for all maps
     */
    /** OAI-PMH metadata prefix */
    const METADATA_PREFIX = 'mets';

    /** XML namespace for output format */
    const METS_NAMESPACE = 'http://www.loc.gov/METS/';

    /** XML schema for output format */
    const METADATA_SCHEMA = 'http://www.loc.gov/standards/mets/mets.xsd';

    /** XML namespace for unqualified Dublin Core */
    
    const DUBLIN_CORE_NAMESPACE = 'http://purl.org/dc/elements/1.1/';

    const OAI_DC_NAMESPACE = 'http://www.openarchives.org/OAI/2.0/oai_dc/';
    
    const XLINK_NAMESPACE = 'http://www.w3.org/1999/xlink';
    
    const OAI_DC_SCHEMA = 'http://www.openarchives.org/OAI/2.0/oai_dc/';
    
    protected $api;

    protected $logger;

    protected $hasErr = false;

    protected $resource_type;
    
    protected $dcProperties;
    
    public function perform()
    {
        $this->logger = $this->getServiceLocator()->get('Omeka\Logger');
        $this->api = $this->getServiceLocator()->get('Omeka\ApiManager');
        $config = $this->getServiceLocator()->get('Config');

        // Set Dc Properties for mapping
        $dcProperties = $this->api->search('properties', ['vocabulary_id' => 1], ['responseContent' => 'resource'])->getContent();
        $elements = [];
        foreach ($dcProperties as $id => $property) {
          $elements[$property->getId()] = $property->getLocalName();
        }
        $this->dcProperties = $elements; 
        
        $args = $this->job->getArgs();

        $harvestJson = [
          'o:job' => ['o:id' => $this->job->getId()],
          'comment' => 'Harvesting started',
          'has_err' => 0,
          'base_url' => $args['base_url'],
          'set_name' => $args['set_name'],
          'set_spec' => $args['set_spec'],
          'collection_id' => $args['collection_id'],
          'metadata_prefix' => $args['metadata_prefix'],          
          'resource_type' => $this->getArg('resource_type', 'items'),        
        ];
              
        $insertJson = [];

//        $logger = new Logger;
//        $writer = new Stream('/Apache/Omeka-S/logs/vb.log');
//        $logger->addWriter($writer);
//        Logger::registerErrorHandler($logger);     
//        $this->logger = $logger;

        // TODO : autres protocoles.
        $method = '';
        switch ($args['metadata_prefix']) {
          case 'mets' :
            $method = '_dmdSecToJson'; 
            break;
          case 'oai_dc' :
            $method = '_oaidcToJson'; 
            break;
          default :
            // TODO : Exception ou message d'erreur
         }
         
        $resumptionToken = false;
        do {
          if ($resumptionToken) {
            $url = $args['base_url'] . "?resumptionToken=$resumptionToken&verb=ListRecords";
          } else {
            $url = $args['base_url'] . "?metadataPrefix=" . $args['metadata_prefix'] . "&verb=ListRecords&set=" . $args['set_spec'];            
          }
    
          $response = \simplexml_load_file($url); 
          
          $records = $response->ListRecords;
          $toInsert = [];
          foreach ($records->record as $n => $record) {
            $toInsert[] = $this->{$method}($record, $args['collection_id']);
          } 
          $this->createItems($toInsert);
          if (isset($response->ListRecords->resumptionToken) && $response->ListRecords->resumptionToken <> '') {            
            $resumptionToken = $response->ListRecords->resumptionToken;   
          } else {
            $resumptionToken = false;
          }
        } while ($resumptionToken);

      $response = $this->api->create('oaipmhharvester_harvestjob', $harvestJson);
      $importRecordId = $response->getContent()->id();

      // Update du job
      $comment = $this->getArg('comment');
      $harvestJson = ['comment' => $comment,
                      'has_err' => $this->hasErr,
                      'nb_items' => count($sets), //TODO : nombre d'items ?
                     ];

      $response = $this->api->update('oaipmhharvester_harvestjob', $importRecordId, $harvestJson);      
    }

    protected function createItems($toCreate)
    {          
          $insertJson = [];
          foreach ($toCreate as $index => $item) {   
            $insertJson[] = $item;
            if ($index % 20 == 0) {
              $createResponse = $this->api->batchCreate('items', $insertJson, [], ['continueOnError' => true]);
              $this->createRollback($createResponse->getContent());
              $insertJson = [];
            }
          }
 
        $createResponse = $this->api->batchCreate('items', $insertJson, [], ['continueOnError' => true]);
          
        $this->createRollback($createResponse->getContent());    
        
        $createImportEntitiesJson = [];

    }  
    
    protected function createRollback($records) {

        foreach ($records as $resourceReference) {
            $createImportEntitiesJson[] = $this->buildImportRecordJson($resourceReference);
        }
        $createImportRecordResponse = $this->api->batchCreate('oaipmhharvester_entities', $createImportEntitiesJson, [], ['continueOnError' => true]);    
        return $createImportRecordResponse;
    }
    /**
     * 
     * Convenience function that returns the 
     * xmls dmdSec as an Omeka ElementTexts array
     * 
     * @param type $record
     * @return boolean/array
     */
    private function _dmdSecToJson($record, $setId)
    {   
        $mets= $record->metadata->mets->children(self::METS_NAMESPACE);
        $meta = null;
        foreach($mets->dmdSec as $k){
            $dcMetadata = $k
                    ->mdWrap
                    ->xmlData
                    ->children(self::DUBLIN_CORE_NAMESPACE);
            $elementTexts = array();            
            foreach($this->dcProperties as $property_id => $element){
              if(isset($dcMetadata->$element)){
                foreach($dcMetadata->$element as $rawText){                
                  $text = trim($rawText);
                  $elementTexts["dcterms:$element"][] = [
                    '@value' => $text,
                     'type' => 'literal',
  //                            "value_is_html" => false,
                    "property_id" => $property_id,
                  ];
                }
              }
            }       
            $meta = $elementTexts;
            $meta['o:item_set'] = ["o:id" => $setId];
        }         
        return $meta;
    }

    private function _oaidcToJson($record, $setId)
    {   
        $dcMetadata = $record
                    ->metadata
                    ->children(self::OAI_DC_NAMESPACE)
                    ->children(self::DUBLIN_CORE_NAMESPACE);
        
        $elementTexts = array();       
        foreach($this->dcProperties as $property_id => $element){        
          if(isset($dcMetadata->$element)){
            foreach($dcMetadata->$element as $rawText){                
              $text = trim($rawText);
              $elementTexts["dcterms:$element"][] = [
                '@value' => $text,
                 'type' => 'literal',
//                            "value_is_html" => false,
                "property_id" => $property_id,
              ];
            }
          }
        }       
        $meta = $elementTexts;
        $meta['o:item_set'] = ["o:id" => $setId];
        return $meta;        
    }
        
    protected function buildImportRecordJson($resourceReference)
    {
        $recordJson = ['o:job' => ['o:id' => $this->job->getId()],
                                  'entity_id' => $resourceReference->id(),
                                  'resource_type' => $this->getArg('entity_type', 'items'),
                            ];
        return $recordJson;
    }
   
}
