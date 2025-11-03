# SGM_site
Website to show pathogenicity predictions and 3D structures for SynGAP1 protein

## Dependencies

* [SGM_molstar](https://github.com/jvlehtonen/SGM_molstar): Mol* code, v4.10.0-2
* [SGM_schema](https://github.com/jvlehtonen/SGM_schema): Database, v1.3.0

## Installation

* Script `build.sh` gathers content for website into directory `build`
  and stores database credentials into php file. Some of the content
  is not in this repo, including _Mol*_ molecule structure viewer.
  The _Mol*_ viewer is in https://github.com/jvlehtonen/SGM_molstar
* Script `upload.sh` copies content from `build` to HTTP server. The server
  should have instance of MariaDB with the database (which uses schema https://github.com/jvlehtonen/SGM_schema)
  that the website is a front-end for. The credentials should have only read-only access to the database.
