Installation, configuration and usage
=====================================

## Requirements

To be fully deployed on a server, the OpenFLUID_WaresHub system requires:
* git
* apache with DAV module, and other modules depending on access and authentification methods (ssl, ldap, ...)
* php

The OpenFLUID WaresHub system has been tested on Ubuntu 12.04 server,
but should work on any linux system with small adjustments.


## Installation

First, install and configure the required tools (see above).

Then, you simply have to put the WareHub system in the directory of you choice, and set correct permissions (at least):
* Read+Write flags on `config` directory 
* Execute flag on `ofwareshub-admin` tool for user who will manage the definitions and instance(s)
* Read access on web directory for web user (usually www-data) 
 

## Configuration

The configuration file is named `config.json`, and is located in the `config` directory.
If a `localconfig.json` file exists in the `config` directory ,
the values it contains will override the values given by `config.json` 

**TODO: give details on format**

Example of configuration file:
```json
{
	"general" : {
		"simulators-dir" : "simulators",
		"observers-dir" : "observers",
		"builderexts-dir" : "builderexts",
		"templates-dir" : "templates",
		"templates-files" : {
			"main-apache" : "main-apache-default.tpl.conf",
			"ware-apache" : "ware-apache-default.tpl.conf",
			"ware-def" : "ware-definition-default.tpl.json"
		}
	},	
		
	"definitions" : {
	    "config-dir" : "config",
		"config-file" : "config.json",
		"localconfig-file" : "localconfig.json",
		"waresdefs-dir" : "definitions",
		"githooks-dir" : "githooks",
		"web-dir" : "web",
		"templates-dir" : "templates",
		"templates-files" : {
			"main-apache" : "main-apache.tpl.conf",
			"ware-apache" : "ware-apache.tpl.conf",
			"ware-def" : "ware-definition.tpl.json"
		}
	},
	"instance" : {
		"wares-git-rootdir" : "wares-git-repositories",
		"apache-conf-rootdir" : "apache-configs",
		"apache-conf-mainfile" : "main.conf"
	}		
}
```

## Usage

**TODO**