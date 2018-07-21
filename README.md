# OaiPmhHarvester

## Requirements

* Omeka-S 1.1.1

## Installation

Nothing special : install as usual.

## Usage

* Go to the Oai-Pmh Harvester page.
* Type or paste the address of the repository you want to fetch data from  (usually something like *http://sample-host.org/data/oai*)
* Cilck the **View Sets** button.
* Choose the sets you want to harvest by checking the box **Harvest this set ?** for each set you want to be harvested.
* Choose the protocol you want to use : **oai_dc** or  **mets** for each set.
* Click the **Harvest** button.
* A task should be created. The module creates a collection for each fetched set and then proceeds to harvest the content.

## Credits

This is a simplified version of the Omeka Classic module, which was shamelessly copied whenever possible. The work consisted mainly in adapting the code to Omeka-S - *i.e* Zend  Framework 3.


