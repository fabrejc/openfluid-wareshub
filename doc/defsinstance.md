Manage definitions sets and corresponding instance
==================================================


## Structure of a definition set


### Configuration files

**TODO**


### Definitions files

**TODO**

Example of a definition file:
```json
{
	"test.ware.sim": {
		"users-ro": ["mike"],
		"users-rw": ["john","dave"],
		"mailinglist": ["john@foobar.org","dave@foobar.org"],
		"webreporting": false
	}
}
```


### Templates files

**TODO**

Templates patterns:
* `@@OFWHUB_GITCORE_ROOTPATH@@` : root path of the git-core directory
* `@@OFWHUB_WEB_URLSUBDIR@@` : url path for web reporting
* `@@OFWHUB_WEB_ROOTPATH@@` : directory for web reporting index
* `@@OFWHUB_WARE_ID@@` : ID of the ware
* `@@OFWHUB_WARE_ROUSERS_STRING@@` : string for apache <Limit> directive for read-only users 
* `@@OFWHUB_WARE_RWUSERS_STRING@@` : string for apache <Limit> directive for read-write users
* `@@OFWHUB_WARES_ROOTPATH@@` : directory root path for git repositories
* `@@OFWHUB_WARES_URLSUBDIR@@` : url root path for git access 
* `@@OFWHUB_WARES_WARETYPESUBDIR@@` : directory name corresponding to ware type
* `@@OFWHUB_WARES_SIMSUBDIR@@` : subdirectory name for simulators
* `@@OFWHUB_WARES_OBSSUBDIR@@` : subdirectory name for observers
* `@@OFWHUB_WARES_BEXTSUBDIR@@` : subdirectory name for builder-extensions
* `@@OFWHUB_APACHE_ROOTPATH@@` : directory of apache config files for each ware


## Definition of a ware

**TODO**