RDF is a W3C standard for modeling and sharing distributed knowledge based on a
decentralized open-world assumption. This RDF Extensions (RDFx) package for
Drupal 7 includes several modules to enhance the RDF and RDFa functionality
which are part of Drupal 7 core.

This project includes the following modules:

* RDFx: extends core RDF support by providing extra APIs and additional
  serialization formats such as RDF/XML, NTriples, Turtle... (requires the
  RESTful Web Services module). Browse to node/1.rdf or node/1.nt to export RDF.
* RDF UI: allows site administrators to manage the RDF mappings of their site:
  alter default core mappings or specify mappings for the new content types and
  fields they create.
* Evoc: enables the import of RDF vocabularies (such as FOAF, SIOC, etc.) which
  the site administrator can use to map Drupal data to RDF.


== Dependencies ==

This project requires the Entity API module:
http://drupal.org/project/entity


== Related projects ==

Download the following modules to avail more RDF features:

* RESTful Web Services module for RDF serializations
  http://drupal.org/project/restws

* The site RDF data can be made available in a SPARQL endpoint with the
  SPARQL module.
  http://drupal.org/project/sparql


== Install the RDF Extensions (RDFx) module ==

  1. Copy all the module files into a subdirectory called
     sites/all/modules/rdfx/ under your Drupal installation directory.

  2. Go to Administer >> Site building >> Modules and enable the RDFx module and
     any other module you would like. You will find them in the "RDF" section.

  3. Install the ARC2 library following one of these 2 options:
       - run "drush rdf-download" (recommended, it will download the right
         package and extract it at the right place for you.)
       - manual install: requires the libraries API module:
         http://drupal.org/project/libraries
         Download the ARC2 library from
         http://github.com/semsol/arc2/tarball/master and extract it in the
         libraries directory such that you end up with the following file
         structure: sites/all/libraries/ARC2/arc/ARC2.php

== Bug reports ==

Post bug reports and feature requests to the issue tracking system at:
<http://drupal.org/node/add/project_issue/rdf>


== Credits ==
The original RDF module was written for Drupal 6 by Arto Bendiken. It has been
refactored for Drupal 7 by Stéphane Corlosquet, Lin Clark and Richard Cyganiak,
based on the RDF CCK and Evoc modules, which are now part of the main RDF
package for Drupal 7.


== Current maintainers ==
  Stéphane "scor" Corlosquet - <http://openspring.net/>
  Lin Clark - <http://lin-clark.com/>
