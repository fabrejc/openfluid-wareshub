OpenFLUID-WaresHub basics
=========================

OpenFLUID-WaresHub is a system for managing and sharing OpenFLUID wares, 
such as simulators, observers and builder-extensions.


## Definitions sets

The system uses definitions sets. Each definition in the set corresponds to a ware,
and gives information to instantiate a git repository for hosting the ware source code.
There may be as definitions as needed, splitted in categories: 
simulators, observers, wares-extensions

A ware definition contains information such as:
* the ware ID
* the users granted for read-only and/or read-write access to the source code

These informations are completed with informations included in the 
ware source code itself in a file called
"wareshub.json"

See also: [Manage definitions sets and corresponding instance](defsinstance.md) for more informations


## Repositories instances

Using definitions sets, the system can create a git repository corresponding to a definition,
setup access control over http or https for this repository, and setup git hooks
(for email notifications and post-processing)

**TODO explain standard branches in ware git repositories**


## Anministration tool

OpenFLUID-WaresHub includes a command line tool to perform main tasks,
such as creation of repositories or deployment of definitions updates

See [Administration tool usage](ofwareshub-admin.md) for more informations


## Web reporting  

The system proposes a web reporting based on the definitions sets and git repositories for wares.
It also uses informations from standard files in git repositories

See [Set up informations in wares source code](waresrc.md) for more informations
 