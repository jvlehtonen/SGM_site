# SGM_site
Website to show pathogenicity predictions and 3D structures for SynGAP1 protein

## Installation

* Script `build.sh` gathers content for website into directory `build`
  and stores database credentials into php file. Some of the content
  is not in this repo, including _Mol*_ molecule structure viewer
* Script `upload.sh` copies content from `build` to HTTP server
  The server should have instance of MariaDb/MySQL with the database
  that the website is a front-end for
