<?php declare(strict_types=1);

namespace OaiPmhHarvester\Mvc\Controller\Plugin;

use Laminas\Mvc\Controller\Plugin\AbstractPlugin;
use Laminas\Mvc\I18n\Translator;
use OaiPmhHarvester\OaiPmh\HarvesterMapManager;

/**
 * Get infos about an OAI-PMH repository.
 */
class OaiPmhRepository extends AbstractPlugin
{
    /**
     * @var \OaiPmhHarvester\OaiPmh\HarvesterMapManager
     */
    protected $harvesterMapManager;

    /**
     * @var \Laminas\Mvc\I18n\Translator
     */
    protected $translator;

    /**
     * @var string
     */
    protected $endpoint;

    /**
     * List of managed metadata prefixes.
     *
     * The order is used to set the default format in the second form.
     * Full Dublin Core is preferred.
     *
     * @var array
     */
    protected $managedMetadataPrefixes = [];

    /**
     * @var int
     */
    protected $maxListSets = 1000;

    public function __construct(HarvesterMapManager $harvesterMapManager, Translator $translator)
    {
        $this->harvesterMapManager = $harvesterMapManager;
        $this->translator = $translator;
        $this->managedMetadataPrefixes = $harvesterMapManager->getRegisteredNames();
    }

    /**
     * Prepare the helper.
     *
     * It does not use http client, but direct simplexml_load_file().
     */
    public function __invoke(?string $endpoint = null): self
    {
        if (!is_null($endpoint)) {
            $this->endpoint = $endpoint;
        }
        return $this;
    }

    public function getTranslator(): Translator
    {
        return $this->translator;
    }

    public function listManagedPrefixes(): array
    {
        return $this->managedMetadataPrefixes;
    }

    public function hasNoQueryAndNoFragment(?string $endpoint = null): bool
    {
        $endpoint = $endpoint ?? $this->endpoint;
        if (!$endpoint) {
            return false;
        }
        return $endpoint
            && strpos($endpoint, '?') === false
            && strpos($endpoint, '#') === false;
    }

    public function isXmlEndpoint(?string $endpoint = null): bool
    {
        $endpoint = $endpoint ?? $this->endpoint;
        if (!$endpoint) {
            return false;
        }

        $url = $endpoint . '?verb=Identify';
        $response = @\simplexml_load_file($url . '?verb=Identify');
        return (bool) $response;
    }

    public function hasOaiPmhManagedFormats(?string $endpoint = null): bool
    {
        $endpoint = $endpoint ?? $this->endpoint;
        if (!$endpoint) {
            return false;
        }
        return (bool) $this->listOaiPmhFormats($endpoint);
    }

    public function getRepositoryName(?string $endpoint = null): ?string
    {
        $endpoint = $endpoint ?? $this->endpoint;
        if (!$endpoint) {
            return null;
         }
        $url = $endpoint . '?verb=Identify';
        $response = @\simplexml_load_file($url);
        if (!$response) {
            return null;
        }
        return (string) $response->Identify->repositoryName;
    }

    /**
     * Prepare the list of formats of an OAI-PMH repository.
     *
     * @return string[] Associative array of format prefix and name.
     */
    public function listOaiPmhFormats(?string $endpoint = null): array
    {
        $endpoint = $endpoint ?? $this->endpoint;
        if (!$endpoint) {
            return [];
        }

        $formats = [];

        $url = $endpoint . '?verb=ListMetadataFormats';
        $response = @\simplexml_load_file($url);
        if ($response) {
            foreach ($response->ListMetadataFormats->metadataFormat as $format) {
                $prefix = (string) $format->metadataPrefix;
                if (in_array($prefix, $this->managedMetadataPrefixes)) {
                    $formats[$prefix] = $prefix;
                } else {
                    $formats[$prefix] = sprintf($this->translator->translate('%s [unmanaged]'), $prefix); // @translate
                }
            }
        }

        return $formats;
    }

    /**
     * Prepare the list of sets of an OAI-PMH repository.
     */
    public function listOaiPmhSets(?string $endpoint = null): array
    {
        $endpoint = $endpoint ?? $this->endpoint;
        if (!$endpoint) {
            return [];
        }

        $sets = [];

        $baseListSetUrl = $endpoint . '?verb=ListSets';
        $resumptionToken = false;
        $totalSets = null;
        do {
            $url = $baseListSetUrl;
            if ($resumptionToken) {
                $url = $baseListSetUrl . '&resumptionToken=' . $resumptionToken;
            }

            /** @var \SimpleXMLElement $response */
            $response = @\simplexml_load_file($url);
            if (!$response || !isset($response->ListSets)) {
                break;
            }

            if (is_null($totalSets)) {
                $totalSets = isset($response->ListRecords->resumptionToken)
                    ? (int) $response->ListSets->resumptionToken['completeListSize']
                    : count($response->ListSets->set);
            }

            foreach ($response->ListSets->set as $set) {
                $sets[(string) $set->setSpec] = (string) $set->setName;
                if (count($sets) >= $this->maxListSets) {
                    break 2;
                }
            }

            $resumptionToken = isset($response->ListSets->resumptionToken) && $response->ListSets->resumptionToken !== ''
                ? (string) $response->ListSets->resumptionToken
                : false;
        } while ($resumptionToken && count($sets) <= $this->maxListSets);

        return [
            'total' => $totalSets,
            'sets' => array_slice($sets, 0, $this->maxListSets, true),
        ];
    }
}
